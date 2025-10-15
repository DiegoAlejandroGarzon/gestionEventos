<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketCharacteristic extends Model
{
    use HasFactory;

    // ✅ Permitir asignación masiva
    protected $fillable = ['name'];

    public function ticketTypes()
    {
        return $this->belongsToMany(TicketType::class, 'characteristic_ticket_type')
                ->withTimestamps();
    }
}
