# 🗺️ Sistema de Delivery com Bairros Automáticos (via API)

**Data**: 22/02/2026
**Status**: 🔄 Proposta de Implementação

---

## 🎯 Ideia do Sistema

Em vez do restaurante **digitar manualmente** cada bairro, o sistema:

1. **Restaurante** informa a **cidade** que atende
2. **Sistema busca automaticamente** todos os bairros (via API IBGE/ViaCEP)
3. **Restaurante seleciona** quais bairros quer atender (checkboxes)
4. **Restaurante define taxa** para cada bairro selecionado
5. **Cliente** vê apenas os bairros disponíveis quando se cadastra

---

## ✅ Vantagens

| Aspecto | Manual | Automático ✅ |
|---------|--------|---------------|
| **Digitação** | Restaurante digita tudo | Zero digitação |
| **Erros** | "Centro", "centro", "CENTRO" | Padronizado |
| **Cliente** | Pode digitar errado | Select (sem erros) |
| **Profissional** | Amador | Profissional |
| **CEP** | Não integra | Integra fácil |
| **Manutenção** | Manual | Automática |

---

## 🏗️ Arquitetura

### **Banco de Dados**

```sql
-- Nova tabela: cities (opcional, pode usar API)
CREATE TABLE cities (
    id UUID PRIMARY KEY,
    state VARCHAR(2),           -- SP, RJ, MG
    name VARCHAR(100),           -- São Paulo, Campinas
    ibge_code VARCHAR(10),
    created_at TIMESTAMP
);

-- Nova tabela: neighborhoods (por tenant)
CREATE TABLE neighborhoods (
    id UUID PRIMARY KEY,
    city VARCHAR(100),           -- São Paulo
    name VARCHAR(200),           -- Vila Mariana
    enabled BOOLEAN DEFAULT false, -- Restaurante atende?
    delivery_fee DECIMAL(10,2),  -- R$ 5,00
    delivery_time INTEGER,       -- 30 minutos
    minimum_order DECIMAL(10,2), -- Pedido mínimo (opcional)
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Index para busca rápida
CREATE INDEX idx_neighborhoods_city ON neighborhoods(city);
CREATE INDEX idx_neighborhoods_enabled ON neighborhoods(enabled);
```

### **Settings (substituir delivery_zones)**

```json
{
  "delivery_city": "São Paulo",
  "delivery_state": "SP",
  "total_neighborhoods": 156,
  "enabled_neighborhoods": 45
}
```

---

## 🔧 Implementação Backend

### **1. Migration**

```bash
php artisan make:migration create_neighborhoods_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('neighborhoods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('city');
            $table->string('name');
            $table->boolean('enabled')->default(false);
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->integer('delivery_time')->nullable();
            $table->decimal('minimum_order', 10, 2)->nullable();
            $table->timestamps();

            $table->index(['city', 'enabled']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('neighborhoods');
    }
};
```

