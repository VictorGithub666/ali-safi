<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();
        
        return redirect()->intended($this->redirectBasedOnRole($user));
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    protected function redirectBasedOnRole($user)
    {
        switch ($user->user_type) {
            case 'admin':
                return route('admin.dashboard');
            case 'vendor':
                return route('vendor.dashboard');
            case 'rider':
                return route('rider.dashboard');
            default:
                return route('customer.dashboard');
        }
    }
}