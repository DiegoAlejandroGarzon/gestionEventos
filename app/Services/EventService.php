<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace App\Services;

use App\Models\EventAssistant;

/**
 * Description of EventService
 *
 * @author USUARIO
 */
class EventService {
    
    public function getDaysAndTimesFrees()
    {
        $days = 3;
        $eventId = 2;

        // ticket_type_id => [fecha, hora_inicio, hora_fin, capacidad]
        $ticketInfo = [
            3  => ["2025-11-15", "17:00", "17:59", 2000],
            4  => ["2025-11-15", "18:00", "18:59", 2000],
            8  => ["2025-11-15", "19:00", "19:59", 2000],
            9  => ["2025-11-15", "20:00", "20:59", 2000],
            10 => ["2025-11-15", "21:00", "21:59", 2000],
            11 => ["2025-11-15", "22:00", "22:59", 2000],
            12 => ["2025-11-15", "23:00", "23:59", 2000],
            13 => ["2025-11-16", "17:00", "17:59", 2000],
            14 => ["2025-11-16", "18:00", "18:59", 2000],
            
            15 => ["2025-10-18", "17:00", "17:59", 2000],
            16 => ["2025-10-19", "17:00", "17:59", 2000],
            17 => ["2025-10-19", "18:00", "18:59", 2000],
            //18 => ["2025-10-20", "17:00", "17:59", 2000],
        ];

        $ticketTypesByDay = [
            "2025-11-15" => [3, 4, 8, 9, 10, 11, 12],
            "2025-11-16" => [13],
            "2025-10-18" => [15],
            "2025-10-19" => [16, 17],
            "2025-10-20" => [18],
        ];

        $result = [];

        for ($i = 0; $i < $days; $i++) {
            $currentDate = now()->addDays($i)->format('Y-m-d');

            if (!isset($ticketTypesByDay[$currentDate])) {
                continue;
            }

            $idsDelDia = $ticketTypesByDay[$currentDate];

            // Obtener la cantidad de asistentes por ticket_type_id
            $usedTickets = \App\Models\EventAssistant::where('event_id', $eventId)
                ->whereIn('ticket_type_id', $idsDelDia)
                ->where('rejected', 0)
                ->selectRaw('ticket_type_id, COUNT(*) as total')
                ->groupBy('ticket_type_id')
                ->pluck('total', 'ticket_type_id')
                ->toArray();

            $result[$currentDate] = [];

            foreach ($idsDelDia as $ticketId) {
                if (!isset($ticketInfo[$ticketId])) continue;

                [$fecha, $start, $end, $capacity] = $ticketInfo[$ticketId];
                $used = $usedTickets[$ticketId] ?? 0;
                $available = $capacity - $used;

                $result[$currentDate][] = [
                    'ticket_type_id' => $ticketId,
                    'start' => $start,
                    'end' => $end,
                    'available' => $available > 0
                ];
            }
        }

        return $result;
    }

}
