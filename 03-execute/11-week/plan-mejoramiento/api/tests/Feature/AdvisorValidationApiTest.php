<?php

namespace Tests\Feature;

use App\Models\Advisor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvisorValidationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticateAs('administrator');
    }

    public function test_cannot_update_advisor_with_empty_full_name(): void
    {
        $advisor = Advisor::create([
            'full_name' => 'Andres Gomez',
            'identification_number' => '2000000001',
            'email' => 'andres@example.com',
        ]);

        $this->putJson("/api/v1/advisors/{$advisor->id}", [
            'full_name' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['full_name']);
    }

    public function test_cannot_update_advisor_with_duplicate_identification(): void
    {
        Advisor::create([
            'full_name' => 'Laura Torres',
            'identification_number' => '2000000002',
            'email' => 'laura@example.com',
        ]);

        $advisor = Advisor::create([
            'full_name' => 'Andres Gomez',
            'identification_number' => '2000000001',
            'email' => 'andres@example.com',
        ]);

        $this->putJson("/api/v1/advisors/{$advisor->id}", [
            'identification_number' => '2000000002',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['identification_number']);
    }

    public function test_cannot_update_advisor_with_invalid_email(): void
    {
        $advisor = Advisor::create([
            'full_name' => 'Andres Gomez',
            'identification_number' => '2000000001',
            'email' => 'andres@example.com',
        ]);

        $this->putJson("/api/v1/advisors/{$advisor->id}", [
            'email' => 'correo-invalido',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_cannot_update_advisor_with_duplicate_email(): void
    {
        Advisor::create([
            'full_name' => 'Laura Torres',
            'identification_number' => '2000000002',
            'email' => 'laura@example.com',
        ]);

        $advisor = Advisor::create([
            'full_name' => 'Andres Gomez',
            'identification_number' => '2000000001',
            'email' => 'andres@example.com',
        ]);

        $this->putJson("/api/v1/advisors/{$advisor->id}", [
            'email' => 'laura@example.com',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
