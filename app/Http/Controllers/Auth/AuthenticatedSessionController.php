<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Check if user is staff and restrict login if not assigned to active shift
        if ($user->role === 'staff' && $user->branch_id) {
            $activeSession = \App\Models\RegisterSession::where('branch_id', $user->branch_id)
                ->where('status', 'open')
                ->first();

            if (!$activeSession) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors(['email' => 'Your branch does not have an active shift open.']);
            }

            $isAssigned = \App\Models\Attendance::where('user_id', $user->id)
                ->where('register_session_id', $activeSession->id)
                ->where('is_assigned', true)
                ->exists();

            if (!$isAssigned) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors(['email' => 'You are not assigned to the current active shift.']);
            }

            // Log staff login
            \App\Models\BranchLog::create([
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
                'action' => 'staff_login',
                'description' => $user->name . ' logged into the system.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

