<?php

namespace App\Services\Messaging;

use App\Models\MessageLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Business Cloud API (Meta). Business-initiated messages outside the 24h window
 * must use pre-approved templates; we send templates when $template is given, otherwise text.
 */
class WhatsApp
{
    public static function normalize(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (str_starts_with($digits, '0')) {
            $digits = '249'.substr($digits, 1);
        }
        if (strlen($digits) === 9) {
            $digits = '249'.$digits;
        }

        return $digits;
    }

    public function send(string $to, string $body, ?int $storeId = null, ?string $template = null, array $params = []): MessageLog
    {
        $to = self::normalize($to);
        $log = MessageLog::create(['store_id' => $storeId, 'channel' => 'whatsapp', 'to' => $to, 'template' => $template, 'body' => $body]);

        if (config('messaging.whatsapp.driver') !== 'cloud') {
            Log::info("[whatsapp:log] to={$to}\n{$body}");
            $log->update(['status' => 'sent']);

            return $log;
        }

        $payload = $template
            ? ['messaging_product' => 'whatsapp', 'to' => $to, 'type' => 'template', 'template' => [
                'name' => $template, 'language' => ['code' => 'ar'],
                'components' => [['type' => 'body', 'parameters' => array_map(fn ($p) => ['type' => 'text', 'text' => (string) $p], $params)]],
            ]]
            : ['messaging_product' => 'whatsapp', 'to' => $to, 'type' => 'text', 'text' => ['body' => $body, 'preview_url' => true]];

        try {
            $res = Http::withToken(config('messaging.whatsapp.token'))->timeout(15)
                ->post('https://graph.facebook.com/'.config('messaging.whatsapp.api_version').'/'.config('messaging.whatsapp.phone_number_id').'/messages', $payload);
            $log->update($res->successful()
                ? ['status' => 'sent', 'provider_id' => $res->json('messages.0.id')]
                : ['status' => 'failed', 'error' => $res->body()]);
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error' => $e->getMessage()]);
        }

        return $log;
    }

    /** Click-to-chat link (wa.me) used as a zero-cost fallback on the storefront. */
    public static function link(string $phone, string $text = ''): string
    {
        return 'https://wa.me/'.self::normalize($phone).($text ? '?text='.rawurlencode($text) : '');
    }
}
