<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'ip',
        'browser',
        'result',
        'details',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
