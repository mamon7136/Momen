<?php

namespace App\Services\Messaging;

use App\Models\MessageLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generic HTTP SMS gateway (local aggregators reaching Zain / MTN / Sudani).
 * Most Sudanese aggregators expose a GET/POST endpoint: user, pass, sender, to, text.
 */
class Sms
{
    public function send(string $to, string $text): MessageLog
    {
        $to = WhatsApp::normalize($to);
        $log = MessageLog::create(['channel' => 'sms', 'to' => $to, 'body' => $text]);

        if (config('messaging.sms.driver') !== 'http') {
            Log::info("[sms:log] to={$to} {$text}");
            $log->update(['status' => 'sent']);

            return $log;
        }

        try {
            $res = Http::timeout(15)->asForm()->post(config('messaging.sms.url'), [
                'username' => config('messaging.sms.username'),
                'password' => config('messaging.sms.password'),
                'sender' => config('messaging.sms.sender'),
                'to' => $to,
                'text' => $text,
                'unicode' => 1,
            ]);
            $log->update(['status' => $res->successful() ? 'sent' : 'failed', 'error' => $res->successful() ? null : $res->body()]);
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error' => $e->getMessage()]);
        }

        return $log;
    }
}
