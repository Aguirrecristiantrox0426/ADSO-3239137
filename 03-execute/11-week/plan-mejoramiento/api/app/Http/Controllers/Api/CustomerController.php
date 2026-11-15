<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * List all active customers.
     */
    public function index(): JsonResponse
    {
        $customers = Customer::query()
            ->orderBy('full_name')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Customers retrieved successfully.',
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
            'errors' => [],
        ]);
    }

    /**
     * Show a specific customer.
     */
    public function show(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
                'data' => null,
                'errors' => [
                    'customer' => ['The requested customer does not exist.'],
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer retrieved successfully.',
            'data' => $customer,
            'errors' => [],
        ]);
    }

    /**
     * Register a new customer.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'identification_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'identification_number'),
            ],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully.',
            'data' => $customer,
            'errors' => [],
        ], 201);
    }

    /**
     * Update an existing customer.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
                'data' => null,
                'errors' => [
                    'customer' => ['The requested customer does not exist.'],
                ],
            ], 404);
        }

        $validated = $request->validate([
            'full_name' => ['sometimes', 'required', 'string', 'max:255'],
            'identification_number' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'identification_number')
                    ->ignore($customer->id),
            ],
            'phone' => ['sometimes', 'required', 'string', 'max:30'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
        ]);

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully.',
            'data' => $customer->fresh(),
            'errors' => [],
        ]);
    }

    /**
     * Soft delete a customer.
     */
    public function destroy(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
                'data' => null,
                'errors' => [
                    'customer' => ['The requested customer does not exist.'],
                ],
            ], 404);
        }

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully.',
            'data' => null,
            'errors' => [],
        ]);
    }
}