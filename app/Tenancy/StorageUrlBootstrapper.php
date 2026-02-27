<?php

namespace App\Tenancy;

use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class StorageUrlBootstrapper implements TenancyBootstrapper
{
    public function bootstrap(Tenant $tenant)
    {
        // Atualizar URL do disco public para usar o domínio do tenant
        config([
            'filesystems.disks.public.url' => request()->getSchemeAndHttpHost() . '/tenancy/assets',
        ]);
    }

    public function revert()
    {
        // Reverter para URL padrão
        config([
            'filesystems.disks.public.url' => env('APP_URL') . '/storage',
        ]);
    }
}
