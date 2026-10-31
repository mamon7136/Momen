<?php

namespace App\Jobs;

use App\Services\Messaging\WhatsApp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWhatsAppMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120, 600];

    public function __construct(public string $to, public string $body, public ?int $storeId = null) {}

    public function handle(WhatsApp $whatsapp): void
    {
        $whatsapp->send($this->to, $this->body, $this->storeId);
    }
}
