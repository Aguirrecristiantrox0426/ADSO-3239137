<?php

namespace Tests\Feature;

use App\Models\Advisor;
use App\Models\Customer;
use App\Models\RentalDetail;
use App\Models\Tool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnValidationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticateAs('administrator');
    }

    private function createReturnFixture(): array
    {
        $customer = Customer::create([
            'full_name' => 'Carlos Ramirez',
            'identification_number' => '123456789',
            'phone' => '3001234567',
            'email' => 'carlos@example.com',
        ]);

        $advisor = Advisor::create([
            'full_name' => 'Andres Gomez',
            'identification_number' => '987654321',
            'email' => 'andres@example.com',
        ]);

        $tool = Tool::create([
            'name' => 'Taladro',
            'total_quantity' => 10,
            'available_quantity' => 10,
        ]);

        $rentalResponse = $this->postJson('/api/v1/rentals', [
            'customer_id' => $customer->id,
            'advisor_id' => $advisor->id,
            'items' => [
                [
                    'tool_id' => $tool->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $rentalResponse->assertCreated();

        $rentalId = $rentalResponse->json('data.id');

        $rentalDetail = RentalDetail::where(
            'rental_id',
            $rentalId
        )->firstOrFail();

        return [
            'advisor' => $advisor,
            'rental_id' => $rentalId,
            'rental_detail_id' => $rentalDetail->id,
        ];
    }

    public function test_cannot_register_return_without_rental_id(): void
    {
        $fixture = $this->createReturnFixture();

        $this->postJson('/api/v1/returns', [
            'advisor_id' => $fixture['advisor']->id,
            'items' => [
                [
                    'rental_detail_id' => $fixture['rental_detail_id'],
                    'returned_quantity' => 1,
                ],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rental_id']);
    }

    public function test_cannot_register_return_without_advisor_id(): void
    {
        $fixture = $this->createReturnFixture();

        $this->postJson('/api/v1/returns', [
            'rental_id' => $fixture['rental_id'],
            'items' => [
                [
                    'rental_detail_id' => $fixture['rental_detail_id'],
                    'returned_quantity' => 1,
                ],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['advisor_id']);
    }

    public function test_cannot_register_return_without_items(): void
    {
        $fixture = $this->createReturnFixture();

        $this->postJson('/api/v1/returns', [
            'rental_id' => $fixture['rental_id'],
            'advisor_id' => $fixture['advisor']->id,
            'items' => [],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    }

    public function test_cannot_register_return_with_zero_quantity(): void
    {
        $fixture = $this->createReturnFixture();

        $this->postJson('/api/v1/returns', [
            'rental_id' => $fixture['rental_id'],
            'advisor_id' => $fixture['advisor']->id,
            'items' => [
                [
                    'rental_detail_id' => $fixture['rental_detail_id'],
                    'returned_quantity' => 0,
                ],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'items.0.returned_quantity',
            ]);
    }
}
