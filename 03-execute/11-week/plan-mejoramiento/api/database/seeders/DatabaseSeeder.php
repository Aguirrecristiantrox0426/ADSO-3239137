<?php

namespace Database\Seeders;

use App\Models\Advisor;
use App\Models\Customer;
use App\Models\Tool;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Herramientas iniciales
        $tools = [
            [
                'name' => 'Martillo de construccion',
                'description' => 'Martillo de uso general para trabajos de construccion.',
                'total_quantity' => 15,
                'available_quantity' => 15,
            ],
            [
                'name' => 'Taladro percutor',
                'description' => 'Taladro percutor para perforaciones en concreto y mamposteria.',
                'total_quantity' => 8,
                'available_quantity' => 8,
            ],
            [
                'name' => 'Pulidora angular',
                'description' => 'Pulidora electrica para corte y acabado de materiales.',
                'total_quantity' => 6,
                'available_quantity' => 6,
            ],
            [
                'name' => 'Sierra circular',
                'description' => 'Sierra circular electrica para trabajos de corte.',
                'total_quantity' => 5,
                'available_quantity' => 5,
            ],
        ];

        foreach ($tools as $tool) {
            Tool::firstOrCreate(
                ['name' => $tool['name']],
                $tool
            );
        }

        // Clientes de prueba
        $customers = [
            [
                'full_name' => 'Carlos Andres Ramirez',
                'identification_number' => '1000000001',
                'phone' => '3001234567',
                'email' => 'carlos.ramirez@example.com',
            ],
            [
                'full_name' => 'Maria Fernanda Lopez',
                'identification_number' => '1000000002',
                'phone' => '3002345678',
                'email' => 'maria.lopez@example.com',
            ],
            [
                'full_name' => 'Juan David Martinez',
                'identification_number' => '1000000003',
                'phone' => '3003456789',
                'email' => 'juan.martinez@example.com',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['identification_number' => $customer['identification_number']],
                $customer
            );
        }

        // Asesores de prueba
        $advisors = [
            [
                'full_name' => 'Andres Felipe Gomez',
                'identification_number' => '2000000001',
                'email' => 'andres.gomez@example.com',
            ],
            [
                'full_name' => 'Laura Camila Torres',
                'identification_number' => '2000000002',
                'email' => 'laura.torres@example.com',
            ],
        ];

        foreach ($advisors as $advisor) {
            Advisor::firstOrCreate(
                ['identification_number' => $advisor['identification_number']],
                $advisor
            );
        }
    }
}