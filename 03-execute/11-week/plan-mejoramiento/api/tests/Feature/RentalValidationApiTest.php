<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalValidationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_create_rental_without_customer(): void
    {
        $this->postJson('/api/v1/rentals', [
            'advisor_id' => 1,
            'items' => [
                [
                    'tool_id' => 1,
                    'quantity' => 1,
                ],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['customer_id']);
    }

    public function test_cannot_create_rental_without_advisor(): void
    {
        $this->postJson('/api/v1/rentals', [
            'customer_id' => 1,
            'items' => [
                [
                    'tool_id' => 1,
                    'quantity' => 1,
                ],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['advisor_id']);
    }

    public function test_cannot_create_rental_without_items(): void
    {
        $this->postJson('/api/v1/rentals', [
            'customer_id' => 1,
            'advisor_id' => 1,
            'items' => [],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    }

    public function test_cannot_create_rental_with_zero_quantity(): void
    {
        $this->postJson('/api/v1/rentals', [
            'customer_id' => 1,
            'advisor_id' => 1,
            'items' => [
                [
                    'tool_id' => 1,
                    'quantity' => 0,
                ],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.quantity']);
    }
}