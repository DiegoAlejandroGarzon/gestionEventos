<?php

namespace App\Imports;

use App\Models\Seat;
use App\Models\TicketType;
use Maatwebsite\Excel\Concerns\ToModel;

class SeatsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    protected $ticket_type_id;

    public function __construct($ticket_type_id)
    {
        $this->ticket_type_id = $ticket_type_id;
    }

    public function model(array $row)
    {
        // Verificar si el registro ya existe
        $existingSeat = Seat::where('row', $row[0])
            ->where('column', $row[1])
            ->where('ticket_type_id', $this->ticket_type_id)
            ->first();

        // Si ya existe, retornar null para no crear un nuevo registro
        if ($existingSeat) {
            return null;
        }

        // Si no existe, crear el nuevo registro
        return new Seat([
            'row' => $row[0],
            'column' => $row[1],
            'ticket_type_id' => $this->ticket_type_id,
        ]);
    }
}
