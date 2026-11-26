<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnValidationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_register_return_without_rental_id(): void
    {
        $this->postJson('/api/v1/returns', [
            'advisor_id' => 1,
            'items' => [
                [
                    'rental_detail_id' => 1,
                    'returned_quantity' => 1,
                ],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rental_id']);
    }

    public function test_cannot_register_return_without_advisor_id(): void
    {
        $this->postJson('/api/v1/returns', [
            'rental_id' => 1,
            'items' => [
                [
                    'rental_detail_id' => 1,
                    'returned_quantity' => 1,
                ],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['advisor_id']);
    }

    public function test_cannot_register_return_without_items(): void
    {
        $this->postJson('/api/v1/returns', [
            'rental_id' => 1,
            'advisor_id' => 1,
            'items' => [],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    }

    public function test_cannot_register_return_with_zero_quantity(): void
    {
        $this->postJson('/api/v1/returns', [
            'rental_id' => 1,
            'advisor_id' => 1,
            'items' => [
                [
                    'rental_detail_id' => 1,
                    'returned_quantity' => 0,
                ],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.returned_quantity']);
    }
}