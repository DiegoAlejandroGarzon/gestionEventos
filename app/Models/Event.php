<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'header_image_path',
        'created_by',
        'additionalFields',
        'end_time',
        'start_time',
        'event_date',
        'city_id',
        'public_link',
        'registration_parameters',
        'status',
        'address',
        'event_date_end',
        'color_one',
        'color_two',
    ];


    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function assistants()
    {
        return $this->hasMany(EventAssistant::class);
    }

    public function additionalParameters()
    {
        return $this->hasMany(AdditionalParameter::class, 'event_id');
    }
}
