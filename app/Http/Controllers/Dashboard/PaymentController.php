<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\StorePaymentMethod;
use App\Services\Payments\PaymentManager;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function approve(Request $request, Payment $payment, PaymentService $service)
    {
        $service->approve($payment, $request->user()->id, $request->input('note'));

        return back()->with('status', 'تم تأكيد الدفع وإشعار العميل');
    }

    public function reject(Request $request, Payment $payment, PaymentService $service)
    {
        $data = $request->validate(['reason' => 'required|string|max:200']);
        $service->reject($payment, $request->user()->id, $data['reason']);

        return back()->with('status', 'تم رفض الإيصال وإشعار العميل');
    }

    public function receipt(Payment $payment)
    {
        abort_unless($payment->receipt_path && Storage::disk('local')->exists($payment->receipt_path), 404);

        return Storage::disk('local')->response($payment->receipt_path);
    }

    public function methods()
    {
        return view('dashboard.payment-methods', [
            'methods' => StorePaymentMethod::orderBy('sort')->get()->keyBy('driver'),
            'drivers' => PaymentManager::availableDrivers(),
        ]);
    }

    public function saveMethod(Request $request)
    {
        $data = $request->validate([
            'driver' => ['required', Rule::in(array_keys(PaymentManager::availableDrivers()))],
            'label' => 'required|string|max:80',
            'instructions' => 'nullable|string|max:1000',
            'config' => 'nullable|array',
            'config.*' => 'nullable|string|max:200',
            'fee_percent' => 'nullable|numeric|min:0|max:20',
        ]);
        StorePaymentMethod::updateOrCreate(['driver' => $data['driver'], 'store_id' => current_store()->id], [
            'label' => $data['label'], 'instructions' => $data['instructions'] ?? null,
            'config' => array_merge($data['config'] ?? [], ['fee_percent' => $data['fee_percent'] ?? 0]),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'تم حفظ وسيلة الدفع');
    }
}
