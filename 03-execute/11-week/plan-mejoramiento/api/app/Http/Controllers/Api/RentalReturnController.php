<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\RentalReturn;
use App\Models\ReturnDetail;
use App\Models\Tool;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentalReturnController extends Controller
{
    /**
     * List all returns.
     */
    public function index(): JsonResponse
    {
        $returns = RentalReturn::with([
            'rental.customer',
            'advisor',
            'details',
        ])
            ->orderByDesc('return_date')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Returns retrieved successfully.',
            'data' => $returns->items(),
            'meta' => [
                'current_page' => $returns->currentPage(),
                'last_page' => $returns->lastPage(),
                'per_page' => $returns->perPage(),
                'total' => $returns->total(),
            ],
            'errors' => [],
        ]);
    }

    /**
     * Show a specific return.
     */
    public function show(int $id): JsonResponse
    {
        $return = RentalReturn::with([
            'rental.customer',
            'advisor',
            'details.rentalDetail',
        ])->find($id);

        if (!$return) {
            return response()->json([
                'success' => false,
                'message' => 'Return not found.',
                'data' => null,
                'errors' => [
                    'return' => [
                        'The requested return does not exist.',
                    ],
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Return retrieved successfully.',
            'data' => $return,
            'errors' => [],
        ]);
    }

    /**
     * Register a return for an existing rental.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rental_id' => [
                'required',
                'integer',
                'exists:rentals,id',
            ],
            'advisor_id' => [
                'nullable',
                'integer',
                'exists:advisors,id',
            ],
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.rental_detail_id' => [
                'required',
                'integer',
                'distinct',
                'exists:rental_details,id',
            ],
            'items.*.returned_quantity' => [
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

            // Lock the rental to prevent simultaneous returns.
            $rental = Rental::query()
                ->lockForUpdate()
                ->find($validated['rental_id']);

            if (!$rental) {
                return [
                    'conflict' => true,
                    'message' => 'Rental not found.',
                ];
            }

            if (!in_array(
                $rental->status,
                ['active', 'partially_returned'],
                true
            )) {
                return [
                    'conflict' => true,
                    'message' => 'This rental has already been completed.',
                ];
            }

            // Retrieve rental details and their previous returns.
            $rentalDetails = $rental->details()
                ->with('returnDetails')
                ->orderBy('tool_id')
                ->get()
                ->keyBy('id');

            // Verify that all requested details belong to this rental.
            $requestedIds = collect($validated['items'])
                ->pluck('rental_detail_id')
                ->sort()
                ->values();

            if ($requestedIds->contains(
                fn ($id) => !$rentalDetails->has($id)
            )) {
                return [
                    'conflict' => true,
                    'message' => 'One or more details do not belong to this rental.',
                ];
            }

            // Retrieve and lock tools, including soft-deleted tools.
            $toolIds = $rentalDetails->pluck('tool_id')
                ->unique()
                ->sort()
                ->values();

            $tools = Tool::withTrashed()
                ->whereIn('id', $toolIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validate all requested returns before creating records.
            foreach ($validated['items'] as $item) {
                $detail = $rentalDetails->get(
                    $item['rental_detail_id']
                );

                $previouslyReturned = $detail->returnDetails
                    ->sum('returned_quantity');

                $pendingQuantity = $detail->quantity
                    - $previouslyReturned;

                if ($item['returned_quantity'] > $pendingQuantity) {
                    return [
                        'conflict' => true,
                        'message' => 'Returned quantity exceeds the pending quantity.',
                    ];
                }

                $tool = $tools->get($detail->tool_id);

                if (!$tool) {
                    return [
                        'conflict' => true,
                        'message' => 'The associated tool no longer exists.',
                    ];
                }

                $newAvailableQuantity =
                    (int) $tool->available_quantity
                    + (int) $item['returned_quantity'];

                if (
                    $newAvailableQuantity >
                    (int) $tool->total_quantity
                ) {
                    return [
                        'conflict' => true,
                        'message' => 'The return would exceed the total tool inventory.',
                    ];
                }
            }

            // Create the return record.
            $return = RentalReturn::create([
                'rental_id' => $rental->id,
                'advisor_id' => $validated['advisor_id'],
                'return_date' => CarbonImmutable::now('UTC')
                    ->toDateString(),
            ]);

            // Register return details and update inventory.
            foreach ($validated['items'] as $item) {
                $detail = $rentalDetails->get(
                    $item['rental_detail_id']
                );

                $tool = $tools->get($detail->tool_id);

                ReturnDetail::create([
                    'return_id' => $return->id,
                    'rental_detail_id' => $detail->id,
                    'returned_quantity' => $item['returned_quantity'],
                ]);

                $tool->increment(
                    'available_quantity',
                    $item['returned_quantity']
                );
            }

            // Determine whether the rental is completed or partially returned.
            $rental->load('details.returnDetails');

            $allReturned = $rental->details->every(
                fn ($detail) =>
                    $detail->quantity <=
                    $detail->returnDetails->sum('returned_quantity')
            );

            $rental->update([
                'status' => $allReturned
                    ? 'completed'
                    : 'partially_returned',
            ]);

            return [
                'conflict' => false,
                'return' => $return,
            ];
        });

        // Return a conflict response when validation fails.
        if ($result['conflict']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => null,
                'errors' => [
                    'return' => [
                        $result['message'],
                    ],
                ],
            ], 409);
        }

        // Return the successfully registered return.
        return response()->json([
            'success' => true,
            'message' => 'Return registered successfully.',
            'data' => RentalReturn::with('details')
                ->find($result['return']->id),
            'errors' => [],
        ], 201);
    }
}
