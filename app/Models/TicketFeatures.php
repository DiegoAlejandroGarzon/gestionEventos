<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketFeatures extends Model
{
    use HasFactory;

    protected $table = "ticket_features";

    protected $fillable = [
        'name',
        'consumable',
        'featured',
    ];

    public function ticketTypes()
    {
        return $this->belongsToMany(TicketType::class, 'ticket_type_feature', 'ticket_feature_id', 'ticket_type_id');
    }

    public function ticketTypes2()
    {
        return $this->belongsToMany(TicketType::class, 'ticket_type_feature');
    }

    public function characteristics()
    {
        return $this->belongsToMany(
            TicketCharacteristic::class,
            'characteristic_ticket_feature',
            'ticket_feature_id',
            'ticket_characteristic_id'
        );
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }
}
