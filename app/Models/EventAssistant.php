<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventAssistant extends Model
{
    use HasFactory;

    protected $table = "event_assistants";

    protected $fillable = [
        'event_id',
        'user_id',
        'ticket_type_id',
        'has_entered',
        'qrCode',
        'guid',
        'entry_time',
        'rejected',
        'rejected_time',
        'guardian_id',
        'is_paid',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }

    // Define la relación con los parámetros adicionales
    public function eventParameters()
    {
        return $this->hasMany(UserEventParameter::class, 'user_id', 'user_id')
                    ->where('event_id', $this->event_id);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Función para sumar todos los pagos asociados al EventAssistant
    public function totalPayments()
    {
        // Suma el campo 'amount' de todos los pagos asociados
        return $this->payments->sum('amount');
    }

    // Función para verificar si los pagos cubren o superan el valor del ticket
    public function isFullyPaid()
    {
        // Verifica si el total de pagos es mayor o igual al precio del ticket
        return $this->totalPayments() >= $this->ticketType->price;
    }

    public function guardian()
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }

    public function featureConsumptions()
    {
        return $this->hasMany(FeatureConsumption::class, 'event_assistant_id');
    }
}
