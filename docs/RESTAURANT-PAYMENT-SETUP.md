# Configuração de Recebimentos para Restaurantes

## ❓ Perguntas Frequentes

### 1. A conta do cliente precisa ser Asaas?

**❌ NÃO!** O cliente **não precisa** ter conta Asaas.

- ✅ Cliente paga via **PIX** (qualquer banco)
- ✅ Cliente paga via **Cartão de Crédito/Débito** (qualquer bandeira)
- ✅ Cliente paga em **Dinheiro** na entrega

**Apenas o RESTAURANTE precisa configurar conta Asaas** para receber os pagamentos.

### 2. Como funciona o fluxo?

```
1. Cliente faz pedido no app → R$ 100,00
2. Cliente escolhe: PIX, Cartão ou Dinheiro
3. Sistema processa pagamento via Asaas
4. Split automático:
   ├─ R$ 97,00 → Conta do Restaurante (97%)
   └─ R$ 3,00 → Comissão da Plataforma (3%)
5. Restaurante recebe na sua conta bancária
```

### 3. Quando o restaurante recebe?

| Método de Pagamento | Prazo de Repasse |
|---------------------|------------------|
| PIX | D+1 (Plano Starter) ou D+0 (Plano Pro) |
| Cartão de Crédito | D+1 (1 dia) |
| Dinheiro | Recebe na hora da entrega |

---

## 🛠️ Como o Restaurante Configura

### Passo 1: Acessar Painel

```
https://seu-restaurante.eliseus.com.br/painel/login
```

### Passo 2: Menu Lateral → "Pagamentos"

Clicar em **"Pagamentos"** no menu de Configurações.

### Passo 3: Preencher Formulário

#### Dados da Empresa
- **Razão Social / Nome Completo** (obrigatório)
- **CPF ou CNPJ** (obrigatório)
- **Tipo de Empresa**: MEI, Ltda, Pessoa Física, etc
- **Telefone** e **Celular**

#### Endereço
- **CEP** (busca automática do endereço)
- Rua, Número, Complemento
- Bairro, Cidade, Estado

#### Dados Bancários (para receber)
- **Tipo de Conta**: Corrente ou Poupança
- **Banco**
- **Agência**
- **Conta** + **Dígito**

**⚠️ IMPORTANTE**: Dados bancários devem ser da **mesma titularidade** do CPF/CNPJ informado!

### Passo 4: Salvar

Clicar em **"Criar Conta de Recebimentos"**

Sistema irá:
1. Validar os dados
2. Criar sub-conta no Asaas
3. Enviar para análise (KYC)

### Passo 5: Aguardar Aprovação

Status aparecerá como:
- ⏳ **Aguardando Aprovação** (1-2 dias úteis)
- ✅ **Aprovada** (pode receber pagamentos)
- ❌ **Rejeitada** (corrigir dados e tentar novamente)

---

## 💰 Quanto o Restaurante Recebe?

### Exemplo Real

**Pedido: R$ 100,00**

| Item | Valor |
|------|-------|
| Subtotal do pedido | R$ 100,00 |
| (-) Comissão Plataforma (3%) | R$ 3,00 |
| **= Você recebe** | **R$ 97,00** |
| (-) Taxa Asaas PIX | R$ 0,99 |
| **= Líquido na sua conta** | **R$ 96,01** |

**vs iFood**:
- iFood cobra **30% de comissão** = R$ 30,00
- Você teria apenas R$ 70,00
- **Economia de R$ 26,01 por pedido!** 🎉

### Comparação de Custos

| Gateway | Comissão | Taxa PIX | Taxa Cartão | Total (pedido R$ 100) |
|---------|----------|----------|-------------|----------------------|
| **DeliveryPro (Asaas)** | 3% | R$ 0,99 | 2,99% + R$ 0,49 | **R$ 96,01** (PIX) |
| iFood | 30% | Incluído | Incluído | R$ 70,00 |
| Rappi | 28% | Incluído | Incluído | R$ 72,00 |
| Uber Eats | 25% | Incluído | Incluído | R$ 75,00 |

