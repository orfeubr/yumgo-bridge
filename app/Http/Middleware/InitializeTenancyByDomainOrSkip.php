<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Tenancy;
use Stancl\Tenancy\Database\Models\Domain;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyByDomainOrSkip
{
    protected Tenancy $tenancy;

    public function __construct(Tenancy $tenancy)
    {
        $this->tenancy = $tenancy;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Lista de domínios centrais que NÃO devem inicializar tenancy
        $centralDomains = config('tenancy.central_domains', []);
        $currentDomain = $request->getHost();

        \Log::debug('InitializeTenancyByDomainOrSkip - Verificando domínio', [
            'current_domain' => $currentDomain,
            'central_domains' => $centralDomains,
            'path' => $request->path(),
        ]);

        // Se for domínio central, pular inicialização de tenancy
        foreach ($centralDomains as $centralDomain) {
            if ($centralDomain === $currentDomain ||
                (str_starts_with($centralDomain, '*.') && str_ends_with($currentDomain, substr($centralDomain, 1)))) {
                \Log::debug('Domínio central detectado, pulando tenancy', ['domain' => $currentDomain]);
                return $next($request);
            }
        }

        // Buscar tenant pelo domínio DIRETAMENTE no banco
        try {
            $domain = Domain::where('domain', $currentDomain)->first();

            if ($domain && $domain->tenant) {
                $this->tenancy->initialize($domain->tenant);
                \Log::info('Tenancy inicializada com sucesso', [
                    'domain' => $currentDomain,
                    'tenant_id' => $domain->tenant->getTenantKey(),
                    'tenant_name' => $domain->tenant->name ?? 'N/A',
                ]);
            } else {
                \Log::warning('Domínio não encontrado na tabela domains', [
                    'domain' => $currentDomain,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao inicializar tenancy', [
                'domain' => $currentDomain,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $next($request);
    }
}
