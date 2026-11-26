<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ToolController extends Controller
{
    /**
     * Listar herramientas activas.
     */
    public function index(): JsonResponse
    {
        $tools = Tool::with('category')
            ->orderBy('name')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Herramientas consultadas correctamente.',
            'data' => $tools->items(),
            'meta' => [
                'current_page' => $tools->currentPage(),
                'last_page' => $tools->lastPage(),
                'per_page' => $tools->perPage(),
                'total' => $tools->total(),
            ],
            'errors' => [],
        ]);
    }

    /**
     * Consultar una herramienta por ID.
     */
    public function show(int $id): JsonResponse
    {
        $tool = Tool::with('category')->find($id);

        if (!$tool) {
            return response()->json([
                'success' => false,
                'message' => 'Herramienta no encontrada.',
                'data' => null,
                'errors' => [
                    'tool' => ['La herramienta solicitada no existe.'],
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Herramienta consultada correctamente.',
            'data' => $tool,
            'errors' => [],
        ]);
    }

    /**
     * Registrar una herramienta.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'total_quantity' => ['required', 'integer', 'min:1'],
        ]);

        $tool = Tool::create([
            'category_id' => $validated['category_id'] ?? null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'total_quantity' => $validated['total_quantity'],
            'available_quantity' => $validated['total_quantity'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Herramienta registrada correctamente.',
            'data' => $tool->load('category'),
            'errors' => [],
        ], 201);
    }

    /**
     * Actualizar una herramienta.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tool = Tool::find($id);

        if (!$tool) {
            return response()->json([
                'success' => false,
                'message' => 'Herramienta no encontrada.',
                'data' => null,
                'errors' => [
                    'tool' => ['La herramienta solicitada no existe.'],
                ],
            ], 404);
        }

        $validated = $request->validate([
            'category_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->whereNull('deleted_at'),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'total_quantity' => ['sometimes', 'required', 'integer', 'min:1'],
        ]);

        $newTotal = $validated['total_quantity']
            ?? $tool->total_quantity;

        $quantityDifference = $newTotal - $tool->total_quantity;
        $newAvailable = $tool->available_quantity + $quantityDifference;

        if ($newAvailable < 0 || $newAvailable > $newTotal) {
            throw ValidationException::withMessages([
                'total_quantity' => [
                    'La cantidad total no puede ser inferior a las unidades que actualmente están alquiladas.',
                ],
            ]);
        }

        $tool->fill([
            'category_id' => array_key_exists('category_id', $validated)
                ? $validated['category_id']
                : $tool->category_id,

            'name' => $validated['name'] ?? $tool->name,

            'description' => array_key_exists('description', $validated)
                ? $validated['description']
                : $tool->description,

            'total_quantity' => $newTotal,
            'available_quantity' => $newAvailable,
        ]);

        $tool->save();

        return response()->json([
            'success' => true,
            'message' => 'Herramienta actualizada correctamente.',
            'data' => $tool->fresh()->load('category'),
            'errors' => [],
        ]);
    }

    /**
     * Eliminar lógicamente una herramienta.
     */
    public function destroy(int $id): JsonResponse
    {
        $tool = Tool::find($id);

        if (!$tool) {
            return response()->json([
                'success' => false,
                'message' => 'Herramienta no encontrada.',
                'data' => null,
                'errors' => [
                    'tool' => ['La herramienta solicitada no existe.'],
                ],
            ], 404);
        }

        if ($tool->available_quantity < $tool->total_quantity) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede desactivar una herramienta que tiene unidades alquiladas.',
                'data' => null,
                'errors' => [
                    'tool' => ['Hay unidades pendientes de devolución.'],
                ],
            ], 409);
        }

        $tool->delete();

        return response()->json([
            'success' => true,
            'message' => 'Herramienta desactivada correctamente.',
            'data' => null,
            'errors' => [],
        ]);
    }

    /**
     * Consultar disponibilidad de todas las herramientas.
     */
    public function availability(): JsonResponse
    {
        $tools = Tool::query()
            ->select([
                'id',
                'category_id',
                'name',
                'total_quantity',
                'available_quantity',
            ])
            ->with('category:id,name')
            ->orderBy('name')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Disponibilidad consultada correctamente.',
            'data' => $tools->items(),
            'meta' => [
                'current_page' => $tools->currentPage(),
                'last_page' => $tools->lastPage(),
                'per_page' => $tools->perPage(),
                'total' => $tools->total(),
            ],
            'errors' => [],
        ]);
    }

    /**
     * Consultar disponibilidad de una herramienta.
     */
    public function showAvailability(int $id): JsonResponse
    {
        $tool = Tool::query()
            ->select([
                'id',
                'category_id',
                'name',
                'total_quantity',
                'available_quantity',
            ])
            ->with('category:id,name')
            ->find($id);

        if (!$tool) {
            return response()->json([
                'success' => false,
                'message' => 'Herramienta no encontrada.',
                'data' => null,
                'errors' => [
                    'tool' => ['No existe una herramienta con el ID indicado.'],
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Disponibilidad consultada correctamente.',
            'data' => $tool,
            'errors' => [],
        ]);
    }
}
