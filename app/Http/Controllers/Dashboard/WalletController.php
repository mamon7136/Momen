<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(WalletService $wallets)
    {
        $wallet = $wallets->for(current_store());

        return view('dashboard.wallet', [
            'wallet' => $wallet,
            'transactions' => $wallet->transactions()->paginate(30),
            'payouts' => Payout::where('store_id', current_store()->id)->latest()->limit(10)->get(),
        ]);
    }

    public function payout(Request $request, WalletService $wallets)
    {
        $data = $request->validate(['amount' => 'required|numeric|min:1000', 'method' => 'required|in:bank_transfer,mbok', 'account' => 'required|string|max:120']);
        try {
            $wallets->requestPayout(current_store(), (float) $data['amount'], $data['method'], $data['account']);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'تم إرسال طلب السحب، سيتم التحويل خلال 48 ساعة عمل');
    }
}
