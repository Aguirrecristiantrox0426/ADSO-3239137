<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Rental;
use App\Models\Tool;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalTools = (int) Tool::sum('total_quantity');
        $availableTools = (int) Tool::sum('available_quantity');

        $activeRentals = Rental::query()
            ->whereIn('status', ['active', 'partially_returned'])
            ->with([
                'customer',
                'details.returnDetails',
            ])
            ->orderByDesc('created_at');

        $rentals = (clone $activeRentals)->get();

        $pendingUnits = $rentals->sum(function ($rental) {
            return $rental->details->sum(function ($detail) {
                $returned = $detail->returnDetails->sum('returned_quantity');

                return max(0, $detail->quantity - $returned);
            });
        });

        $recentRentals = Rental::query()
            ->with(['customer', 'details'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($rental) {
                return [
                    'id' => $rental->id,
                    'customer' => $rental->customer?->full_name,
                    'rental_date' => $rental->rental_date?->toDateString(),
                    'due_date' => $rental->due_date?->toDateString(),
                    'status' => $rental->status,
                    'units' => $rental->details->sum('quantity'),
                ];
            });

        $data = [
            'metrics' => [
                'total_tools' => $totalTools,
                'available_tools' => $availableTools,
                'pending_return_units' => (int) $pendingUnits,
                'active_rentals' => (clone $activeRentals)->count(),
                'registered_customers' => Customer::count(),
            ],
            'inventory' => [
                'total' => $totalTools,
                'available' => $availableTools,
                'pending_return' => (int) $pendingUnits,
            ],
            'recent_rentals' => $recentRentals,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Dashboard information retrieved successfully.',
            'data' => $data,
            'errors' => [],
        ]);
    }
}
