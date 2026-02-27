<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateTenantCommand extends Command
{
    protected $signature = 'tenant:create
                            {name : Nome do restaurante}
                            {domain : Domínio (ex: food.yumgo.com.br)}
                            {--email= : Email do restaurante}';

    protected $description = 'Cria um novo tenant (restaurante)';

    public function handle()
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');
        $email = $this->option('email') ?? 'contato@' . $domain;

        try {
            $tenant = Tenant::create([
                'id' => Str::uuid(),
                'name' => $name,
                'slug' => Str::slug($name),
                'email' => $email,
                'status' => 'active',
            ]);

            $tenant->domains()->create([
                'domain' => $domain,
            ]);

            // Rodar migrations do tenant
            $this->call('tenants:migrate', ['--tenants' => [$tenant->id]]);

            $this->info("✅ Tenant criado com sucesso!");
            $this->info("ID: {$tenant->id}");
            $this->info("Nome: {$tenant->name}");
            $this->info("Domínio: {$domain}");
            $this->info("Email: {$tenant->email}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Erro ao criar tenant: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
