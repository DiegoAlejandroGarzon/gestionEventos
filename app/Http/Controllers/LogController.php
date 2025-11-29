<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class LogController extends Controller
{

    public function index()
    {
        $logs = SystemLog::with('user')
            ->when(request('user'), fn($q) => $q->where('user_id', request('user')))
            ->when(request('module'), fn($q) => $q->where('module', request('module')))
            ->when(request('result'), fn($q) => $q->where('result', request('result')))
            ->when(request('date_from'), fn($q) => $q->whereDate('created_at', '>=', request('date_from')))
            ->when(request('date_to'), fn($q) => $q->whereDate('created_at', '<=', request('date_to')))
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('logs.index', compact('logs'));
    }
}
