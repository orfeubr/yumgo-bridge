<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class GenerateOAuthUrls extends Command
{
    protected $signature = 'oauth:generate-urls';

    protected $description = 'Gera lista de URLs OAuth para todos os tenants (Google/Facebook)';

    public function handle()
    {
        $this->info('');
        $this->info('==============================================');
        $this->info('  📋 URLS OAUTH PARA GOOGLE CLOUD CONSOLE');
        $this->info('==============================================');
        $this->info('');

        $tenants = Tenant::all();
        $baseDomains = ['yumgo.com.br', 'yumgo.com.br'];

        // Redirect URIs
        $this->warn('🔗 AUTHORIZED REDIRECT URIs:');
        $this->info('');
        $this->line('Cole estas URLs em:');
        $this->line('Google Cloud Console → Credentials → YumGo Web Client → Authorized redirect URIs');
        $this->info('');

        foreach ($tenants as $tenant) {
            foreach ($baseDomains as $baseDomain) {
                $domain = $tenant->id . '.' . $baseDomain;
                $this->line('https://' . $domain . '/auth/google/callback');
            }
        }

        $this->info('');
        $this->info('');

        // JavaScript Origins
        $this->warn('🌐 AUTHORIZED JAVASCRIPT ORIGINS:');
        $this->info('');
        $this->line('Cole estas URLs em:');
        $this->line('Google Cloud Console → Credentials → YumGo Web Client → Authorized JavaScript origins');
        $this->info('');

        foreach ($tenants as $tenant) {
            foreach ($baseDomains as $baseDomain) {
                $domain = $tenant->id . '.' . $baseDomain;
                $this->line('https://' . $domain);
            }
        }

        $this->info('');
        $this->info('==============================================');
        $this->info('  ✅ Total de tenants: ' . $tenants->count());
        $this->info('  ✅ Total de redirect URIs: ' . ($tenants->count() * count($baseDomains)));
        $this->info('==============================================');
        $this->info('');

        $this->warn('💡 DICA: Rode este comando sempre que criar um novo tenant!');
        $this->info('');

        return 0;
    }
}
