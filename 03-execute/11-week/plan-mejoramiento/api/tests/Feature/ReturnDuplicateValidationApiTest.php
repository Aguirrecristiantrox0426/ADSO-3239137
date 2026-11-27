<?php

namespace Tests\Feature;

use App\Models\Advisor;
use App\Models\Customer;
use App\Models\RentalDetail;
use App\Models\Tool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnDuplicateValidationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticateAs('administrator');
    }

    public function test_cannot_register_return_with_duplicate_rental_detail_ids(): void
    {
        // Create customer.
        $customer = Customer::create([
            'full_name' => 'Carlos Ramirez',
            'identification_number' => '123456789',
            'phone' => '3001234567',
            'email' => 'carlos@example.com',
        ]);

        // Create advisor.
        $advisor = Advisor::create([
            'full_name' => 'Andres Gomez',
            'identification_number' => '987654321',
            'email' => 'andres@example.com',
        ]);

        // Create tool.
        $tool = Tool::create([
            'name' => 'Taladro',
            'total_quantity' => 10,
            'available_quantity' => 10,
        ]);

        // Register a real rental.
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

        // Retrieve the real rental detail.
        $rentalDetail = RentalDetail::where(
            'rental_id',
            $rentalId
        )->firstOrFail();

        // Attempt to register a return with duplicate detail IDs.
        $this->postJson('/api/v1/returns', [
            'rental_id' => $rentalId,
            'advisor_id' => $advisor->id,
            'items' => [
                [
                    'rental_detail_id' => $rentalDetail->id,
                    'returned_quantity' => 1,
                ],
                [
                    'rental_detail_id' => $rentalDetail->id,
                    'returned_quantity' => 1,
                ],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'items.1.rental_detail_id',
            ]);

        // Verify that no return was registered.
        $this->assertDatabaseCount('returns', 0);
    }
}