**Economia mensal (1000 pedidos/mês de R$ 50)**:

```
DeliveryPro: R$ 48.005,00 líquido
iFood:       R$ 35.000,00 líquido
DIFERENÇA:   R$ 13.005,00/mês! 💰
```

---

## 🔐 Segurança e Validação

### KYC (Know Your Customer)

Asaas valida:
- ✅ CPF/CNPJ na Receita Federal
- ✅ Dados bancários (teste de depósito)
- ✅ Endereço (via CEP)
- ✅ Telefone (SMS de verificação)

### Proteção Anti-Fraude

- Dados criptografados (SSL/TLS)
- Split acontece na mesma transação (auditável)
- Impossível alterar percentual de comissão
- Repasses automáticos (sem manual)

---

## 🚀 Código do Painel (Filament)

### Arquivo Principal

`app/Filament/Restaurant/Pages/PaymentSettings.php`

**Features**:
- ✅ Busca CEP automática (ViaCEP)
- ✅ Validação de CPF/CNPJ
- ✅ Máscaras de telefone
- ✅ Status visual da conta (pendente/aprovada)
- ✅ Criação de sub-conta Asaas
- ✅ Atualização de dados

### Registrar no Painel

Adicionar no `RestaurantPanelProvider.php`:

```php
use App\Filament\Restaurant\Pages\PaymentSettings;

public function panel(Panel $panel): Panel
{
    return $panel
        ->pages([
            PaymentSettings::class,
        ])
        // ...
}
```

---

## 📱 Fluxo Completo

### 1. Cliente Faz Pedido (App Mobile)

```javascript
const response = await fetch('/api/v1/orders', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    items: [
      { product_id: 1, quantity: 2 }
    ],
    delivery_address: 'Rua Teste, 123',
    payment_method: 'pix', // ou credit_card, debit_card, cash
  }),
})

const { order, payment } = await response.json()

// Se PIX, exibe QR Code
if (payment?.qrcode_image) {
  showPixQrCode(payment.qrcode_image, payment.qrcode_text)
}
```

### 2. Sistema Cria Pagamento Asaas (Backend)

`OrderService::createOrder()` automaticamente:
1. Calcula split (97% restaurante + 3% plataforma)
2. Cria cobrança no Asaas
3. Retorna QR Code (se PIX)
4. Aguarda webhook de confirmação

### 3. Webhook Confirma Pagamento

```
POST /api/v1/webhooks/asaas

{
  "event": "PAYMENT_CONFIRMED",
  "payment": {
    "id": "pay_123",
    "status": "RECEIVED"
  }
}
```

Sistema:
1. Confirma pedido
2. Adiciona cashback ao cliente
3. Notifica restaurante
4. Agenda repasse (D+1 para PIX no Plano Starter)

### 4. Restaurante Recebe

Asaas transfere automaticamente para conta bancária cadastrada.

---

## 🎯 Checklist de Implementação

- [x] Migration de campos Asaas em tenants
- [x] Página Filament de configuração
- [x] AsaasService com createSubAccount()
- [x] Integração de pagamento no OrderService
- [x] Webhook para confirmação
- [x] Documentação completa
- [ ] Testes automatizados
- [ ] Notificação por email (aprovação/rejeição)
- [ ] Dashboard de repasses

---

## 📞 Suporte

**Restaurante com dúvidas?**

1. Verificar documentação: `/docs/ASAAS-INTEGRATION.md`
2. Testar em sandbox primeiro
3. Contatar suporte da plataforma

**Problemas comuns**:
- ❌ Conta rejeitada → CPF/CNPJ inválido ou dados bancários incorretos
- ❌ Não recebe → Aguardar prazo (D+1 para PIX/cartão no Plano Starter)
- ❌ Split errado → Verificar configuração de comissão no plano

---

**Última atualização**: 21/02/2026
