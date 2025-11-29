<?php

namespace App\Traits;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public function logActivity($action, $module, $result = 'success', $details = null)
    {
        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'ip' => request()->ip(),
            'browser' => request()->header('User-Agent'),
            'result' => $result,
            'details' => is_array($details) ? json_encode($details) : $details,
        ]);
    }
}
