<?php
/**
 * Description of security_roles
 *
 * @author programador1
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NumbersWhatsappSysUser extends Model
{
    use SoftDeletes;

    protected $table = 'numbers_whatsapp_sys_user';

    protected $fillable = [
        'sys_users_id',
        'whatsapp_number',
        'phone_number_id',
        'whatsapp_business_id',
        'token',
        'alias',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // 🔁 Relación con User (ajusta si tu modelo se llama diferente)
    public function user()
    {
        return $this->belongsTo(User::class, 'sys_users_id');
    }
}