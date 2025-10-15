<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEventParameter extends Model
{
    use HasFactory;

    protected $fillable = ['event_id', 'user_id', 'additional_parameter_id', 'value'];

    // Relación con el modelo Event
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // Relación con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // Relación con el modelo AdditionalParameter
    public function additionalParameter()
    {
        return $this->belongsTo(AdditionalParameter::class);
    }
}
