<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversations extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'conversations';

    protected $fillable = [
        'users_id',
        'number_whatsapp_sysuser_id',
        'external_phone_number',
        'started_at',
        'last_message_at',
        'last_message_truncated',
        'is_external',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_external' => 'boolean',
        'started_at' => 'datetime',
        'last_message_at' => 'datetime',
    ];

    protected $dates = [
        'started_at',
        'last_message_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function messages()
    {
        return $this->hasMany(ConversationsMessage::class, 'conversations_id');
    }
}
