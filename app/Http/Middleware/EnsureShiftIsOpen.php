<?php

namespace App\Http\Middleware;

use App\Models\RegisterSession;
use App\Models\Scopes\BranchScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShiftIsOpen
{
    /**
     * Handle an incoming request.
     * Ensure branch / workshop has an active open shift before allowing internal operational pages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super Admin has full unrestricted bypass across the entire system
        if ($user->role === 'super_admin' || $user->role === 'owner') {
            return $next($request);
        }

        // Accountant, HR Manager, and dual role do not run register shifts
        if (in_array($user->role, ['accountant', 'hr_manager', 'accountant_and_hr_manager', 'rider'])) {
            return $next($request);
        }

        // Determine active branch
        $branchId = session('active_branch_id') ?? $user->branch_id;

        if (!$branchId) {
            return $next($request);
        }

        // Check if shift open state is cached for this request/session lifecycle
        $cacheKey = "branch_shift_open_{$branchId}";
        $activeShift = session()->get($cacheKey);

        if ($activeShift === null) {
            $activeShift = RegisterSession::withoutGlobalScope(BranchScope::class)
                ->where('branch_id', $branchId)
                ->where('status', 'open')
                ->exists();

            // Cache in session
            session()->put($cacheKey, $activeShift);
        }

        if (!$activeShift) {
            // Determine target dashboard redirect based on user role / branch type
            $isWorkshop = ($user->role === 'workshop_admin') || ($user->branch && $user->branch->type === 'workshop');
            $dashboardRoute = $isWorkshop ? 'workshop.dashboard' : 'dashboard';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Shift is closed. Please open your shift register to perform this action.',
                    'redirect' => route($dashboardRoute)
                ], 403);
            }

            return redirect()->route($dashboardRoute)->with(
                'error',
                'Your shift is currently closed. You must open today\'s shift before accessing internal pages and operations.'
            );
        }

        return $next($request);
    }
}
