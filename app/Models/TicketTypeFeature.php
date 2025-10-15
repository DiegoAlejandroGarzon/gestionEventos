<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketTypeFeature extends Model
{
    use HasFactory;

    protected $table = 'ticket_type_feature';

    protected $fillable = [
        'ticket_type_id',
        'ticket_feature_id',
    ];

    // Relación con el modelo TicketType
    public function ticketType()
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }

    // Relación con el modelo TicketFeature
    public function ticketFeature()
    {
        return $this->belongsTo(TicketFeatures::class, 'ticket_feature_id');
    }
}
