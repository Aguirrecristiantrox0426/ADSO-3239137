<?php

namespace Tests\Feature;

use App\Models\Tool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolAvailabilityApiTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticateAs('administrator');
    }

    public function test_can_list_tool_availability(): void
    {
        Tool::create([
            'name' => 'Taladro electrico',
            'description' => 'Taladro para construccion',
            'total_quantity' => 10,
            'available_quantity' => 7,
        ]);

        $this->getJson('/api/v1/tools/availability')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Taladro electrico');
    }

    public function test_availability_response_includes_total_and_available_quantities(): void
    {
        Tool::create([
            'name' => 'Martillo',
            'description' => 'Martillo de construccion',
            'total_quantity' => 15,
            'available_quantity' => 12,
        ]);

        $this->getJson('/api/v1/tools/availability')
            ->assertOk()
            ->assertJsonPath('data.0.total_quantity', 15)
            ->assertJsonPath('data.0.available_quantity', 12);
    }

    public function test_can_show_availability_of_a_specific_tool(): void
    {
        $tool = Tool::create([
            'name' => 'Pulidora angular',
            'description' => 'Herramienta de corte',
            'total_quantity' => 8,
            'available_quantity' => 5,
        ]);

        $this->getJson("/api/v1/tools/{$tool->id}/availability")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $tool->id)
            ->assertJsonPath('data.total_quantity', 8)
            ->assertJsonPath('data.available_quantity', 5);
    }

    public function test_cannot_show_availability_of_nonexistent_tool(): void
    {
        $this->getJson('/api/v1/tools/99999/availability')
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null);
    }
}