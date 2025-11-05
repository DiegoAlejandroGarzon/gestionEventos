<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Models\EventAssistant;
use App\Models\Minor;
use Illuminate\Support\Facades\DB;

class PilotAssistantsSeeder extends Seeder
{
    public function run()
    {
        $cantidadDeAsistentes = 2000;

        $this->command->info('🚀 Iniciando generación masiva de asistentes...');
        // ⚙️ Crear o usar un evento de prueba
        $event = Event::find(2);

        if (!$event) {
            $this->command->error('❌ No hay eventos registrados en la base de datos.');
            return;
        }

        // Obtener IDs de tickets válidos
        $ticketIds = DB::table('ticket_types')
            ->where('event_id', $event->id)
            ->pluck('id')
            ->toArray();

        if (empty($ticketIds)) {
            $this->command->error('⚠️ No hay tickets disponibles.');
            return;
        }

        // Crear usuarios + asistentes
        $total = $cantidadDeAsistentes;
        for ($i = 1; $i <= $total; $i++) {
            $document = 1000000000 + $i;

            // Crear usuario si no existe
            $user = User::firstOrCreate(
                ['document_number' => (string) $document],
                [
                    'name' => "Usuario $i",
                    'lastname' => "apellido_$i",
                    'email' => "usuario$i@example.com",
                    'password' => bcrypt('123456'),
                    'phone' => "30$i",
                ]
            );

            // Ticket aleatorio
            $ticketId = $ticketIds[array_rand($ticketIds)];

            // Guardian aleatorio (10% de los casos)
            $guardianId = rand(1, 100) <= 10 ? rand(1, $total) : null;

            // Crear registro principal
            $assistant = EventAssistant::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'ticket_type_id' => $ticketId,
                'guardian_id' => $guardianId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crear menores asociados (0–3 por asistente)
            $minorCount = rand(0, 3);
            if ($minorCount > 0) {
                for ($m = 1; $m <= $minorCount; $m++) {
                    Minor::create([
                        'event_assistant_id' => $assistant->id,
                        'full_name' => "Menor {$i}_{$m}",
                        'age' => rand(1, 12),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Feedback por lote
            if ($i % 200 === 0) {
                $this->command->info("✅ Insertados $i registros...");
            }
        }

        $this->command->info("🎉 Se generaron correctamente {$total} asistentes con menores aleatorios.");
    }
}
