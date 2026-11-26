<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnDuplicateValidationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_register_return_with_duplicate_rental_detail_ids(): void
    {
        $this->postJson('/api/v1/returns', [
            'rental_id' => 1,
            'advisor_id' => 1,
            'items' => [
                [
                    'rental_detail_id' => 1,
                    'returned_quantity' => 1,
                ],
                [
                    'rental_detail_id' => 1,
                    'returned_quantity' => 1,
                ],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'items.1.rental_detail_id',
            ]);
    }
}