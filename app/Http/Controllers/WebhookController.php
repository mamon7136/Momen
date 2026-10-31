<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StorePaymentMethod;
use App\Services\Payments\PaymentManager;
use App\Support\Tenancy\CurrentStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function syberpay(Request $request, Store $store, PaymentManager $payments, CurrentStore $tenancy)
    {
        return $tenancy->runAs($store, function () use ($request, $payments) {
            $method = StorePaymentMethod::where('driver', 'syberpay')->firstOrFail();
            $payment = $payments->driver($method)->callback($request);

            return response()->json(['ok' => (bool) $payment]);
        });
    }

    public function whatsappVerify(Request $request)
    {
        abort_unless($request->query('hub_verify_token') === config('messaging.whatsapp.verify_token'), 403);

        return response($request->query('hub_challenge'));
    }

    /** Delivery receipts + inbound messages (future: merchant approves receipts by replying "تأكيد 10023"). */
    public function whatsapp(Request $request)
    {
        Log::info('whatsapp webhook', $request->all());

        return response()->json(['ok' => true]);
    }
}
