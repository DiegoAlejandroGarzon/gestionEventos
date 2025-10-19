<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConversationsMessages extends Model
{
    use HasFactory, SoftDeletes;

    // Si el nombre de la tabla no sigue la convención plural, se puede especificar
    protected $table = 'conversations_messages';

    // Asumiendo que las columnas "created_at" y "updated_at" existen, no es necesario configurar timestamps
    // Si quieres definir qué campos pueden asignarse masivamente:
    protected $fillable = [
        'users_id',
        'conversations_id',
        'is_read',
        'content',
        'content_bot',
        'content_response',
        'direction',
        'message_what_id',
        'type',
        'origin',
        'origin_bot_type',
        'url_file',
        'created_by',
        'updated_by',
        'received_at',
    ];

    // Define los campos de tipo fecha para que Laravel los maneje como Carbon
    protected $dates = [
        'received_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Puedes agregar relaciones, por ejemplo con Usuario o Conversación
    public function user()
    {
        return $this->belongsTo(User::class, 'sys_users_id');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversations_id');
    }
}
