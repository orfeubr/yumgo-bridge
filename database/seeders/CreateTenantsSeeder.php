<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CreateTenantsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏪 Criando restaurantes...');

        $plans = Plan::all();
        $proPlan = $plans->where('name', 'Pro')->first();
        $starterPlan = $plans->where('name', 'Starter')->first();
        $enterprisePlan = $plans->where('name', 'Enterprise')->first();

        // Tenant 1: Pizza Express
        $tenant1 = Tenant::create([
            'id' => 'pizza-express',
            'name' => 'Pizza Express',
            'slug' => 'pizza-express',
            'email' => 'contato@pizzaexpress.com',
            'phone' => '(11) 98765-4321',
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'trial_ends_at' => null,
            'asaas_account_id' => null,
        ]);

        Subscription::create([
            'tenant_id' => $tenant1->id,
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => null,
            'trial_ends_at' => null,
        ]);

        $this->command->info("✅ Pizza Express criado!");

        // Tenant 2: Burger Master
        $tenant2 = Tenant::create([
            'id' => 'burger-master',
            'name' => 'Burger Master',
            'slug' => 'burger-master',
            'email' => 'contato@burgermaster.com',
            'phone' => '(11) 98765-4322',
            'plan_id' => $starterPlan->id,
            'status' => 'active',
            'trial_ends_at' => null,
            'asaas_account_id' => null,
        ]);

        Subscription::create([
            'tenant_id' => $tenant2->id,
            'plan_id' => $starterPlan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => null,
            'trial_ends_at' => null,
        ]);

        $this->command->info("✅ Burger Master criado!");

        // Tenant 3: Sushi House
        $tenant3 = Tenant::create([
            'id' => 'sushi-house',
            'name' => 'Sushi House',
            'slug' => 'sushi-house',
            'email' => 'contato@sushihouse.com',
            'phone' => '(11) 98765-4323',
            'plan_id' => $enterprisePlan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(15),
            'asaas_account_id' => null,
        ]);

        Subscription::create([
            'tenant_id' => $tenant3->id,
            'plan_id' => $enterprisePlan->id,
            'status' => 'trialing',
            'starts_at' => now(),
            'ends_at' => null,
            'trial_ends_at' => now()->addDays(15),
        ]);

        $this->command->info("✅ Sushi House criado!");
        $this->command->info('🎉 3 restaurantes criados com sucesso!');
    }
}
