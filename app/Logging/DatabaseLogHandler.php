<?php

namespace App\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;
use Monolog\LogRecord;

class DatabaseLogHandler extends AbstractProcessingHandler
{
    public function __construct($level = Logger::ERROR, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    /**
     * Este método debe ser compatible con AbstractProcessingHandler::write()
     *
     * @param array $record
     * @return void
     */
    protected function write(LogRecord $record): void
    {
        try {
            // Evitar usar request() en contextos CLI (cron, queue) — proteger con null coalescing
            $ip = null;
            $browser = null;
            try {
                $ip = request()?->ip();
                $browser = request()?->header('User-Agent');
            } catch (\Throwable $_) {
                // request() puede fallar en CLI; dejar null
            }

            SystemLog::create([
                'user_id' => Auth::id(),
                'action'  => $record['message'] ?? 'log',
                'module'  => $record['channel'] ?? 'system',
                'ip'      => $ip,
                'browser' => $browser,
                'result'  => 'error', // puedes mapear $record['level_name'] a success/error si quieres
                'details' => isset($record['context']) ? json_encode($record['context']) : null,
            ]);
        } catch (\Throwable $e) {
            // prevenir bucle infinito de logging si falla la escritura en DB
        }
    }
}
