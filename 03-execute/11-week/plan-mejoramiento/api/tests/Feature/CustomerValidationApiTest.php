<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerValidationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_update_customer_with_empty_full_name(): void
    {
        $customer = Customer::create([
            'full_name' => 'Carlos Ramirez',
            'identification_number' => '123456789',
            'phone' => '3001234567',
            'email' => 'carlos@example.com',
        ]);

        $this->putJson("/api/v1/customers/{$customer->id}", [
            'full_name' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['full_name']);
    }

    public function test_cannot_update_customer_with_duplicate_identification(): void
    {
        Customer::create([
            'full_name' => 'Ana Martinez',
            'identification_number' => '987654321',
            'phone' => '3101234567',
            'email' => 'ana@example.com',
        ]);

        $customer = Customer::create([
            'full_name' => 'Carlos Ramirez',
            'identification_number' => '123456789',
            'phone' => '3001234567',
            'email' => 'carlos@example.com',
        ]);

        $this->putJson("/api/v1/customers/{$customer->id}", [
            'identification_number' => '987654321',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['identification_number']);
    }

    public function test_cannot_update_customer_with_empty_phone(): void
    {
        $customer = Customer::create([
            'full_name' => 'Carlos Ramirez',
            'identification_number' => '123456789',
            'phone' => '3001234567',
            'email' => 'carlos@example.com',
        ]);

        $this->putJson("/api/v1/customers/{$customer->id}", [
            'phone' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_cannot_update_customer_with_invalid_email(): void
    {
        $customer = Customer::create([
            'full_name' => 'Carlos Ramirez',
            'identification_number' => '123456789',
            'phone' => '3001234567',
            'email' => 'carlos@example.com',
        ]);

        $this->putJson("/api/v1/customers/{$customer->id}", [
            'email' => 'correo-invalido',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}