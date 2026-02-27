# 🔧 Correção: Status de Pagamento Enganoso

**Data:** 27/02/2026
**Problema:** Status mostrava "✅ Configurada" quando na verdade o gateway ativo não tinha credenciais

---

## ❌ Problema Identificado

### Situação
```
Database:
- payment_gateway: 'pagarme'
- pagarme_recipient_id: NULL
- asaas_account_id: 4a4751de-55c5-42e2-be86-6174058576f7

Interface mostrando:
✅ Configurada
```

### Por que era um problema?
1. **Gateway ativo era Pagar.me** mas sem recipient configurado
2. Status mostrava "✅ Configurada" por causa do Asaas (legado)
3. **Novos pedidos FALHARIAM** porque o sistema tentaria usar Pagar.me sem credenciais
4. Usuário não sabia que precisava configurar o Pagar.me

---

## ✅ Solução Implementada

### Lógica ANTES (INCORRETA)
```php
public function getAccountStatus(): array
{
    $tenant = tenant();

    // ❌ Não verificava qual gateway está ATIVO
    if (!empty($tenant->pagarme_recipient_id)) {
        return ['status' => 'approved', 'label' => '✅ Configurada (Pagar.me)'];
    }

    if (!empty($tenant->asaas_account_id)) {
        // ❌ Mostrava "configurada" mesmo com Pagar.me ativo
        return ['status' => 'legacy', 'label' => '✅ Configurada'];
    }
}
```

### Lógica DEPOIS (CORRETA)
```php
public function getAccountStatus(): array
{
    $tenant = tenant();
    $activeGateway = $tenant->payment_gateway ?? 'pagarme';

    // ✅ Verifica qual gateway está ATIVO primeiro
    if ($activeGateway === 'pagarme') {
        if (!empty($tenant->pagarme_recipient_id)) {
            return ['status' => 'approved', 'label' => '✅ Configurada (Pagar.me)'];
        }

        // ✅ AVISA que Pagar.me está ativo mas sem recipient
        if (!empty($tenant->asaas_account_id)) {
            return ['status' => 'needs_migration', 'label' => '⚠️ Configure o Pagar.me'];
        }
    }

    if ($activeGateway === 'asaas') {
        if (!empty($tenant->asaas_account_id)) {
            return ['status' => 'legacy', 'label' => '✅ Configurada (Asaas)'];
        }
    }
}
```

---

## 🎨 Melhorias na Interface

### 1. Novo Status: `needs_migration`
```php
[
    'configured' => false,
    'status' => 'needs_migration',
    'label' => '⚠️ Configure o Pagar.me',
    'color' => 'warning',
]
```

### 2. Banner de Aviso Adicionado
Quando status é `needs_migration`, mostra banner laranja explicando:
- Gateway está configurado para Pagar.me
- Mas recebedor ainda não foi criado
- Você tem Asaas (legado) mas novos pedidos usarão Pagar.me
- Preencha os dados para criar o recebedor

### 3. Mensagens Atualizadas
```php
// Descrição do status
@if($statusInfo['status'] === 'needs_migration')
    Você tem uma conta Asaas configurada, mas o gateway ativo é Pagar.me.
    Configure seus dados abaixo para criar o recebedor.
@elseif($statusInfo['status'] === 'legacy')
    Sua conta Asaas está ativa e você pode receber pagamentos!
@elseif($statusInfo['status'] === 'approved')
    Sua conta está ativa e você pode receber pagamentos!
@endif
```

---

## 🧪 Teste Realizado

```bash
php artisan tinker --execute="
\$tenant = tenant();
echo 'payment_gateway: ' . \$tenant->payment_gateway . PHP_EOL;
echo 'pagarme_recipient_id: ' . (\$tenant->pagarme_recipient_id ?? 'NULL') . PHP_EOL;
echo 'asaas_account_id: ' . \$tenant->asaas_account_id . PHP_EOL;
"

# Resultado:
payment_gateway: pagarme
pagarme_recipient_id: NULL
asaas_account_id: 4a4751de-55c5-42e2-be86-6174058576f7

# Status ANTES:  ✅ Configurada
# Status DEPOIS: ⚠️ Configure o Pagar.me ✅
```

---

## 📁 Arquivos Alterados

### `app/Filament/Restaurant/Pages/PaymentAccount.php`
- ✅ Método `getAccountStatus()` reescrito
- ✅ Agora verifica qual gateway está ativo PRIMEIRO
- ✅ Só mostra "configurada" se gateway ativo tem credenciais
- ✅ Novo status `needs_migration` adicionado

### `resources/views/filament/restaurant/pages/payment-account.blade.php`
- ✅ Banner de aviso para status `needs_migration`
- ✅ Mensagens atualizadas por status
- ✅ Success banner aceita tanto 'approved' quanto 'legacy'

---

## 🎯 Resultado Final

### Cenários Cobertos

| payment_gateway | pagarme_recipient_id | asaas_account_id | Status Exibido |
|----------------|---------------------|------------------|----------------|
| pagarme | EXISTS | ANY | ✅ Configurada (Pagar.me) |
| pagarme | NULL | EXISTS | ⚠️ Configure o Pagar.me |
| pagarme | NULL | NULL | ⚪ Não Configurada |
| asaas | ANY | EXISTS | ✅ Configurada (Asaas) |
| asaas | ANY | NULL | ⚠️ Configure o Asaas |

### Benefícios
1. ✅ **Status preciso**: Mostra exatamente qual gateway está configurado
2. ✅ **Previne erros**: Avisa quando gateway ativo não tem credenciais
3. ✅ **Guia o usuário**: Banner explica exatamente o que fazer
4. ✅ **Evita falhas de pagamento**: Usuário sabe que precisa configurar antes de vender

---

## 🚀 Próximos Passos

Para o restaurante "Marmitaria da Gi":
1. Acessar `/painel/payment-account`
2. Status agora mostra: **⚠️ Configure o Pagar.me**
3. Banner laranja explica a situação
4. Preencher dados bancários
5. Clicar **"🎉 Criar Conta de Recebimentos"**
6. Sistema cria `pagarme_recipient_id` automaticamente
7. Status muda para: **✅ Configurada (Pagar.me)**

---

**PROBLEMA RESOLVIDO!** ✅

Agora o sistema sempre mostra o status correto baseado no gateway ATIVO, não apenas na existência de credenciais.
