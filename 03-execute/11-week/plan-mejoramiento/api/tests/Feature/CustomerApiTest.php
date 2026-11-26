<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_a_customer(): void
    {
        $this->postJson('/api/v1/customers', [
            'full_name' => 'Carlos Ramirez',
            'identification_number' => '123456789',
            'phone' => '3001234567',
            'email' => 'carlos@example.com',
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.full_name', 'Carlos Ramirez')
            ->assertJsonPath('data.identification_number', '123456789');
    }

    public function test_can_list_customers(): void
    {
        Customer::create([
            'full_name' => 'Carlos Ramirez',
            'identification_number' => '123456789',
            'phone' => '3001234567',
            'email' => 'carlos@example.com',
        ]);

        $this->getJson('/api/v1/customers')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_can_show_a_customer(): void
    {
        $customer = Customer::create([
            'full_name' => 'Ana Martinez',
            'identification_number' => '987654321',
            'phone' => '3101234567',
            'email' => 'ana@example.com',
        ]);

        $this->getJson("/api/v1/customers/{$customer->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $customer->id)
            ->assertJsonPath('data.full_name', 'Ana Martinez');
    }

    public function test_can_update_a_customer(): void
    {
        $customer = Customer::create([
            'full_name' => 'Carlos Ramirez',
            'identification_number' => '123456789',
            'phone' => '3001234567',
            'email' => 'carlos@example.com',
        ]);

        $this->putJson("/api/v1/customers/{$customer->id}", [
            'full_name' => 'Carlos Ramirez Gomez',
            'phone' => '3119876543',
        ])
            ->assertOk()
            ->assertJsonPath('data.full_name', 'Carlos Ramirez Gomez')
            ->assertJsonPath('data.phone', '3119876543');
    }

    public function test_cannot_register_customers_with_duplicate_identification(): void
    {
        Customer::create([
            'full_name' => 'Carlos Ramirez',
            'identification_number' => '123456789',
            'phone' => '3001234567',
            'email' => 'carlos@example.com',
        ]);

        $this->postJson('/api/v1/customers', [
            'full_name' => 'Otro Cliente',
            'identification_number' => '123456789',
            'phone' => '3101234567',
            'email' => 'otro@example.com',
        ])
            ->assertUnprocessable();
    }

    public function test_can_soft_delete_a_customer(): void
    {
        $customer = Customer::create([
            'full_name' => 'Ana Martinez',
            'identification_number' => '987654321',
            'phone' => '3101234567',
            'email' => 'ana@example.com',
        ]);

        $this->deleteJson("/api/v1/customers/{$customer->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);
    }

    // Additional validation tests

    public function test_cannot_register_customer_without_full_name(): void
    {
        $this->postJson('/api/v1/customers', [
            'identification_number' => '123456789',
            'phone' => '3001234567',
            'email' => 'carlos@example.com',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['full_name']);
    }

    public function test_cannot_register_customer_without_identification_number(): void
    {
        $this->postJson('/api/v1/customers', [
            'full_name' => 'Carlos Ramirez',
            'phone' => '3001234567',
            'email' => 'carlos@example.com',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['identification_number']);
    }

    public function test_cannot_register_customer_without_phone(): void
    {
        $this->postJson('/api/v1/customers', [
            'full_name' => 'Carlos Ramirez',
            'identification_number' => '123456789',
            'email' => 'carlos@example.com',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_cannot_register_customer_with_invalid_email(): void
    {
        $this->postJson('/api/v1/customers', [
            'full_name' => 'Carlos Ramirez',
            'identification_number' => '123456789',
            'phone' => '3001234567',
            'email' => 'correo-invalido',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
