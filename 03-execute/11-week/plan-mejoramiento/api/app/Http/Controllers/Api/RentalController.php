<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advisor;
use App\Models\Customer;
use App\Models\Rental;
use App\Models\RentalDetail;
use App\Models\Tool;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RentalController extends Controller
{
    /**
     * List all rentals.
     */
    public function index(): JsonResponse
    {
        $rentals = Rental::with(['customer', 'advisor', 'details'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Rentals retrieved successfully.',
            'data' => $rentals->items(),
            'meta' => [
                'current_page' => $rentals->currentPage(),
                'last_page' => $rentals->lastPage(),
                'per_page' => $rentals->perPage(),
                'total' => $rentals->total(),
            ],
            'errors' => [],
        ]);
    }

    /**
     * Show a specific rental.
     */
    public function show(int $id): JsonResponse
    {
        $rental = Rental::with([
            'customer',
            'advisor',
            'details.tool',
            'details.returnDetails',
            'returns.details',
        ])->find($id);

        if (!$rental) {
            return response()->json([
                'success' => false,
                'message' => 'Rental not found.',
                'data' => null,
                'errors' => [
                    'rental' => ['The requested rental does not exist.'],
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rental retrieved successfully.',
            'data' => $rental,
            'errors' => [],
        ]);
    }

    /**
     * Register a new rental.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id')
                    ->whereNull('deleted_at'),
            ],
            'advisor_id' => [
                'nullable',
                'integer',
                Rule::exists('advisors', 'id')
                    ->whereNull('deleted_at'),
            ],
            'items' => ['required', 'array', 'min:1'],
            'items.*.tool_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('tools', 'id')
                    ->whereNull('deleted_at'),
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        if ($request->user()->role === 'advisor') {
            $advisor = Advisor::query()->where('user_id', $request->user()->id)->first();
            if (!$advisor || $advisor->trashed() || (isset($validated['advisor_id']) && (int) $validated['advisor_id'] !== $advisor->id)) {
                return response()->json(['success' => false, 'message' => 'The authenticated advisor is not authorized for this operation.', 'data' => null, 'errors' => ['advisor' => ['The advisor identity is not authorized.']]], 403);
            }
            $validated['advisor_id'] = $advisor->id;
        } elseif (empty($validated['advisor_id'])) {
            return response()->json(['success' => false, 'message' => 'An advisor is required.', 'data' => null, 'errors' => ['advisor_id' => ['The advisor field is required.']]], 422);
        }

        $result = DB::transaction(function () use ($validated) {

            // Lock the customer to prevent simultaneous rentals
            // from exceeding the customer's outstanding limit.
            $customer = Customer::query()
                ->lockForUpdate()
                ->find($validated['customer_id']);

            if (!$customer) {
                return [
                    'conflict' => true,
                    'message' => 'Customer not found or inactive.',
                ];
            }

            $advisor = Advisor::find($validated['advisor_id']);

            if (!$advisor) {
                return [
                    'conflict' => true,
                    'message' => 'Advisor not found or inactive.',
                ];
            }

            // Retrieve outstanding rentals and their return details.
            $existingRentals = Rental::query()
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['active', 'partially_returned'])
                ->with('details.returnDetails')
                ->get();

            $outstandingUnits = 0;
            $today = CarbonImmutable::now('UTC')->startOfDay();

            foreach ($existingRentals as $existingRental) {
                foreach ($existingRental->details as $detail) {
                    $returned = $detail->returnDetails
                        ->sum('returned_quantity');

                    $outstandingUnits += max(
                        0,
                        $detail->quantity - $returned
                    );
                }

                // Customers with overdue rentals cannot rent again.
                if (
                    $existingRental->due_date->lt($today)
                    && $existingRental->details->contains(
                        fn ($detail) =>
                            $detail->quantity >
                            $detail->returnDetails->sum('returned_quantity')
                    )
                ) {
                    return [
                        'conflict' => true,
                        'message' => 'Customer has overdue rentals and cannot rent more tools.',
                    ];
                }
            }

            $requestedUnits = collect($validated['items'])
                ->sum('quantity');

            // Maximum of three outstanding physical units per customer.
            if ($outstandingUnits + $requestedUnits > 3) {
                return [
                    'conflict' => true,
                    'message' => 'The customer cannot have more than three outstanding tool units.',
                ];
            }

            // Lock tools in a consistent order to protect inventory.
            $toolIds = collect($validated['items'])
                ->pluck('tool_id')
                ->sort()
                ->values();

            $tools = Tool::query()
                ->whereIn('id', $toolIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($validated['items'] as $item) {
                $tool = $tools->get($item['tool_id']);

                if (!$tool || $tool->available_quantity < $item['quantity']) {
                    return [
                        'conflict' => true,
                        'message' => 'Insufficient available inventory for one or more tools.',
                    ];
                }
            }

            // Calculate rental dates using UTC.
            $rentalDate = $today;
            $dueDate = $rentalDate->addDays(7);

            $rental = Rental::create([
                'customer_id' => $customer->id,
                'advisor_id' => $advisor->id,
                'rental_date' => $rentalDate->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'status' => 'active',
            ]);

            foreach ($validated['items'] as $item) {
                $tool = $tools->get($item['tool_id']);

                RentalDetail::create([
                    'rental_id' => $rental->id,
                    'tool_id' => $tool->id,
                    'tool_name_snapshot' => $tool->name,
                    'quantity' => $item['quantity'],
                ]);

                $tool->decrement(
                    'available_quantity',
                    $item['quantity']
                );
            }

            return [
                'conflict' => false,
                'rental' => $rental,
            ];
        });

        if ($result['conflict']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => null,
                'errors' => [
                    'rental' => [$result['message']],
                ],
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rental created successfully.',
            'data' => Rental::with([
                'customer',
                'advisor',
                'details',
            ])->find($result['rental']->id),
            'errors' => [],
        ], 201);
    }

    /**
     * Show the tools included in a rental.
     */
    public function details(int $id): JsonResponse
    {
        $rental = Rental::with('details.tool')->find($id);

        if (!$rental) {
            return response()->json([
                'success' => false,
                'message' => 'Rental not found.',
                'data' => null,
                'errors' => [
                    'rental' => ['The requested rental does not exist.'],
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rental details retrieved successfully.',
            'data' => $rental->details,
            'errors' => [],
        ]);
    }
}
