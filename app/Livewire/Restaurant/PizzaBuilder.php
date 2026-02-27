<?php

namespace App\Livewire\Restaurant;

use App\Models\Product;
use Livewire\Component;

class PizzaBuilder extends Component
{
    public $showModal = false;
    public $product = null;

    // Configurações da pizza
    public $pizzaType = 'whole'; // whole ou half
    public $size = 'medium'; // small, medium, large, family
    public $flavor1 = null;
    public $flavor2 = null;
    public $border = 'none'; // none, catupiry, cheddar, chocolate
    public $additionals = [];

    // Preços
    public $sizeMultipliers = [
        'small' => 0.7,
        'medium' => 1.0,
        'large' => 1.3,
        'family' => 1.6,
    ];

    public $borderPrices = [
        'none' => 0,
        'catupiry' => 8.00,
        'cheddar' => 8.00,
        'chocolate' => 10.00,
    ];

    protected $listeners = ['openPizzaBuilder'];

    public function openPizzaBuilder($productId)
    {
        $this->product = Product::find($productId);

        if ($this->product && $this->product->is_pizza) {
            $this->resetBuilder();
            $this->showModal = true;
        }
    }

    public function resetBuilder()
    {
        $this->pizzaType = 'whole';
        $this->size = 'medium';
        $this->flavor1 = $this->product->id;
        $this->flavor2 = null;
        $this->border = 'none';
        $this->additionals = [];
    }

    public function getPizzaFlavors()
    {
        // Buscar todas as pizzas disponíveis
        return Product::where('is_pizza', true)
            ->where('is_active', true)
            ->where('category_id', $this->product->category_id)
            ->orderBy('name')
            ->get();
    }

    public function calculatePrice()
    {
        if (!$this->product) return 0;

        $basePrice = 0;

        if ($this->pizzaType === 'whole') {
            // Pizza inteira: preço normal
            $flavor = Product::find($this->flavor1);
            $basePrice = $flavor ? $flavor->price : $this->product->price;
        } else {
            // Meio a meio: cobra pelo maior preço
            $flavor1 = Product::find($this->flavor1);
            $flavor2 = Product::find($this->flavor2);

            $price1 = $flavor1 ? $flavor1->price : 0;
            $price2 = $flavor2 ? $flavor2->price : 0;

            $basePrice = max($price1, $price2);
        }

        // Aplicar multiplicador de tamanho
        $sizeMultiplier = $this->sizeMultipliers[$this->size] ?? 1.0;
        $totalPrice = $basePrice * $sizeMultiplier;

        // Adicionar borda
        $totalPrice += $this->borderPrices[$this->border] ?? 0;

        return $totalPrice;
    }

    public function addToCart()
    {
        // Validações
        if (!$this->flavor1) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Selecione o sabor da pizza'
            ]);
            return;
        }

        if ($this->pizzaType === 'half' && !$this->flavor2) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Selecione o segundo sabor'
            ]);
            return;
        }

        // Montar descrição da pizza
        $description = $this->buildDescription();

        // Calcular preço final
        $finalPrice = $this->calculatePrice();

        // Emitir evento para adicionar ao carrinho do PDV
        $this->dispatch('addCustomPizza', [
            'product_id' => $this->product->id,
            'name' => 'Pizza ' . $this->product->name,
            'description' => $description,
            'price' => $finalPrice,
            'quantity' => 1,
            'config' => [
                'type' => $this->pizzaType,
                'size' => $this->size,
                'flavor1' => $this->flavor1,
                'flavor2' => $this->flavor2,
                'border' => $this->border,
            ]
        ]);

        $this->showModal = false;
        $this->reset(['product', 'pizzaType', 'size', 'flavor1', 'flavor2', 'border']);
    }

    public function buildDescription()
    {
        $parts = [];

        // Tamanho
        $sizes = [
            'small' => 'Pequena',
            'medium' => 'Média',
            'large' => 'Grande',
            'family' => 'Família',
        ];
        $parts[] = $sizes[$this->size];

        // Sabores
        if ($this->pizzaType === 'whole') {
            $flavor = Product::find($this->flavor1);
            $parts[] = $flavor ? $flavor->name : '';
        } else {
            $flavor1 = Product::find($this->flavor1);
            $flavor2 = Product::find($this->flavor2);
            $parts[] = '1/2 ' . ($flavor1 ? $flavor1->name : '') . ' + 1/2 ' . ($flavor2 ? $flavor2->name : '');
        }

        // Borda
        if ($this->border !== 'none') {
            $borders = [
                'catupiry' => 'Borda de Catupiry',
                'cheddar' => 'Borda de Cheddar',
                'chocolate' => 'Borda de Chocolate',
            ];
            $parts[] = $borders[$this->border];
        }

        return implode(' | ', $parts);
    }

    public function render()
    {
        return view('livewire.restaurant.pizza-builder', [
            'flavors' => $this->showModal ? $this->getPizzaFlavors() : collect([]),
            'calculatedPrice' => $this->calculatePrice(),
        ]);
    }
}