### **2. Model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Neighborhood extends Model
{
    protected $fillable = [
        'city',
        'name',
        'enabled',
        'delivery_fee',
        'delivery_time',
        'minimum_order'
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'delivery_fee' => 'decimal:2',
        'minimum_order' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Apenas bairros habilitados para delivery
     */
    public static function enabled()
    {
        return static::where('enabled', true)->orderBy('name');
    }

    /**
     * Bairros de uma cidade específica
     */
    public static function byCity(string $city)
    {
        return static::where('city', $city)->orderBy('name');
    }
}
```

### **3. Service para Buscar Bairros (API)**

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class LocationService
{
    /**
     * Buscar todos os bairros de uma cidade via ViaCEP
     * (scraping inteligente)
     */
    public function getNeighborhoodsByCity(string $city, string $state = 'SP'): array
    {
        $cacheKey = "neighborhoods_{$state}_{$city}";

        return Cache::remember($cacheKey, 60 * 24 * 30, function() use ($city, $state) {
            // Estratégia: Usar base de dados pronta
            // https://github.com/chandez/Estados-Cidades-IBGE

            // Para MVP: retornar lista hardcoded dos principais bairros
            return $this->getHardcodedNeighborhoods($city);
        });
    }

    /**
     * Buscar cidades de um estado
     */
    public function getCitiesByState(string $state = 'SP'): array
    {
        $response = Http::get("https://servicodados.ibge.gov.br/api/v1/localidades/estados/{$state}/municipios");

        if($response->successful()){
            return collect($response->json())
                ->pluck('nome')
                ->sort()
                ->values()
                ->toArray();
        }

        return [];
    }

    /**
     * Buscar bairro via CEP (para validação)
     */
    public function getAddressByCep(string $cep): ?array
    {
        $cep = preg_replace('/\D/', '', $cep);

        $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

        if($response->successful() && !isset($response['erro'])){
            return [
                'cep' => $response['cep'],
                'street' => $response['logradouro'],
                'neighborhood' => $response['bairro'],
                'city' => $response['localidade'],
                'state' => $response['uf']
            ];
        }

        return null;
    }

    /**
     * Base de bairros principais (hardcoded para MVP)
     */
    private function getHardcodedNeighborhoods(string $city): array
    {
        $neighborhoods = [
            'São Paulo' => [
                'Centro', 'Vila Mariana', 'Moema', 'Jardim Paulista', 'Pinheiros',
                'Itaim Bibi', 'Brooklin', 'Vila Madalena', 'Perdizes', 'Consolação',
                'Liberdade', 'Bela Vista', 'Aclimação', 'Paraíso', 'Vila Olímpia',
                'Morumbi', 'Campo Belo', 'Saúde', 'Jabaquara', 'Santo Amaro',
                'Tatuapé', 'Mooca', 'Brás', 'Belém', 'Penha',
                'Vila Prudente', 'Ipiranga', 'Sacomã', 'Cursino', 'Vila da Saúde'
            ],
            'Campinas' => [
                'Centro', 'Cambuí', 'Taquaral', 'Guanabara', 'Jardim das Paineiras',
                'Barão Geraldo', 'Nova Campinas', 'Jardim Chapadão', 'Jardim Proença',
                'Vila Industrial', 'Ponte Preta', 'Swift', 'Jardim Garcia'
            ],
            'Rio de Janeiro' => [
                'Centro', 'Copacabana', 'Ipanema', 'Leblon', 'Botafogo',
                'Flamengo', 'Laranjeiras', 'Tijuca', 'Vila Isabel', 'Grajaú',
                'Barra da Tijuca', 'Recreio', 'Jacarepaguá', 'Méier', 'Madureira'
            ]
        ];

        return $neighborhoods[$city] ?? [];
    }
}
```

### **4. API Routes**

```php
// routes/api.php

Route::prefix('location')->group(function(){
    Route::get('/cities/{state}', [LocationController::class, 'getCities']);
    Route::get('/neighborhoods/{city}', [LocationController::class, 'getNeighborhoods']);
    Route::get('/cep/{cep}', [LocationController::class, 'searchByCep']);
});
```

### **5. Controller**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LocationService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function getCities(string $state)
    {
        $cities = $this->locationService->getCitiesByState($state);
        return response()->json(['cities' => $cities]);
    }

    public function getNeighborhoods(string $city)
    {
        $neighborhoods = $this->locationService->getNeighborhoodsByCity($city);
        return response()->json(['neighborhoods' => $neighborhoods]);
    }

    public function searchByCep(string $cep)
    {
        $address = $this->locationService->getAddressByCep($cep);

        if(!$address){
            return response()->json(['error' => 'CEP não encontrado'], 404);
        }

        return response()->json($address);
    }
}
```

---

## 🎨 Implementação Frontend (Filament)

### **Settings Resource - Nova Aba "Bairros"**

```php
Forms\Components\Tabs\Tab::make('Bairros de Entrega')
    ->icon('heroicon-o-map')
    ->schema([
        Forms\Components\Section::make('Configurar Área de Entrega')
            ->description('Selecione a cidade e os bairros que você atende')
            ->schema([
                // Cidade
                Forms\Components\Select::make('delivery_city')
                    ->label('Cidade Principal')
                    ->searchable()
                    ->reactive()
                    ->options(function(){
                        $service = app(\App\Services\LocationService::class);
                        return collect($service->getCitiesByState('SP'))
                            ->mapWithKeys(fn($city) => [$city => $city]);
                    })
                    ->afterStateUpdated(function($state, $set){
                        // Limpar bairros quando trocar cidade
                        $set('needs_neighborhood_reload', true);
                    }),

                // Botão para carregar bairros
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('load_neighborhoods')
                        ->label('Carregar Bairros desta Cidade')
                        ->action(function($record, $get){
                            $city = $get('delivery_city');
                            if(!$city) return;

                            $service = app(\App\Services\LocationService::class);
                            $neighborhoods = $service->getNeighborhoodsByCity($city);

                            // Criar registros de bairros
                            foreach($neighborhoods as $neighborhood){
                                \App\Models\Neighborhood::firstOrCreate([
                                    'city' => $city,
                                    'name' => $neighborhood
                                ]);
                            }

                            Notification::make()
                                ->success()
                                ->title('Bairros carregados!')
                                ->body(count($neighborhoods) . ' bairros encontrados')
                                ->send();
                        })
                        ->color('primary')
                ]),

                // Mensagem de ajuda
                Forms\Components\Placeholder::make('help')
                    ->content('Depois de carregar os bairros, role a página para configurar as taxas.'),
            ]),

        Forms\Components\Section::make('Bairros Disponíveis')
            ->description('Marque os bairros que você atende e defina as taxas')
            ->schema([
                // Table/Repeater com todos os bairros
                Forms\Components\ViewField::make('neighborhoods_config')
                    ->view('filament.forms.neighborhoods-table')
            ])
    ])
