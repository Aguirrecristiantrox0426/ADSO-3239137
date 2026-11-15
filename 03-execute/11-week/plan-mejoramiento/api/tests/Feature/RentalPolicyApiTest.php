<?php

namespace Tests\Feature;

use App\Models\Advisor;
use App\Models\Customer;
use App\Models\Tool;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalPolicyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_rental_due_date_is_seven_days_after_rental_date(): void
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

        $response = $this->postJson('/api/v1/rentals', [
            'customer_id' => $customer->id,
            'advisor_id' => $advisor->id,
            'items' => [
                [
                    'tool_id' => $tool->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $rentalDate = CarbonImmutable::parse(
            $response->json('data.rental_date')
        );

        $dueDate = CarbonImmutable::parse(
            $response->json('data.due_date')
        );

        $this->assertSame(
            7,
            (int) $rentalDate->diffInDays($dueDate)
        );

        $this->assertSame(
            'active',
            $response->json('data.status')
        );
    }
}