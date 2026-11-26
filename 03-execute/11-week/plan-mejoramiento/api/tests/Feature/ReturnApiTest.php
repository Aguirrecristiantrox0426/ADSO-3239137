<?php

namespace Tests\Feature;

use App\Models\Advisor;
use App\Models\Customer;
use App\Models\Rental;
use App\Models\RentalDetail;
use App\Models\Tool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnApiTest extends TestCase
{
    use RefreshDatabase;

    private function createRentalForReturn(): array
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
            'available_quantity' => 8,
        ]);

        $rental = Rental::create([
            'customer_id' => $customer->id,
            'advisor_id' => $advisor->id,
            'rental_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'active',
        ]);

        $detail = RentalDetail::create([
            'rental_id' => $rental->id,
            'tool_id' => $tool->id,
            'tool_name_snapshot' => 'Taladro',
            'quantity' => 2,
        ]);

        return [$customer, $advisor, $tool, $rental, $detail];
    }

    public function test_can_register_a_return(): void
    {
        [$customer, $advisor, $tool, $rental, $detail] =
            $this->createRentalForReturn();

        $this->postJson('/api/v1/returns', [
            'rental_id' => $rental->id,
            'advisor_id' => $advisor->id,
            'items' => [
                [
                    'rental_detail_id' => $detail->id,
                    'returned_quantity' => 1,
                ],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.rental_id', $rental->id);

        $this->assertDatabaseHas('return_details', [
            'rental_detail_id' => $detail->id,
            'returned_quantity' => 1,
        ]);
    }

    public function test_return_increases_available_tool_quantity(): void
    {
        [$customer, $advisor, $tool, $rental, $detail] =
            $this->createRentalForReturn();

        $this->postJson('/api/v1/returns', [
            'rental_id' => $rental->id,
            'advisor_id' => $advisor->id,
            'items' => [
                [
                    'rental_detail_id' => $detail->id,
                    'returned_quantity' => 1,
                ],
            ],
        ])->assertCreated();

        $this->assertDatabaseHas('tools', [
            'id' => $tool->id,
            'available_quantity' => 9,
        ]);
    }

    public function test_rental_is_completed_when_all_tools_are_returned(): void
    {
        [$customer, $advisor, $tool, $rental, $detail] =
            $this->createRentalForReturn();

        $this->postJson('/api/v1/returns', [
            'rental_id' => $rental->id,
            'advisor_id' => $advisor->id,
            'items' => [
                [
                    'rental_detail_id' => $detail->id,
                    'returned_quantity' => 2,
                ],
            ],
        ])->assertCreated();

        $this->assertDatabaseHas('rentals', [
            'id' => $rental->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('tools', [
            'id' => $tool->id,
            'available_quantity' => 10,
        ]);
    }

    public function test_cannot_return_more_units_than_pending(): void
    {
        [$customer, $advisor, $tool, $rental, $detail] =
            $this->createRentalForReturn();

        $this->postJson('/api/v1/returns', [
            'rental_id' => $rental->id,
            'advisor_id' => $advisor->id,
            'items' => [
                [
                    'rental_detail_id' => $detail->id,
                    'returned_quantity' => 3,
                ],
            ],
        ])->assertStatus(409);

        $this->assertDatabaseCount('returns', 0);

        $this->assertDatabaseHas('tools', [
            'id' => $tool->id,
            'available_quantity' => 8,
        ]);
    }

    public function test_can_list_returns(): void
    {
        [$customer, $advisor, $tool, $rental, $detail] =
            $this->createRentalForReturn();

        $response = $this->postJson('/api/v1/returns', [
            'rental_id' => $rental->id,
            'advisor_id' => $advisor->id,
            'items' => [
                [
                    'rental_detail_id' => $detail->id,
                    'returned_quantity' => 1,
                ],
            ],
        ]);

        $response->assertCreated();

        $this->getJson('/api/v1/returns')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_can_show_a_return(): void
    {
        [$customer, $advisor, $tool, $rental, $detail] =
            $this->createRentalForReturn();

        $response = $this->postJson('/api/v1/returns', [
            'rental_id' => $rental->id,
            'advisor_id' => $advisor->id,
            'items' => [
                [
                    'rental_detail_id' => $detail->id,
                    'returned_quantity' => 1,
                ],
            ],
        ])->assertCreated();

        $returnId = $response->json('data.id');

        $this->assertNotNull($returnId);

        $this->getJson("/api/v1/returns/{$returnId}")
            ->assertOk()
            ->assertJsonPath('data.id', $returnId)
            ->assertJsonPath('data.rental_id', $rental->id);
    }

        public function test_cannot_return_units_that_exceed_total_inventory(): void
    {
        [$customer, $advisor, $tool, $rental, $detail] =
        $this->createRentalForReturn();

        // Simula una inconsistencia previa en el inventario.
        $tool->available_quantity = 9;
         $tool->save();

    $this->postJson('/api/v1/returns', [
        'rental_id' => $rental->id,
        'advisor_id' => $advisor->id,
        'items' => [
            [
                'rental_detail_id' => $detail->id,
                'returned_quantity' => 2,
            ],
        ],
    ])->assertStatus(409);

    $this->assertDatabaseCount('returns', 0);

    $this->assertDatabaseHas('tools', [
        'id' => $tool->id,
        'total_quantity' => 10,
        'available_quantity' => 9,
    ]);
}

public function test_can_return_a_soft_deleted_tool(): void
{
    [$customer, $advisor, $tool, $rental, $detail] =
        $this->createRentalForReturn();

    // Elimina la herramienta lógicamente antes de devolverla.
    $tool->delete();

    $this->postJson('/api/v1/returns', [
        'rental_id' => $rental->id,
        'advisor_id' => $advisor->id,
        'items' => [
            [
                'rental_detail_id' => $detail->id,
                'returned_quantity' => 1,
            ],
        ],
    ])
        ->assertCreated()
        ->assertJsonPath('success', true);

    $this->assertSoftDeleted('tools', [
        'id' => $tool->id,
    ]);

    $this->assertDatabaseHas('tools', [
        'id' => $tool->id,
        'available_quantity' => 9,
    ]);
}
}