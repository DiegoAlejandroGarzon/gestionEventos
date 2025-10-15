<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;
    protected $fillable = [
        'event_id',
        'name',
        'price',
        'capacity'
    ];

    // Relación con el evento
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function features()
    {
        return $this->belongsToMany(TicketFeatures::class, 'ticket_type_feature', 'ticket_type_id', 'ticket_feature_id');
    }

    public function ticketTypeFeature()
    {
        return $this->belongsToMany(TicketTypeFeature::class, 'ticket_type_feature');
    }

    public function EventAssistant()
    {
        return $this->hasMany(EventAssistant::class, 'ticket_type_id');
    }

    // Relación con los asientos
    public function seats()
    {
        return $this->hasMany(Seat::class, 'ticket_type_id');
    }

    public function formattedPrice()
    {
        return number_format($this->price, 0, ',', '.');
    }
}
