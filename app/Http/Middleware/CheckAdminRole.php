<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $section  Section to check: 'master-data', 'transaction', 'financial'
     */
    public function handle(Request $request, Closure $next, string $section): Response
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login')
                ->with('error', 'Please log in first');
        }

        // Check access based on section
        switch ($section) {
            case 'master-data':
                if (!$admin->canAccessMasterData()) {
                    abort(403, 'Anda tidak memiliki akses ke Master Data');
                }
                break;

            case 'transaction':
                if (!$admin->canAccessTransaction()) {
                    abort(403, 'Anda tidak memiliki akses ke Transaction');
                }
                break;

            case 'financial':
                if (!$admin->canAccessFinancial()) {
                    abort(403, 'Anda tidak memiliki akses ke Financial Reports');
                }
                break;

            default:
                // Dashboard is accessible by all authenticated admins
                break;
        }

        return $next($request);
    }
}
