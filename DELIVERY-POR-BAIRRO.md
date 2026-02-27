# 🚚 Sistema de Delivery por Bairro

**Data**: 22/02/2026
**Status**: ✅ Implementado

---

## 🎯 Como Funciona

Agora você pode **cadastrar quantos bairros quiser**, cada um com:
- ✅ **Taxa de entrega própria**
- ✅ **Tempo estimado diferente**
- ✅ **Nome personalizável**

---

## 📸 Interface

### Antes ❌
```
┌─────────────────────────────────┐
│ Taxa de Entrega: R$ [5,00]      │  ← Valor único
└─────────────────────────────────┘
```

### Agora ✅
```
┌────────────────────────────────────────────────────────┐
│  Taxas de Entrega por Bairro                           │
│  Configure valores diferentes para cada bairro         │
├────────────────────────────────────────────────────────┤
│                                                         │
│  ▼ Centro                                              │
│     Bairro: [Centro            ]  Taxa: [R$ 3,00]     │
│     Tempo:  [20 min]                                   │
│                                                         │
│  ▼ Jardim das Flores                                   │
│     Bairro: [Jardim das Flores ]  Taxa: [R$ 5,00]     │
│     Tempo:  [30 min]                                   │
│                                                         │
│  ▼ Vila Nova                                           │
│     Bairro: [Vila Nova         ]  Taxa: [R$ 8,00]     │
│     Tempo:  [45 min]                                   │
│                                                         │
│  [+ Adicionar Bairro]                                  │
│                                                         │
└────────────────────────────────────────────────────────┘
```

---

## ✨ Recursos

### 1. **Adicionar Bairros Ilimitados**
- Clique em **"+ Adicionar Bairro"**
- Preencha nome, taxa e tempo
- Salve!

### 2. **Reordenar** (arrastar e soltar)
- Clique e arraste para reorganizar
- Útil para deixar os mais próximos no topo

### 3. **Colapsar/Expandir**
- Clique na seta para minimizar
- Facilita gerenciar muitos bairros

### 4. **Remover Bairro**
- Clique no ícone de lixeira
- Confirme a remoção

---

## 💾 Como os Dados São Salvos

### Formato no Banco (JSON)
```json
{
  "delivery_zones": [
    {
      "neighborhood": "Centro",
      "fee": 3.00,
      "delivery_time": 20
    },
    {
      "neighborhood": "Jardim das Flores",
      "fee": 5.00,
      "delivery_time": 30
    },
    {
      "neighborhood": "Vila Nova",
      "fee": 8.00,
      "delivery_time": 45
    },
    {
      "neighborhood": "Bairro Distante",
      "fee": 12.00,
      "delivery_time": 60
    }
  ]
}
```

---

## 🛒 Como Usar no Checkout

### 1. **Seleção de Bairro no Checkout**

O checkout pode ter um campo select:
```php
<select name="neighborhood">
  <option value="">Selecione seu bairro</option>
  <option value="Centro" data-fee="3.00" data-time="20">
    Centro - R$ 3,00 (20 min)
  </option>
  <option value="Jardim das Flores" data-fee="5.00" data-time="30">
    Jardim das Flores - R$ 5,00 (30 min)
  </option>
  <option value="Vila Nova" data-fee="8.00" data-time="45">
    Vila Nova - R$ 8,00 (45 min)
  </option>
</select>
```

### 2. **Cálculo Automático**

JavaScript atualiza o total quando o usuário seleciona:
```javascript
// Cliente escolhe "Jardim das Flores"
Subtotal:     R$ 45,00
Taxa Entrega: R$  5,00  ← Automaticamente
Desconto:     R$  0,00
──────────────────────
Total:        R$ 50,00
Tempo:        30 minutos
```

### 3. **Endereço Fora das Zonas**

Opções:
- **Opção A**: Não entrega (mensagem: "Não entregamos neste bairro")
- **Opção B**: Taxa padrão (ex: R$ 10,00)
- **Opção C**: Campo "Outro bairro" com valor fixo

---

## 🔧 Implementação no Frontend

### Carregar Zonas de Entrega

```php
// Controller
$settings = Settings::current();
$deliveryZones = $settings->delivery_zones ?? [];

return view('checkout', [
    'deliveryZones' => $deliveryZones
]);
```

### Blade Template

