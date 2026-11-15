<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ToolController extends Controller
{
    /**
     * Listar todas las herramientas activas.
     */
    public function index(): JsonResponse
    {
        $tools = Tool::query()
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
     * Consultar una herramienta por su ID.
     */
    public function show(int $id): JsonResponse
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

        return response()->json([
            'success' => true,
            'message' => 'Herramienta consultada correctamente.',
            'data' => $tool,
            'errors' => [],
        ]);
    }

    /**
     * Registrar una nueva herramienta.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'total_quantity' => ['required', 'integer', 'min:1'],
        ]);

        $tool = Tool::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'total_quantity' => $validated['total_quantity'],
            'available_quantity' => $validated['total_quantity'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Herramienta registrada correctamente.',
            'data' => $tool,
            'errors' => [],
        ], 201);
    }

    /**
     * Actualizar una herramienta existente.
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
            'data' => $tool->fresh(),
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

        $tool->delete();

        return response()->json([
            'success' => true,
            'message' => 'Herramienta eliminada correctamente.',
            'data' => null,
            'errors' => [],
        ], 200);
    }

    /**
     * Consultar la disponibilidad de todas las herramientas.
     */
    public function availability(): JsonResponse
    {
        $tools = Tool::query()
            ->select([
                'id',
                'name',
                'total_quantity',
                'available_quantity',
            ])
            ->orderBy('name')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Disponibilidad de herramientas consultada correctamente.',
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
     * Consultar la disponibilidad de una herramienta específica.
     */
    public function showAvailability(int $id): JsonResponse
    {
        $tool = Tool::query()
            ->select([
                'id',
                'name',
                'total_quantity',
                'available_quantity',
            ])
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
            'message' => 'Disponibilidad de la herramienta consultada correctamente.',
            'data' => $tool,
            'errors' => [],
        ]);
    }
}
