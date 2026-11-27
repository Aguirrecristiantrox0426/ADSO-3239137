<?php

namespace Tests\Feature;

use App\Models\Advisor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvisorApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticateAs('administrator');
    }

    public function test_can_register_an_advisor(): void
    {
        $this->postJson('/api/v1/advisors', [
            'full_name' => 'Andres Gomez',
            'identification_number' => '123456789',
            'email' => 'andres@example.com',
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.full_name', 'Andres Gomez')
            ->assertJsonPath('data.identification_number', '123456789');
    }

    public function test_can_list_advisors(): void
    {
        Advisor::create([
            'full_name' => 'Andres Gomez',
            'identification_number' => '123456789',
            'email' => 'andres@example.com',
        ]);

        $this->getJson('/api/v1/advisors')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_can_show_an_advisor(): void
    {
        $advisor = Advisor::create([
            'full_name' => 'Laura Martinez',
            'identification_number' => '987654321',
            'email' => 'laura@example.com',
        ]);

        $this->getJson("/api/v1/advisors/{$advisor->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $advisor->id)
            ->assertJsonPath('data.full_name', 'Laura Martinez');
    }

    public function test_can_update_an_advisor(): void
    {
        $advisor = Advisor::create([
            'full_name' => 'Andres Gomez',
            'identification_number' => '123456789',
            'email' => 'andres@example.com',
        ]);

        $this->putJson("/api/v1/advisors/{$advisor->id}", [
            'full_name' => 'Andres Felipe Gomez',
            'email' => 'andresfelipe@example.com',
        ])
            ->assertOk()
            ->assertJsonPath('data.full_name', 'Andres Felipe Gomez')
            ->assertJsonPath('data.email', 'andresfelipe@example.com');
    }

    public function test_cannot_register_advisors_with_duplicate_identification(): void
    {
        Advisor::create([
            'full_name' => 'Andres Gomez',
            'identification_number' => '123456789',
            'email' => 'andres@example.com',
        ]);

        $this->postJson('/api/v1/advisors', [
            'full_name' => 'Otro Asesor',
            'identification_number' => '123456789',
            'email' => 'otro@example.com',
        ])
            ->assertUnprocessable();
    }

    public function test_can_soft_delete_an_advisor(): void
    {
        $advisor = Advisor::create([
            'full_name' => 'Laura Martinez',
            'identification_number' => '987654321',
            'email' => 'laura@example.com',
        ]);

        $this->deleteJson("/api/v1/advisors/{$advisor->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('advisors', [
            'id' => $advisor->id,
        ]);
    }
}
