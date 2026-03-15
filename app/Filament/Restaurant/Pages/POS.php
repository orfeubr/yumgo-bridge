<?php

namespace App\Filament\Restaurant\Pages;

use App\Filament\Restaurant\Resources\OrderResource;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\OrderService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class POS extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = '💰 PDV - Frente de Caixa';
    protected static ?string $title = 'PDV - Ponto de Venda';
    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.restaurant.pages.p-o-s';

    // Estado do carrinho
    public $cart = [];
    public $selectedCategory = null;
    public $searchProduct = '';
    public $showImages = true; // Toggle imagens

    // Cliente
    public $selectedCustomer = null;
    public $customerName = '';
    public $customerPhone = '';
    public $customerEmail = '';
    public $customerCashbackBalance = 0;
    public $customerLoyaltyTier = 'bronze';

    // Pedido
    public $deliveryType = 'delivery';
    public $paymentMethod = 'pix';
    public $deliveryAddress = '';
    public $customerNotes = '';
    public $discount = 0;
    public $deliveryFee = 0;
    public $cashbackUsed = 0;

    // Modal novo cliente
    public $showNewCustomerModal = false;

    // Modal PIX
    public $showPixModal = false;
    public $pixQrCode = null;
    public $pixCopyPaste = null;
    public $pixOrderNumber = null;

    protected $listeners = ['addCustomPizza'];

    public function mount(): void
    {
        $this->cart = [];
    }

    public function getCategories(): Collection
    {
        return Category::with('products')
            ->where('is_active', true)
            ->ordered()
            ->get();
    }

    public function getProducts(): Collection
    {
        $query = Product::query()
            ->with('category')
            ->where('is_active', true);

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        if ($this->searchProduct) {
            $query->where(function($q) {
                $q->where('name', 'ilike', '%' . $this->searchProduct . '%')
                  ->orWhere('description', 'ilike', '%' . $this->searchProduct . '%');
            });
        }

        return $query->ordered()->get();
    }

    public function addToCart($productId): void
    {
        $product = Product::find($productId);

        if (!$product) {
            Notification::make()
                ->danger()
                ->title('Produto não encontrado')
                ->send();
            return;
        }

        // Se for pizza, abrir modal de personalização
        if ($product->is_pizza) {
            $this->dispatch('openPizzaBuilder', productId: $productId);
            return;
        }

        // Verificar estoque
        if ($product->has_stock_control && !$product->hasStock()) {
            Notification::make()
                ->danger()
                ->title('Produto sem estoque')
                ->send();
            return;
        }

        // Adicionar ou incrementar quantidade
        $cartKey = 'product_' . $productId;

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['quantity']++;
        } else {
            $this->cart[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'subtotal' => $product->price,
            ];
        }

        $this->updateCartTotals();
    }

    public function addCustomPizza($data): void
    {
        // Adicionar pizza customizada ao carrinho
        $cartKey = 'pizza_' . uniqid();

        $this->cart[$cartKey] = [
            'product_id' => $data['product_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'quantity' => $data['quantity'],
            'subtotal' => $data['price'] * $data['quantity'],
            'config' => $data['config'],
            'is_pizza' => true,
        ];

        $this->updateCartTotals();

        Notification::make()
            ->success()
            ->title('Pizza adicionada ao carrinho! 🍕')
            ->send();
    }

    public function removeFromCart($cartKey): void
    {
        unset($this->cart[$cartKey]);
        $this->updateCartTotals();
    }

    public function updateQuantity($cartKey, $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeFromCart($cartKey);
            return;
        }

        $this->cart[$cartKey]['quantity'] = $quantity;
        $this->updateCartTotals();
    }

    public function updateCartTotals(): void
    {
        foreach ($this->cart as $key => $item) {
            $this->cart[$key]['subtotal'] = $item['price'] * $item['quantity'];
        }
    }

    public function getSubtotal(): float
    {
        return collect($this->cart)->sum('subtotal');
    }

    public function getTotal(): float
    {
        $total = $this->getSubtotal() + $this->deliveryFee - $this->discount - $this->cashbackUsed;
        return max(0, $total); // Nunca negativo
    }

    public function getCashbackPercentage(): float
    {
        // Percentual de cashback baseado no tier do cliente
        return match($this->customerLoyaltyTier) {
            'bronze' => 3,
            'silver' => 5,
            'gold' => 7,
            'platinum' => 10,
            default => 3,
        };
    }

    public function getCashbackEarned(): float
    {
        $total = $this->getTotal();
        $percentage = $this->getCashbackPercentage();
        return $total * ($percentage / 100);
    }

    public function selectCustomer($customerId): void
    {
        $customer = Customer::find($customerId);

        if ($customer) {
            $this->selectedCustomer = $customer->id;
            $this->customerName = $customer->name;
            $this->customerPhone = $customer->phone;
            $this->customerEmail = $customer->email;
            $this->deliveryAddress = $customer->full_address ?? '';
            $this->customerCashbackBalance = $customer->cashback_balance;
            $this->customerLoyaltyTier = $customer->loyalty_tier;
            $this->cashbackUsed = 0; // Resetar cashback usado ao trocar cliente
        }
    }

    public function clearCustomer(): void
    {
        $this->selectedCustomer = null;
        $this->customerName = '';
        $this->customerPhone = '';
        $this->customerEmail = '';
        $this->deliveryAddress = '';
        $this->customerCashbackBalance = 0;
        $this->customerLoyaltyTier = 'bronze';
        $this->cashbackUsed = 0;
    }

    public function applyCashback($amount): void
    {
        // Validar se o cliente tem saldo suficiente
        if ($amount > $this->customerCashbackBalance) {
            Notification::make()
                ->danger()
                ->title('Saldo insuficiente')
                ->body('Cliente não tem saldo suficiente de cashback.')
                ->send();
            return;
        }

        // Validar se não está usando mais que o total
        $maxUsable = $this->getSubtotal() + $this->deliveryFee - $this->discount;
        if ($amount > $maxUsable) {
            Notification::make()
                ->warning()
                ->title('Valor ajustado')
                ->body('O cashback não pode ser maior que o total do pedido.')
                ->send();
            $this->cashbackUsed = $maxUsable;
            return;
        }

        $this->cashbackUsed = $amount;
    }

    public function useAllCashback(): void
    {
        $maxUsable = min(
            $this->customerCashbackBalance,
            $this->getSubtotal() + $this->deliveryFee - $this->discount
        );

        $this->cashbackUsed = $maxUsable;

        Notification::make()
            ->success()
            ->title('Cashback aplicado!')
            ->body('R$ ' . number_format($maxUsable, 2, ',', '.') . ' de cashback aplicado.')
            ->send();
    }

    public function clearCashback(): void
    {
        $this->cashbackUsed = 0;

        Notification::make()
            ->info()
            ->title('Cashback removido')
            ->send();
    }

    public function createQuickCustomer(): void
    {
        $this->validate([
            'customerName' => 'required|string|max:255',
            'customerPhone' => 'required|string|max:255',
            'customerEmail' => 'nullable|email|max:255',
        ], [
            'customerName.required' => 'Nome é obrigatório',
            'customerPhone.required' => 'Telefone é obrigatório',
            'customerEmail.email' => 'E-mail inválido',
        ]);

        $customer = Customer::create([
            'name' => $this->customerName,
            'phone' => $this->customerPhone,
            'email' => $this->customerEmail ?: $this->customerPhone . '@temp.com',
            'password' => bcrypt('123456'),
            'is_active' => true,
        ]);

        $this->selectCustomer($customer->id);
        $this->showNewCustomerModal = false;

        Notification::make()
            ->success()
            ->title('Cliente cadastrado com sucesso!')
            ->send();
    }

    public function finishOrder(): void
    {
        // Validações
        if (empty($this->cart)) {
            Notification::make()
                ->danger()
                ->title('Carrinho vazio')
                ->body('Adicione produtos ao carrinho antes de finalizar.')
                ->send();
            return;
        }

        if (!$this->selectedCustomer && !$this->customerName) {
            Notification::make()
                ->danger()
                ->title('Cliente não selecionado')
                ->body('Selecione ou cadastre um cliente.')
                ->send();
            return;
        }

        // Criar cliente rápido se necessário
        if (!$this->selectedCustomer && $this->customerName) {
            $this->createQuickCustomer();
        }

        try {
            $customer = Customer::findOrFail($this->selectedCustomer);

            // Preparar items para o OrderService
            $items = collect($this->cart)->map(function ($item) {
                return [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'notes' => $item['description'] ?? $item['notes'] ?? null,
                ];
            })->toArray();

            // Criar pedido via OrderService (já integrado com Asaas)
            $orderService = app(OrderService::class);
            $order = $orderService->createOrder($customer, [
                'items' => $items,
                'delivery_address' => $this->deliveryAddress,
                'delivery_city' => 'N/A', // POS não tem cidade separada
                'delivery_neighborhood' => 'N/A', // POS não tem bairro separado
                'delivery_fee' => $this->deliveryFee,
                'discount' => $this->discount,
                'payment_method' => $this->paymentMethod,
                'delivery_type' => $this->deliveryType,
                'use_cashback' => $this->cashbackUsed,
                'notes' => $this->customerNotes,
            ]);

            // Se for PIX, buscar o pagamento e exibir QR Code
            if ($this->paymentMethod === 'pix') {
                $payment = $order->payments()->latest()->first();

                if ($payment && $payment->pix_qrcode) {
                    $this->pixQrCode = $payment->pix_qrcode;
                    $this->pixCopyPaste = $payment->pix_code;
                    $this->pixOrderNumber = $order->order_number;
                    $this->showPixModal = true;
                } else {
                    Notification::make()
                        ->warning()
                        ->title('Pedido criado, mas QR Code PIX não foi gerado')
                        ->body('Verifique as configurações do Asaas')
                        ->send();
                }
            }

            // Limpar carrinho
            $this->cart = [];
            $this->clearCustomer();
            $this->customerNotes = '';
            $this->discount = 0;
            $this->deliveryFee = 0;
            $this->cashbackUsed = 0;

            if ($this->paymentMethod !== 'pix') {
                Notification::make()
                    ->success()
                    ->title('Pedido criado com sucesso! 🎉')
                    ->body("Número: {$order->order_number}")
                    ->send();

                // Redirecionar para o pedido (apenas se não for PIX)
                $this->redirect(OrderResource::getUrl('edit', ['record' => $order]));
            }

        } catch (\Exception $e) {
            \Log::error('Erro ao criar pedido no POS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->danger()
                ->title('Erro ao criar pedido')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function closePixModal(): void
    {
        $this->showPixModal = false;
        $this->pixQrCode = null;
        $this->pixCopyPaste = null;
        $this->pixOrderNumber = null;
    }

    public function sendPixWhatsApp(): void
    {
        $customer = Customer::find($this->selectedCustomer);

        if (!$customer || !$customer->phone) {
            Notification::make()
                ->danger()
                ->title('Cliente sem telefone cadastrado')
                ->send();
            return;
        }

        $message = urlencode(
            "🍕 *Pedido #{$this->pixOrderNumber}*\n\n" .
            "Olá {$customer->name}! 👋\n\n" .
            "Seu pedido foi confirmado!\n" .
            "Total: R$ " . number_format($this->getTotal(), 2, ',', '.') . "\n\n" .
            "💰 *Pague com PIX:*\n" .
            "Copie o código abaixo:\n\n" .
            $this->pixCopyPaste
        );

        $whatsappUrl = "https://wa.me/{$customer->phone}?text={$message}";

        $this->dispatch('open-url', url: $whatsappUrl);

        Notification::make()
            ->success()
            ->title('Abrindo WhatsApp...')
            ->send();
    }

    public function clearCart(): void
    {
        $this->cart = [];

        Notification::make()
            ->info()
            ->title('Carrinho limpo')
            ->send();
    }
}