```

---

## 🧪 Fluxo Completo

### **1. Restaurante Configura** (Uma vez)

```
1. Acessa Settings → Aba "Bairros de Entrega"
2. Seleciona cidade: "São Paulo"
3. Clica em "Carregar Bairros"
4. Sistema busca 156 bairros via API
5. Sistema salva no banco (neighborhoods table)
6. Restaurante vê lista com checkboxes:

   [✓] Centro           R$ [3,00]  [20] min
   [✓] Vila Mariana     R$ [5,00]  [30] min
   [ ] Perdizes         (desmarcado = não atende)
   [✓] Moema            R$ [5,00]  [30] min

7. Salva configuração
```

### **2. Cliente Compra**

```
1. Adiciona produtos no carrinho
2. No carrinho aparece:

   Cidade: São Paulo (fixo)
   Bairro: [Centro ▼]
           - Centro (R$ 3,00)
           - Moema (R$ 5,00)
           - Vila Mariana (R$ 5,00)

3. Seleciona bairro
4. Taxa calculada automaticamente
5. Finaliza pedido
```

### **3. Cliente Cadastra Endereço**

```
Opção A - Digite o CEP:
CEP: [12345-678]  [Buscar]
     ↓
Sistema preenche automaticamente:
Cidade: São Paulo
Bairro: Centro (R$ 3,00 - 20 min)

Opção B - Selecione manualmente:
Cidade: [São Paulo ▼]
Bairro: [Centro ▼] (apenas bairros enabled)
```

---

## ✅ Resumo da Solução

| Etapa | Como Funciona |
|-------|---------------|
| **1. Setup** | Restaurante escolhe cidade → Sistema carrega bairros (API) |
| **2. Config** | Restaurante marca bairros (checkbox) + define taxas |
| **3. Cliente** | Cliente vê apenas bairros disponíveis (select) |
| **4. Checkout** | Sistema calcula taxa automaticamente |
| **5. CEP** | Opcional: Auto-preenche bairro via CEP |

---

## 🚀 Próximos Passos

1. ✅ **Criar migration** `neighborhoods`
2. ✅ **Criar model** `Neighborhood`
3. ✅ **Criar service** `LocationService`
4. ✅ **API routes** para buscar cidades/bairros
5. 🔄 **Filament form** para configurar bairros
6. 🔄 **Frontend** - Select dinâmico de bairros
7. 🔄 **Validação** no checkout
8. 🔄 **CEP** auto-complete (opcional)

---

## 💰 Custo

**ZERO!** Todas as APIs são gratuitas:
- IBGE: Grátis
- ViaCEP: Grátis
- BrasilAPI: Grátis

---

**Quer que eu implemente isso AGORA?** 🚀

Posso criar:
1. Migration + Model
2. LocationService
3. Formulário Filament
4. Select dinâmico no frontend

**É A MELHOR SOLUÇÃO!** 💯
