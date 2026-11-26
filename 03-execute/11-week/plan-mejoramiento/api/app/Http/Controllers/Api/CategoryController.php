<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Categorías consultadas.',
            'data' => Category::orderBy('name')->get(),
            'errors' => [],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $category = Category::create($this->validated($request));

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada.',
            'data' => $category,
            'errors' => [],
        ], 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->ignore($category->getKey()),
            ],
            'description' => ['nullable', 'string'],
        ]);

        $category->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada.',
            'data' => $category->fresh(),
            'errors' => [],
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categoría desactivada.',
            'data' => null,
            'errors' => [],
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name'),
            ],
            'description' => ['nullable', 'string'],
        ]);
    }
}
