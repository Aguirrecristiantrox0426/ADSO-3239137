<?php

namespace Tests\Feature;

use App\Models\Advisor;
use App\Models\Customer;
use App\Models\Rental;
use App\Models\Tool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalApiTest extends TestCase
{
    use RefreshDatabase;

    private function createRentalParticipants(): array
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

        return [$customer, $advisor];
    }

    public function test_can_register_a_rental(): void
    {
        [$customer, $advisor] = $this->createRentalParticipants();

        $tool = Tool::create([
            'name' => 'Taladro',
            'total_quantity' => 10,
            'available_quantity' => 10,
        ]);

        $this->postJson('/api/v1/rentals', [
            'customer_id' => $customer->id,
            'advisor_id' => $advisor->id,
            'items' => [
                [
                    'tool_id' => $tool->id,
                    'quantity' => 2,
                ],
            ],
        ])->assertCreated()
          ->assertJsonPath('success', true);

        $this->assertDatabaseHas('rentals', [
            'customer_id' => $customer->id,
            'advisor_id' => $advisor->id,
        ]);

        $this->assertDatabaseHas('rental_details', [
            'tool_id' => $tool->id,
            'quantity' => 2,
        ]);
    }

    public function test_rental_decreases_available_tool_quantity(): void
    {
        [$customer, $advisor] = $this->createRentalParticipants();

        $tool = Tool::create([
            'name' => 'Martillo',
            'total_quantity' => 10,
            'available_quantity' => 10,
        ]);

        $this->postJson('/api/v1/rentals', [
            'customer_id' => $customer->id,
            'advisor_id' => $advisor->id,
            'items' => [
                [
                    'tool_id' => $tool->id,
                    'quantity' => 2,
                ],
            ],
        ])->assertCreated();

        $this->assertDatabaseHas('tools', [
            'id' => $tool->id,
            'available_quantity' => 8,
        ]);
    }

    public function test_customer_cannot_rent_more_than_three_outstanding_units(): void
    {
        [$customer, $advisor] = $this->createRentalParticipants();

        $tool = Tool::create([
            'name' => 'Taladro',
            'total_quantity' => 10,
            'available_quantity' => 10,
        ]);

        $response = $this->postJson('/api/v1/rentals', [
            'customer_id' => $customer->id,
            'advisor_id' => $advisor->id,
            'items' => [
                [
                    'tool_id' => $tool->id,
                    'quantity' => 4,
                ],
            ],
        ]);

        $response->assertStatus(409);

        $this->assertDatabaseCount('rentals', 0);
    }

    public function test_cannot_rent_more_units_than_available(): void
    {
        [$customer, $advisor] = $this->createRentalParticipants();

        $tool = Tool::create([
            'name' => 'Pulidora',
            'total_quantity' => 5,
            'available_quantity' => 1,
        ]);

        $this->postJson('/api/v1/rentals', [
            'customer_id' => $customer->id,
            'advisor_id' => $advisor->id,
            'items' => [
                [
                    'tool_id' => $tool->id,
                    'quantity' => 2,
                ],
            ],
        ])->assertStatus(409);

        $this->assertDatabaseHas('tools', [
            'id' => $tool->id,
            'available_quantity' => 1,
        ]);

        $this->assertDatabaseCount('rentals', 0);
    }

    public function test_can_list_rentals(): void
    {
        [$customer, $advisor] = $this->createRentalParticipants();

        $tool = Tool::create([
            'name' => 'Taladro',
            'total_quantity' => 5,
            'available_quantity' => 5,
        ]);

        $this->postJson('/api/v1/rentals', [
            'customer_id' => $customer->id,
            'advisor_id' => $advisor->id,
            'items' => [
                [
                    'tool_id' => $tool->id,
                    'quantity' => 1,
                ],
            ],
        ])->assertCreated();

        $this->getJson('/api/v1/rentals')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_can_show_a_rental_and_its_details(): void
    {
        [$customer, $advisor] = $this->createRentalParticipants();

        $tool = Tool::create([
            'name' => 'Martillo',
            'total_quantity' => 5,
            'available_quantity' => 5,
        ]);

        $response = $this->postJson('/api/v1/rentals', [
            'customer_id' => $customer->id,
            'advisor_id' => $advisor->id,
            'items' => [
                [
                    'tool_id' => $tool->id,
                    'quantity' => 1,
                ],
            ],
        ])->assertCreated();

        $rentalId = $response->json('data.id');

        $this->assertNotNull($rentalId);

        $this->getJson("/api/v1/rentals/{$rentalId}")
            ->assertOk()
            ->assertJsonPath('data.id', $rentalId);

        $this->getJson("/api/v1/rentals/{$rentalId}/details")
            ->assertOk()
            ->assertJsonPath('data.0.rental_id', $rentalId);
    }
}