```blade
<select id="neighborhood" name="neighborhood" required>
    <option value="">Selecione seu bairro</option>
    @foreach($deliveryZones as $zone)
        <option
            value="{{ $zone['neighborhood'] }}"
            data-fee="{{ $zone['fee'] }}"
            data-time="{{ $zone['delivery_time'] }}">
            {{ $zone['neighborhood'] }} -
            R$ {{ number_format($zone['fee'], 2, ',', '.') }}
            ({{ $zone['delivery_time'] }} min)
        </option>
    @endforeach
</select>

<div id="delivery-info" class="hidden">
    <p>Taxa de entrega: <strong id="delivery-fee">R$ 0,00</strong></p>
    <p>Tempo estimado: <strong id="delivery-time">0 min</strong></p>
</div>
```

### JavaScript (Alpine.js)

```javascript
data() {
    return {
        selectedNeighborhood: '',
        deliveryFee: 0,
        deliveryTime: 0,

        updateDeliveryInfo(event) {
            const option = event.target.selectedOptions[0];
            this.deliveryFee = parseFloat(option.dataset.fee || 0);
            this.deliveryTime = parseInt(option.dataset.time || 0);
        },

        get total() {
            return this.cartTotal + this.deliveryFee;
        }
    }
}
```

---

## 📊 Exemplos de Uso

### Exemplo 1: Marmitaria (raio pequeno)
```
Centro:          R$ 0,00 (grátis, próximo)
Bairro Vizinho:  R$ 3,00
Bairro Distante: R$ 5,00
Fora da Área:    Não entrega
```

### Exemplo 2: Pizzaria (área maior)
```
Centro:               R$ 5,00  (20 min)
Zona Norte:           R$ 8,00  (30 min)
Zona Sul:             R$ 8,00  (30 min)
Zona Leste:           R$ 10,00 (40 min)
Zona Oeste:           R$ 10,00 (40 min)
Bairros Periféricos:  R$ 15,00 (60 min)
```

### Exemplo 3: Restaurante Premium
```
Centro:     R$ 10,00 (entrega rápida - 15 min)
Bairro 1:   R$ 12,00 (20 min)
Bairro 2:   R$ 12,00 (20 min)
Bairro 3:   R$ 15,00 (30 min)
```

---

## 🎨 Customizações Futuras

### 1. **Integração com CEP**
```php
// Detectar bairro automaticamente via CEP
$cep = '12345-678';
$address = ViaCEP::fetch($cep);
$neighborhood = $address->bairro;

// Buscar taxa
$zone = collect($settings->delivery_zones)
    ->firstWhere('neighborhood', $neighborhood);

$deliveryFee = $zone['fee'] ?? 10.00; // Taxa padrão
```

### 2. **Pedido Mínimo por Bairro**
```json
{
  "neighborhood": "Bairro Distante",
  "fee": 12.00,
  "delivery_time": 60,
  "minimum_order": 50.00  ← Novo campo
}
```

### 3. **Horários Especiais**
```json
{
  "neighborhood": "Centro",
  "fee": 5.00,
  "fee_night": 8.00,  ← Após 22h
  "delivery_time": 30
}
```

### 4. **Entrega Grátis Condicional**
```json
{
  "neighborhood": "Centro",
  "fee": 5.00,
  "free_delivery_above": 50.00  ← Grátis acima de R$ 50
}
```

---

## 🧪 Como Testar

1. Acesse: `https://marmitaria-gi.eliseus.com.br/restaurant/settings?tab=-delivery-tab`

2. Role até **"Taxas de Entrega por Bairro"**

3. Clique em **"+ Adicionar Bairro"**

4. Preencha:
   - Bairro: `Centro`
   - Taxa: `R$ 3,00`
   - Tempo: `20 min`

5. Adicione mais bairros

6. Salve!

---

## ✅ Checklist

- [x] Repeater para múltiplos bairros
- [x] Nome, taxa e tempo por bairro
- [x] Reordenável (arrastar)
- [x] Colapsável (organização)
- [x] Dados salvos como JSON
- [ ] Frontend: Select de bairros no checkout
- [ ] Frontend: Cálculo automático da taxa
- [ ] Integração com CEP (opcional)

---

## 🚀 Próximos Passos

### Backend (prioridade)
1. ✅ Painel de configuração (FEITO)
2. 🔄 API para retornar zonas de entrega
3. 🔄 Validação de bairro no checkout
4. 🔄 Salvar bairro no pedido

### Frontend (próximo)
1. Adicionar select de bairros na página de checkout
2. Atualizar total quando selecionar bairro
3. Mostrar tempo estimado
4. Validar se bairro está nas zonas

---

**YumGo** - Delivery inteligente e justo! 🚚📦

**Agora cada bairro tem seu preço justo baseado na distância!**
