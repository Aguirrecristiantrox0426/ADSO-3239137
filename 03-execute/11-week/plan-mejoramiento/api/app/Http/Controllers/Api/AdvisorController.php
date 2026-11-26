<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advisor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdvisorController extends Controller
{
    /**
     * List all active advisors.
     */
    public function index(): JsonResponse
    {
        $advisors = Advisor::query()
            ->orderBy('full_name')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Advisors retrieved successfully.',
            'data' => $advisors->items(),
            'meta' => [
                'current_page' => $advisors->currentPage(),
                'last_page' => $advisors->lastPage(),
                'per_page' => $advisors->perPage(),
                'total' => $advisors->total(),
            ],
            'errors' => [],
        ]);
    }

    /**
     * Show a specific advisor.
     */
    public function show(int $id): JsonResponse
    {
        $advisor = Advisor::find($id);

        if (!$advisor) {
            return response()->json([
                'success' => false,
                'message' => 'Advisor not found.',
                'data' => null,
                'errors' => [
                    'advisor' => ['The requested advisor does not exist.'],
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Advisor retrieved successfully.',
            'data' => $advisor,
            'errors' => [],
        ]);
    }

    /**
     * Register a new advisor.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'identification_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('advisors', 'identification_number'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('advisors', 'email'),
            ],
        ]);

        $advisor = Advisor::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Advisor created successfully.',
            'data' => $advisor,
            'errors' => [],
        ], 201);
    }

    /**
     * Update an existing advisor.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $advisor = Advisor::find($id);

        if (!$advisor) {
            return response()->json([
                'success' => false,
                'message' => 'Advisor not found.',
                'data' => null,
                'errors' => [
                    'advisor' => ['The requested advisor does not exist.'],
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
                Rule::unique('advisors', 'identification_number')
                    ->ignore($advisor->id),
            ],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('advisors', 'email')
                    ->ignore($advisor->id),
            ],
        ]);

        $advisor->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Advisor updated successfully.',
            'data' => $advisor->fresh(),
            'errors' => [],
        ]);
    }

    /**
     * Soft delete an advisor.
     */
    public function destroy(int $id): JsonResponse
    {
        $advisor = Advisor::find($id);

        if (!$advisor) {
            return response()->json([
                'success' => false,
                'message' => 'Advisor not found.',
                'data' => null,
                'errors' => [
                    'advisor' => ['The requested advisor does not exist.'],
                ],
            ], 404);
        }

        $advisor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Advisor deleted successfully.',
            'data' => null,
            'errors' => [],
        ]);
    }
}