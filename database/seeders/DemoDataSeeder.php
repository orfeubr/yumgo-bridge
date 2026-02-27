<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed the application's database with demo data.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // Criar Categorias
            $pizzas = Category::create([
                'name' => 'Pizzas',
                'slug' => 'pizzas',
                'description' => 'Nossas deliciosas pizzas artesanais',
                'order' => 1,
                'is_active' => true,
            ]);

            $bebidas = Category::create([
                'name' => 'Bebidas',
                'slug' => 'bebidas',
                'description' => 'Bebidas geladas para acompanhar',
                'order' => 2,
                'is_active' => true,
            ]);

            $sobremesas = Category::create([
                'name' => 'Sobremesas',
                'slug' => 'sobremesas',
                'description' => 'Doces para finalizar sua refeição',
                'order' => 3,
                'is_active' => true,
            ]);

            // Criar Produtos - Pizzas
            $mussarela = Product::create([
                'category_id' => $pizzas->id,
                'name' => 'Pizza de Mussarela',
                'slug' => 'pizza-de-mussarela',
                'description' => 'Molho de tomate, mussarela e orégano',
                'price' => 45.00,
                'preparation_time' => 30,
                'has_stock_control' => false,
                'is_active' => true,
                'is_featured' => true,
                'order' => 1,
            ]);

            $calabresa = Product::create([
                'category_id' => $pizzas->id,
                'name' => 'Pizza de Calabresa',
                'slug' => 'pizza-de-calabresa',
                'description' => 'Molho de tomate, mussarela, calabresa e cebola',
                'price' => 48.00,
                'preparation_time' => 30,
                'has_stock_control' => false,
                'is_active' => true,
                'is_featured' => true,
                'order' => 2,
            ]);

            $portuguesa = Product::create([
                'category_id' => $pizzas->id,
                'name' => 'Pizza Portuguesa',
                'slug' => 'pizza-portuguesa',
                'description' => 'Molho de tomate, mussarela, presunto, ovos, cebola, azeitona e orégano',
                'price' => 52.00,
                'preparation_time' => 35,
                'has_stock_control' => false,
                'is_active' => true,
                'is_featured' => false,
                'order' => 3,
            ]);

            $quatroQueijos = Product::create([
                'category_id' => $pizzas->id,
                'name' => 'Pizza Quatro Queijos',
                'slug' => 'pizza-quatro-queijos',
                'description' => 'Molho de tomate, mussarela, catupiry, parmesão e gorgonzola',
                'price' => 55.00,
                'preparation_time' => 30,
                'has_stock_control' => false,
                'is_active' => true,
                'is_featured' => true,
                'order' => 4,
            ]);

            // Criar Produtos - Bebidas
            $coca2l = Product::create([
                'category_id' => $bebidas->id,
                'name' => 'Coca-Cola 2L',
                'slug' => 'coca-cola-2l',
                'description' => 'Refrigerante Coca-Cola 2 litros',
                'price' => 12.00,
                'preparation_time' => 0,
                'has_stock_control' => true,
                'stock_quantity' => 50,
                'min_stock_alert' => 10,
                'is_active' => true,
                'is_featured' => false,
                'order' => 1,
            ]);

            $guarana2l = Product::create([
                'category_id' => $bebidas->id,
                'name' => 'Guaraná Antarctica 2L',
                'slug' => 'guarana-antarctica-2l',
                'description' => 'Refrigerante Guaraná Antarctica 2 litros',
                'price' => 10.00,
                'preparation_time' => 0,
                'has_stock_control' => true,
                'stock_quantity' => 35,
                'min_stock_alert' => 10,
                'is_active' => true,
                'is_featured' => false,
                'order' => 2,
            ]);

            $suco = Product::create([
                'category_id' => $bebidas->id,
                'name' => 'Suco Natural 500ml',
                'slug' => 'suco-natural-500ml',
                'description' => 'Suco natural de laranja, limão ou morango',
                'price' => 8.00,
                'preparation_time' => 5,
                'has_stock_control' => false,
                'is_active' => true,
                'is_featured' => false,
                'order' => 3,
            ]);

            // Criar Produtos - Sobremesas
            $pudim = Product::create([
                'category_id' => $sobremesas->id,
                'name' => 'Pudim de Leite',
                'slug' => 'pudim-de-leite',
                'description' => 'Pudim de leite condensado caseiro',
                'price' => 15.00,
                'preparation_time' => 5,
                'has_stock_control' => true,
                'stock_quantity' => 8,
                'min_stock_alert' => 3,
                'is_active' => true,
                'is_featured' => false,
                'order' => 1,
            ]);

            $brownie = Product::create([
                'category_id' => $sobremesas->id,
                'name' => 'Brownie com Sorvete',
                'slug' => 'brownie-com-sorvete',
                'description' => 'Brownie de chocolate quente com sorvete de creme',
                'price' => 18.00,
                'preparation_time' => 10,
                'has_stock_control' => true,
                'stock_quantity' => 12,
                'min_stock_alert' => 5,
                'is_active' => true,
                'is_featured' => true,
                'order' => 2,
            ]);

            // Criar Clientes de Demonstração
            $clientes = [];

            $clientes[] = Customer::create([
                'name' => 'João Silva',
                'email' => 'joao.silva@example.com',
                'phone' => '(11) 98765-4321',
                'cpf' => '123.456.789-00',
                'birth_date' => '1990-05-15',
                'password' => bcrypt('123456'),
                'cashback_balance' => 25.50,
                'loyalty_tier' => 'gold',
                'total_orders' => 15,
                'total_spent' => 750.00,
                'address_street' => 'Rua das Flores',
                'address_number' => '123',
                'address_complement' => 'Apto 45',
                'address_neighborhood' => 'Centro',
                'address_city' => 'São Paulo',
                'address_state' => 'SP',
                'address_zipcode' => '01310-100',
                'is_active' => true,
            ]);

            $clientes[] = Customer::create([
                'name' => 'Maria Santos',
                'email' => 'maria.santos@example.com',
                'phone' => '(11) 97654-3210',
                'cpf' => '987.654.321-00',
                'birth_date' => '1985-08-22',
                'password' => bcrypt('123456'),
                'cashback_balance' => 45.80,
                'loyalty_tier' => 'platinum',
                'total_orders' => 32,
                'total_spent' => 1620.00,
                'address_street' => 'Avenida Paulista',
                'address_number' => '1500',
                'address_neighborhood' => 'Bela Vista',
                'address_city' => 'São Paulo',
                'address_state' => 'SP',
                'address_zipcode' => '01310-200',
                'is_active' => true,
            ]);

            $clientes[] = Customer::create([
                'name' => 'Pedro Oliveira',
                'email' => 'pedro.oliveira@example.com',
                'phone' => '(11) 96543-2109',
                'cpf' => '456.789.123-00',
                'birth_date' => '1995-12-10',
                'password' => bcrypt('123456'),
                'cashback_balance' => 12.30,
                'loyalty_tier' => 'silver',
                'total_orders' => 8,
                'total_spent' => 380.00,
                'address_street' => 'Rua Augusta',
                'address_number' => '2500',
                'address_neighborhood' => 'Consolação',
                'address_city' => 'São Paulo',
                'address_state' => 'SP',
                'address_zipcode' => '01412-100',
                'is_active' => true,
            ]);

            $clientes[] = Customer::create([
                'name' => 'Ana Costa',
                'email' => 'ana.costa@example.com',
                'phone' => '(11) 95432-1098',
                'cpf' => '321.654.987-00',
                'birth_date' => '2000-03-28',
                'password' => bcrypt('123456'),
                'cashback_balance' => 5.60,
                'loyalty_tier' => 'bronze',
                'total_orders' => 3,
                'total_spent' => 142.00,
                'address_street' => 'Rua Oscar Freire',
                'address_number' => '800',
                'address_neighborhood' => 'Jardins',
                'address_city' => 'São Paulo',
                'address_state' => 'SP',
                'address_zipcode' => '01426-000',
                'is_active' => true,
            ]);

            // Criar Pedidos de Demonstração
            $statuses = ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered'];
            $paymentMethods = ['pix', 'credit_card', 'debit_card', 'cash'];

            // Criar alguns pedidos recentes
            for ($i = 0; $i < 15; $i++) {
                $customer = $clientes[array_rand($clientes)];
                $status = $statuses[array_rand($statuses)];

                $subtotal = rand(40, 150);
                $deliveryFee = rand(0, 10);
                $discount = rand(0, 20);
                $cashbackUsed = $customer->cashback_balance > 0 ? rand(0, min(10, $customer->cashback_balance)) : 0;
                $total = $subtotal + $deliveryFee - $discount - $cashbackUsed;
                $cashbackEarned = $total * 0.05; // 5% de cashback

                Order::create([
                    'order_number' => 'PED-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                    'customer_id' => $customer->id,
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'discount' => $discount,
                    'cashback_used' => $cashbackUsed,
                    'total' => $total,
                    'cashback_earned' => $cashbackEarned,
                    'cashback_percentage' => 5,
                    'status' => $status,
                    'payment_status' => $status === 'cancelled' ? 'failed' : ($status === 'delivered' ? 'paid' : 'pending'),
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'delivery_type' => rand(0, 1) ? 'delivery' : 'pickup',
                    'delivery_address' => $customer->full_address,
                    'estimated_time' => rand(30, 60),
                    'customer_notes' => $i % 3 === 0 ? 'Sem cebola, por favor' : null,
                    'created_at' => now()->subDays(rand(0, 7))->subHours(rand(0, 23)),
                ]);
            }

            DB::commit();

            $this->command->info('✅ Dados de demonstração criados com sucesso!');
            $this->command->info('');
            $this->command->info('📊 Resumo:');
            $this->command->info('   - 3 Categorias');
            $this->command->info('   - 10 Produtos');
            $this->command->info('   - 4 Clientes');
            $this->command->info('   - 15 Pedidos');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Erro ao criar dados de demonstração: ' . $e->getMessage());
            throw $e;
        }
    }
}
