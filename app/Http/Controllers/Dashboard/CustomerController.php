<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::when($request->q, fn ($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%"))
            ->orderByDesc('total_spent')->paginate(30)->withQueryString();

        return view('dashboard.customers', compact('customers'));
    }
}
