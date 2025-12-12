<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class CheckSubscriptionAndPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $requiredPermission = null): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Bypass total si super-admin (cf. Gate::before dans AppServiceProvider)
        if ($user->is_owner && $user->is_platform_user()) {
            return $next($request);
        }

        // 1. Vérifier si l'utilisateur est actif
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Votre compte a été désactivé. Contactez l’administrateur.',
            ]);
        }

        // 2. Vérifier si le tenant a un abonnement actif
        $subscription = $user->tenant->subscriptions()
            ->where('is_active', true)
            ->where('ends_at', '>=', now())
            ->latest()
            ->first();

        if (!$subscription || !$subscription->plan) {
            if (!$user->is_platform_user()){
                return abort(403, 'Votre entreprise n’a pas d’abonnement actif.');
            }
        }
        // 3. Vérifier que le plan contient la permission requise (si spécifiée)
        if ($requiredPermission) {
            if(!$user->is_platform_user()){
                $planPermissions = $subscription->plan->permissions->pluck('name')->toArray();

                if (!in_array($requiredPermission, $planPermissions)) {
                    return abort(403, 'Votre abonnement ne vous permet pas d’accéder à cette fonctionnalité.');
                }
            }


            // 4. Vérifier que l'utilisateur a lui-même la permission
            if (!$user->can($requiredPermission)) {
                return abort(403, 'Vous n’avez pas la permission d’effectuer cette action.');
            }
        }

        return $next($request);
    }
}
