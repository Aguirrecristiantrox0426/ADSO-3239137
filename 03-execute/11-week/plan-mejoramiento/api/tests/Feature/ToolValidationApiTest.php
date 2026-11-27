<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolValidationApiTest extends TestCase
{
    use RefreshDatabase;

     protected function setUp(): void
    {
        parent::setUp();
        $this->authenticateAs('administrator');
    }

    public function test_cannot_register_tool_without_name(): void
    {
        $this->postJson('/api/v1/tools', [
            'total_quantity' => 5,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_cannot_register_tool_without_total_quantity(): void
    {
        $this->postJson('/api/v1/tools', [
            'name' => 'Taladro electrico',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['total_quantity']);
    }

    public function test_cannot_register_tool_with_zero_quantity(): void
    {
        $this->postJson('/api/v1/tools', [
            'name' => 'Taladro electrico',
            'total_quantity' => 0,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['total_quantity']);
    }

    public function test_cannot_register_tool_with_invalid_description(): void
    {
        $this->postJson('/api/v1/tools', [
            'name' => 'Taladro electrico',
            'description' => ['Descripcion invalida'],
            'total_quantity' => 5,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['description']);
    }
}