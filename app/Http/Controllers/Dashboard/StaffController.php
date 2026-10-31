<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Messaging\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index()
    {
        return view('dashboard.staff', ['staff' => current_store()->staff()->get(), 'roles' => config('platform.role_labels')]);
    }

    public function store(Request $request)
    {
        $store = current_store();
        if ($store->plan?->max_staff && $store->staff()->count() >= $store->plan->max_staff) {
            return back()->with('error', 'وصلت للحد الأقصى من الموظفين في باقتك');
        }
        $request->merge(['phone' => WhatsApp::normalize((string) $request->phone)]);
        $data = $request->validate([
            'name' => 'required|string|max:80', 'phone' => 'required|string|min:11',
            'role' => ['required', Rule::in(['manager', 'staff', 'accountant', 'shipping'])],
        ]);

        $password = null;
        $user = User::where('phone', $data['phone'])->first();
        if (! $user) {
            $password = Str::password(10, symbols: false);
            $user = User::create(['name' => $data['name'], 'phone' => $data['phone'], 'email' => $data['phone'].'@staff.local', 'password' => $password]);
        }
        $store->staff()->syncWithoutDetaching([$user->id => ['role' => $data['role']]]);

        app(WhatsApp::class)->send($data['phone'], 'تمت إضافتك كـ'.config('platform.role_labels.'.$data['role'])." في متجر {$store->name}.\nالدخول: ".route('login')
            .($password ? "\nرقم الهاتف: {$data['phone']}\nكلمة المرور المؤقتة: {$password}" : ''), $store->id);

        return back()->with('status', 'تمت إضافة الموظف وإرسال بيانات الدخول عبر واتساب');
    }

    public function destroy(User $user)
    {
        $store = current_store();
        abort_if($user->id === $store->owner_id, 422);
        $store->staff()->detach($user->id);

        return back()->with('status', 'تم حذف الموظف');
    }
}
