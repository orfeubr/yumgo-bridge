<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar permissões personalizadas por módulo
        $modules = [
            'Produtos' => [
                'view_products' => 'Visualizar lista de produtos',
                'create_product' => 'Criar novo produto',
                'edit_product' => 'Editar produto',
                'delete_product' => 'Excluir produto',
                'change_product_image' => 'Alterar foto do produto',
            ],
            'Pedidos' => [
                'view_orders' => 'Visualizar pedidos',
                'create_order' => 'Criar pedido',
                'edit_order' => 'Editar pedido',
                'cancel_order' => 'Cancelar pedido',
                'change_order_status' => 'Alterar status do pedido',
            ],
            'Clientes' => [
                'view_customers' => 'Visualizar clientes',
                'edit_customer' => 'Editar cliente',
                'delete_customer' => 'Excluir cliente',
                'view_customer_orders' => 'Ver histórico de pedidos',
            ],
            'Categorias' => [
                'view_categories' => 'Visualizar categorias',
                'create_category' => 'Criar categoria',
                'edit_category' => 'Editar categoria',
                'delete_category' => 'Excluir categoria',
            ],
            'Cashback' => [
                'view_cashback' => 'Visualizar configurações de cashback',
                'edit_cashback' => 'Editar cashback',
                'view_cashback_transactions' => 'Ver transações de cashback',
            ],
            'Usuários' => [
                'view_users' => 'Visualizar usuários',
                'create_user' => 'Criar usuário',
                'edit_user' => 'Editar usuário',
                'delete_user' => 'Excluir usuário',
                'manage_permissions' => 'Gerenciar permissões',
            ],
            'Relatórios' => [
                'view_reports' => 'Visualizar relatórios',
                'export_reports' => 'Exportar relatórios',
            ],
            'Configurações' => [
                'view_settings' => 'Visualizar configurações',
                'edit_settings' => 'Editar configurações',
            ],
        ];

        // Criar permissões
        foreach ($modules as $module => $permissions) {
            foreach ($permissions as $permission => $description) {
                Permission::create([
                    'name' => $permission,
                    'guard_name' => 'web',
                ]);
            }
        }

        // Criar perfis
        $superAdmin = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo([
            // Produtos
            'view_products', 'create_product', 'edit_product', 'delete_product', 'change_product_image',
            // Pedidos
            'view_orders', 'create_order', 'edit_order', 'cancel_order', 'change_order_status',
            // Clientes
            'view_customers', 'edit_customer', 'view_customer_orders',
            // Categorias
            'view_categories', 'create_category', 'edit_category', 'delete_category',
            // Cashback
            'view_cashback', 'edit_cashback', 'view_cashback_transactions',
            // Usuários
            'view_users', 'create_user', 'edit_user',
            // Relatórios
            'view_reports', 'export_reports',
            // Configurações
            'view_settings', 'edit_settings',
        ]);

        $gerente = Role::create(['name' => 'gerente', 'guard_name' => 'web']);
        $gerente->givePermissionTo([
            // Produtos (sem deletar)
            'view_products', 'create_product', 'edit_product', 'change_product_image',
            // Pedidos
            'view_orders', 'create_order', 'edit_order', 'change_order_status',
            // Clientes
            'view_customers', 'edit_customer', 'view_customer_orders',
            // Categorias
            'view_categories', 'create_category', 'edit_category',
            // Cashback
            'view_cashback', 'view_cashback_transactions',
            // Relatórios
            'view_reports', 'export_reports',
        ]);

        $cozinha = Role::create(['name' => 'cozinha', 'guard_name' => 'web']);
        $cozinha->givePermissionTo([
            // Apenas pedidos
            'view_orders', 'change_order_status',
            // Ver produtos
            'view_products',
        ]);

        $atendente = Role::create(['name' => 'atendente', 'guard_name' => 'web']);
        $atendente->givePermissionTo([
            // Produtos (só visualizar)
            'view_products',
            // Pedidos
            'view_orders', 'create_order', 'edit_order',
            // Clientes
            'view_customers', 'view_customer_orders',
        ]);
    }
}
