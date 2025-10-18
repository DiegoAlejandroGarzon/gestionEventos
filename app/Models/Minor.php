<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Minor extends Model
{
    use HasFactory;
    protected $fillable = ['full_name', 'age', 'event_assistant_id'];

    public function eventAssistant()
    {
        return $this->belongsTo(EventAssistant::class);
    }
}
