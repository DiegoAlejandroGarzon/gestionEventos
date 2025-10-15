<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_type_id',
        'row',
        'column',
        'is_assigned',
        'event_assistant_id'
    ];

    // Relación con el tipo de ticket
    public function ticketType()
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }

    // Relación con el asistente al evento (si está asignado)
    public function eventAssistant()
    {
        return $this->belongsTo(EventAssistant::class, 'event_assistant_id');
    }
}
