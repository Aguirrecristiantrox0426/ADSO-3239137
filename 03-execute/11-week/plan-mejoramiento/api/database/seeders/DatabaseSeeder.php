<?php

namespace Database\Seeders;

use App\Models\Advisor;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Rental;
use App\Models\RentalDetail;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Cuentas de demostración. En otros entornos, configure estas variables.
        // updateOrCreate también actualiza las cuentas que ya existían en un
        // volumen MySQL persistente. firstOrCreate dejaba intacta una contraseña
        // anterior y provocaba que las credenciales de demostración no funcionaran.
        $admin = User::updateOrCreate(
            ['email' => env('DEMO_ADMIN_EMAIL', 'admin@renttools.test')],
            [
                'name' => 'Administrador RENT-TOOLS',
                'password' => Hash::make(env('DEMO_ADMIN_PASSWORD', 'Admin123!')),
                'role' => 'administrator',
                'is_active' => true,
            ]
        );

        $advisorUser = User::updateOrCreate(
            ['email' => env('DEMO_ADVISOR_EMAIL', 'asesor@renttools.test')],
            [
                'name' => 'Laura Camila Torres',
                'password' => Hash::make(env('DEMO_ADVISOR_PASSWORD', 'Asesor123!')),
                'role' => 'advisor',
                'is_active' => true,
            ]
        );

        $advisor = Advisor::firstOrCreate(
            ['identification_number' => '2000000002'],
            [
                'user_id' => $advisorUser->id,
                'full_name' => 'Laura Camila Torres',
                'email' => $advisorUser->email,
            ]
        );
        if (!$advisor->user_id) {
            $advisor->user_id = $advisorUser->id;
            $advisor->save();
        }

        $categories = [];
        foreach ([
            ['Herramientas eléctricas', 'Equipos eléctricos para construcción y mantenimiento.'],
            ['Herramientas manuales', 'Herramientas de uso manual.'],
            ['Equipos de corte', 'Equipos para corte y acabado de materiales.'],
        ] as [$name, $description]) {
            $categories[$name] = Category::firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }

        $tools = [
            ['name' => 'Martillo de construcción', 'description' => 'Martillo de uso general para trabajos de construcción.', 'total_quantity' => 15, 'available_quantity' => 15, 'category_id' => $categories['Herramientas manuales']->id],
            ['name' => 'Taladro percutor', 'description' => 'Taladro percutor para perforaciones en concreto y mampostería.', 'total_quantity' => 8, 'available_quantity' => 8, 'category_id' => $categories['Herramientas eléctricas']->id],
            ['name' => 'Pulidora angular', 'description' => 'Pulidora eléctrica para corte y acabado de materiales.', 'total_quantity' => 6, 'available_quantity' => 6, 'category_id' => $categories['Herramientas eléctricas']->id],
            ['name' => 'Sierra circular', 'description' => 'Sierra circular eléctrica para trabajos de corte.', 'total_quantity' => 5, 'available_quantity' => 5, 'category_id' => $categories['Equipos de corte']->id],
        ];
        foreach ($tools as $tool) {
            Tool::firstOrCreate(['name' => $tool['name']], $tool);
        }

        $customers = [
            ['full_name' => 'Carlos Andrés Ramírez', 'identification_number' => '1000000001', 'phone' => '3001234567', 'email' => 'carlos.ramirez@example.com'],
            ['full_name' => 'María Fernanda López', 'identification_number' => '1000000002', 'phone' => '3002345678', 'email' => 'maria.lopez@example.com'],
            ['full_name' => 'Juan David Martínez', 'identification_number' => '1000000003', 'phone' => '3003456789', 'email' => 'juan.martinez@example.com'],
        ];
        foreach ($customers as $customer) {
            Customer::firstOrCreate(['identification_number' => $customer['identification_number']], $customer);
        }

        // Un alquiler inicial permite demostrar el dashboard y el flujo de devoluciones.
        $customer = Customer::where('identification_number', '1000000001')->first();
        $tool = Tool::where('name', 'Taladro percutor')->first();
        $rental = Rental::firstOrCreate(
            [
                'customer_id' => $customer->id,
                'advisor_id' => $advisor->id,
                'rental_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
            ],
            ['status' => 'active']
        );

        if ($rental->wasRecentlyCreated && $tool && $tool->available_quantity > 0) {
            RentalDetail::create([
                'rental_id' => $rental->id,
                'tool_id' => $tool->id,
                'tool_name_snapshot' => $tool->name,
                'quantity' => 1,
            ]);
            $tool->decrement('available_quantity', 1);
        }
    }
}
