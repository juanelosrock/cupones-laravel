<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            if (Auth::user()->status !== 'active') {
                Auth::logout();
                LoginHistory::create([
                    'user_id' => null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status' => 'failed',
                    'email' => $request->email,
                    'created_at' => now(),
                ]);
                throw ValidationException::withMessages(['email' => 'Tu cuenta está inactiva.']);
            }

            $request->session()->regenerate();

            LoginHistory::create([
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'success',
                'email' => $request->email,
                'created_at' => now(),
            ]);

            return redirect()->intended(route('admin.dashboard'));
        }

        LoginHistory::create([
            'user_id' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'failed',
            'email' => $request->email,
            'created_at' => now(),
        ]);

        // Detectar fuerza bruta
        $recentFails = LoginHistory::where('ip_address', $request->ip())
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        if ($recentFails >= 5) {
            AuditService::alert('brute_force', 'high', "Múltiples intentos fallidos desde {$request->ip()}", [
                'email' => $request->email, 'attempts' => $recentFails
            ]);
        }

        throw ValidationException::withMessages(['email' => 'Credenciales inválidas.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}