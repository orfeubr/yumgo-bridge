<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Verifica se o tenant tem assinatura ativa
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se não está em contexto de tenant, pular
        if (!tenancy()->initialized) {
            return $next($request);
        }

        $tenant = tenancy()->tenant;

        // ===== BLOQUEAR TENANT COM STATUS PENDING =====
        // Conta criada mas ainda não configurou pagamento
        if ($tenant->status === 'pending') {
            // Permitir acesso apenas à página de gerenciar assinatura
            if (!$request->routeIs('filament.restaurant.pages.manage-subscription')) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Configure seu método de pagamento para ativar sua conta.',
                        'error' => 'payment_setup_required',
                    ], 402);
                }

                return redirect()->route('filament.restaurant.pages.manage-subscription')
                    ->with('warning', 'Configure seu método de pagamento para começar a usar o sistema.');
            }

            return $next($request);
        }

        // Buscar assinatura ativa do tenant
        $subscription = \App\Models\Subscription::where('tenant_id', $tenant->id)
            ->whereIn('status', ['active'])
            ->first();

        // Se não tem assinatura ativa, bloquear acesso
        if (!$subscription) {
            // Permitir acesso à página de gerenciar assinatura
            if (!$request->routeIs('filament.restaurant.pages.manage-subscription')) {
                // Se for requisição JSON (API), retornar 402
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Assinatura inativa. Entre em contato com o suporte.',
                        'error' => 'subscription_required',
                    ], 402);
                }

                // Se for web, redirecionar para página de assinatura vencida
                return redirect()->route('filament.restaurant.pages.manage-subscription')
                    ->with('error', 'Sua assinatura está inativa. Renove para continuar usando o sistema.');
            }

            return $next($request);
        }

        // Se assinatura está vencida (past_due), bloquear
        if ($subscription->status === 'past_due') {
            // Permitir acesso à página de gerenciar assinatura
            if (!$request->routeIs('filament.restaurant.pages.manage-subscription')) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Pagamento em atraso. Regularize para continuar.',
                        'error' => 'payment_overdue',
                    ], 402);
                }

                return redirect()->route('filament.restaurant.pages.manage-subscription')
                    ->with('error', 'Pagamento em atraso. Regularize sua assinatura para continuar.');
            }

            return $next($request);
        }

        // Adicionar subscription no request para uso posterior
        $request->attributes->set('subscription', $subscription);

        return $next($request);
    }
}
