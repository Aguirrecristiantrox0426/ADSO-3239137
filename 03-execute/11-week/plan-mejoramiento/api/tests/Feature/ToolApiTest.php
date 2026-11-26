<?php

namespace Tests\Feature;

use App\Models\Tool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_a_tool(): void
    {
        $response = $this->postJson('/api/v1/tools', [
            'name' => 'Taladro electrico',
            'description' => 'Herramienta para construccion',
            'total_quantity' => 10,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Taladro electrico')
            ->assertJsonPath('data.total_quantity', 10)
            ->assertJsonPath('data.available_quantity', 10);
    }

    public function test_can_list_tools(): void
    {
        Tool::create([
            'name' => 'Taladro',
            'total_quantity' => 5,
            'available_quantity' => 5,
        ]);

        $this->getJson('/api/v1/tools')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_can_show_a_tool(): void
    {
        $tool = Tool::create([
            'name' => 'Martillo',
            'total_quantity' => 4,
            'available_quantity' => 4,
        ]);

        $this->getJson("/api/v1/tools/{$tool->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $tool->id)
            ->assertJsonPath('data.name', 'Martillo');
    }

    public function test_can_update_tool_quantity(): void
    {
        $tool = Tool::create([
            'name' => 'Taladro',
            'total_quantity' => 10,
            'available_quantity' => 10,
        ]);

        $this->putJson("/api/v1/tools/{$tool->id}", [
            'total_quantity' => 12,
        ])
            ->assertOk()
            ->assertJsonPath('data.total_quantity', 12)
            ->assertJsonPath('data.available_quantity', 12);
    }

    public function test_cannot_reduce_total_below_rented_units(): void
    {
        $tool = Tool::create([
            'name' => 'Taladro',
            'total_quantity' => 10,
            'available_quantity' => 3,
        ]);

        $this->putJson("/api/v1/tools/{$tool->id}", [
            'total_quantity' => 6,
        ])
            ->assertUnprocessable();
    }

    public function test_can_soft_delete_a_tool(): void
    {
        $tool = Tool::create([
            'name' => 'Martillo',
            'total_quantity' => 5,
            'available_quantity' => 5,
        ]);

        $this->deleteJson("/api/v1/tools/{$tool->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('tools', [
            'id' => $tool->id,
        ]);
    }

    public function test_can_consult_tool_availability(): void
    {
        $tool = Tool::create([
            'name' => 'Taladro',
            'total_quantity' => 10,
            'available_quantity' => 8,
        ]);

        $this->getJson('/api/v1/tools/availability')
            ->assertOk()
            ->assertJsonPath('data.0.id', $tool->id)
            ->assertJsonPath('data.0.available_quantity', 8);

        $this->getJson("/api/v1/tools/{$tool->id}/availability")
            ->assertOk()
            ->assertJsonPath('data.total_quantity', 10)
            ->assertJsonPath('data.available_quantity', 8);
    }
}