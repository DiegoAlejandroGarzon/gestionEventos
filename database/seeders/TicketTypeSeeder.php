<?php

namespace Database\Seeders;

use App\Models\TicketType;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * TIPOS DE TICKETS PARA EL EVENTO DE EL PESEBRE MAS GRANDE DEL MUNDO
     */
    public function run()
    {
        $eventId = 2;

        // Rango de fechas
        $startDate = Carbon::createFromFormat('Y-m-d', '2025-11-15');
        $endDate = Carbon::createFromFormat('Y-m-d', '2026-01-15');

        // Horarios en formato 24 horas (PM)
        $horarios = [
            ['start' => '17:00:00', 'end' => '17:59:59'], // 5 a 6 PM
            ['start' => '18:00:00', 'end' => '18:59:59'], // 6 a 7 PM
            ['start' => '19:00:00', 'end' => '19:59:59'], // 7 a 8 PM
            ['start' => '20:00:00', 'end' => '20:59:59'], // 8 a 9 PM
            ['start' => '21:00:00', 'end' => '21:59:59'], // 9 a 10 PM
            ['start' => '22:00:00', 'end' => '22:59:59'], // 10 a 11 PM
            ['start' => '23:00:00', 'end' => '23:59:59'], // 11 a 12 PM
        ];

        $tickets = [];

        // Iterar sobre cada día
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {

            foreach ($horarios as $h) {
                $tickets[] = [
                    'event_id' => $eventId,
                    // 'name' => 'Ingreso ' . ucfirst($date->translatedFormat('l d/m/Y')) . ' - ' . substr($h['start'], 0, 5) . ' a ' . substr($h['end'], 0, 5),
                    'name' => $date->format('d-m-Y') . ' ' . (int)date('g', strtotime($h['start'])) . '-' . ((int)date('g', strtotime($h['end']))+1),
                    'capacity' => 2000, // puedes ajustar el aforo
                    'price' => 0, // o el valor que necesites
                    'entry_date' => $date->format('Y-m-d'),
                    'entry_start_time' => $h['start'],
                    'entry_end_time' => $h['end'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Inserta todo de una vez
        TicketType::insert($tickets);

        $this->command->info('Se han creado ' . count($tickets) . ' tipos de ticket para el evento ID=2.');
    }
}
