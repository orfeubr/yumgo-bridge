# 🧪 Guia Completo: Testar Emissão de NFC-e (Homologação Gratuita)

> **Objetivo:** Testar a emissão automática de NFC-e sem custo, usando o ambiente de homologação da SEFAZ

---

## 📋 Pré-requisitos

✅ Restaurante cadastrado no YumGo
✅ CNPJ válido
✅ Inscrição Estadual válida
✅ Dados fiscais completos (endereço, razão social, etc)

---

## 🎯 OPÇÃO 1: Certificado de Homologação GRATUITO (Recomendado)

### Passo 1: Obter Certificado de Teste da SEFAZ

A SEFAZ oferece certificados digitais **GRATUITOS** para ambiente de homologação!

**🔗 Links por Estado:**

| Estado | Portal SEFAZ | Link Direto |
|--------|--------------|-------------|
| SP | Fazenda SP | https://www.fazenda.sp.gov.br/nfce/ |
| RJ | Fazenda RJ | https://www.fazenda.rj.gov.br/nfce/ |
| MG | Fazenda MG | https://www.fazenda.mg.gov.br/nfe/ |
| BA | Sefaz BA | https://www.sefaz.ba.gov.br/nfce/ |
| PR | Sefaz PR | https://www.fazenda.pr.gov.br/nfce/ |
| RS | Sefaz RS | https://www.sefaz.rs.gov.br/nfe/ |
| SC | Fazenda SC | https://www.sef.sc.gov.br/nfce/ |
| GO | Sefaz GO | https://www.sefaz.go.gov.br/nfe/ |

**📝 Procedimento Geral (varia por estado):**

1. Acesse o portal da SEFAZ do seu estado
2. Procure por: **"Ambiente de Homologação"** ou **"Certificado de Teste"**
3. Faça login com certificado A1 (ou e-CNPJ se disponível)
4. Baixe o **Certificado de Homologação** (.pfx)
5. Anote a senha fornecida

**💡 Alternativa: Gerar Certificado Auto-assinado**

Se sua SEFAZ não oferece certificado gratuito, use esta ferramenta:

```bash
# Gerar certificado auto-assinado para TESTES
openssl req -x509 -newkey rsa:2048 -keyout key.pem -out cert.pem -days 365 -nodes

# Converter para .pfx (formato do certificado A1)
openssl pkcs12 -export -out certificado-teste.pfx -inkey key.pem -in cert.pem

# Digite uma senha quando solicitado (ex: "123456")
```

⚠️ **Este certificado só funciona em HOMOLOGAÇÃO!**

---

### Passo 2: Obter CSC de Homologação

O **CSC (Código de Segurança do Contribuinte)** é obrigatório para NFC-e.

**Para HOMOLOGAÇÃO, use valores padrão:**

| Campo | Valor Padrão |
|-------|--------------|
| **CSC ID** | `1` |
| **CSC Token** | `123456` (ou conforme documentação da SEFAZ) |

**📚 CSC por Estado (Homologação):**

- **SP:** ID=1, Token=`123456`
- **RJ:** ID=1, Token=`1234567890`
- **Outros:** Consulte a documentação da SEFAZ

🔗 **Documentação Oficial:**
https://www.nfe.fazenda.gov.br/portal/listaConteudo.aspx?tipoConteudo=tW+YMyk/50s=

---

### Passo 3: Configurar no Painel YumGo

**1. Acesse:** `https://{seu-slug}.yumgo.com.br/painel/configuracoes-fiscais`

**2. Preencha os dados:**

#### 📄 Emissão de NFC-e
- **Ambiente NFC-e:** Homologação (Testes) ⚠️

#### 🏢 Dados da Empresa
- **CNPJ:** 11.222.333/0001-44 (exemplo)
- **Razão Social:** Conforme cadastro
- **Inscrição Estadual:** Conforme cadastro
- **Inscrição Municipal:** (Opcional)
- **Regime Tributário:** Simples Nacional (mais comum)

#### 🔐 Certificado Digital A1
- **Upload:** Selecione o arquivo `.pfx` baixado
- **Senha:** Digite a senha do certificado

#### ⚙️ Configuração NFC-e
- **Série NFC-e:** `1` (padrão)
- **Número Atual:** `1` (primeira nota)
- **CSC ID:** `1`
- **CSC Token:** `123456` (ou o valor do seu estado)

#### 📍 Endereço Fiscal
- Preencha com o endereço da sede (conforme CNPJ)

**3. Clique em:** `Salvar Configurações`

✅ Se tudo estiver correto, você verá: **"✅ Certificado instalado"**

---

### Passo 4: Configurar Produtos com NCM/CFOP

Antes de emitir NFC-e, os produtos precisam ter classificação fiscal.

**Acesse:** `https://{seu-slug}.yumgo.com.br/painel/products`

**Para cada produto:**

1. Clique em **Editar**
2. Aba: **Informações Fiscais**
3. Preencha:
   - **NCM:** (ex: 19059090 para alimentos preparados)
   - **CFOP:** 5102 (vendas dentro do estado)
   - **CEST:** (Opcional, apenas se obrigatório)

**💡 Atalho: Use o botão "🤖 Classificar com IA"**
O sistema usa IA para sugerir NCM/CFOP automaticamente!

**📋 NCM Comuns para Delivery:**

| Produto | NCM | Descrição |
|---------|-----|-----------|
| 🍕 Pizza | 19059090 | Alimentos preparados |
| 🍔 Hambúrguer | 19059090 | Alimentos preparados |
| 🥤 Refrigerante | 22029900 | Bebidas não alcoólicas |
| 🍺 Cerveja | 22030000 | Bebidas alcoólicas |
| 💧 Água | 22021000 | Águas minerais |
| 🍦 Sorvete | 21050000 | Sorvetes |
| 🍰 Bolo | 19059090 | Produtos de confeitaria |

---

### Passo 5: Testar Emissão Automática

Agora vamos simular um pedido pago para testar a emissão!

#### Via Interface Web (Recomendado)

1. Faça um pedido de teste no cardápio
2. Finalize o pedido (PIX ou Cartão)
3. Simule o pagamento:
   ```bash
   # Acesse o painel admin
   php artisan tinker

   # Buscar último pedido
   $order = \App\Models\Order::latest()->first();

   # Marcar como pago (dispara emissão automática)
   $order->update(['payment_status' => 'paid']);

   exit
   ```

4. **Aguarde 10 segundos**

5. Verifique os logs:
   ```bash
   tail -f storage/logs/laravel.log | grep "NFC-e"
   ```

#### Logs Esperados:

```
📋 Despachando Job para emissão de NFC-e
🚀 Iniciando emissão NFC-e (Job)
✅ NFC-e emitida com sucesso!
Chave: 44260311222333000144650010000000011234567890
```

#### Via Painel Admin (Fácil)

1. **Acesse:** `https://{slug}.yumgo.com.br/painel/orders`
2. **Clique** no pedido de teste
3. **Mude** o status de pagamento para "Pago"
4. **Aguarde** ~10 segundos
5. **Recarregue** a página
6. Você verá: **"✅ NFC-e emitida - Chave: 4426..."**

---

### Passo 6: Verificar Nota Fiscal Emitida

**1. No Painel de Pedidos:**
```
https://{slug}.yumgo.com.br/painel/orders/{id}
```

Você verá:
- ✅ **Chave de Acesso:** 44 dígitos
- ✅ **XML da NFC-e:** Download disponível
- ✅ **DANFE (PDF):** Download da nota impressa

**2. No Banco de Dados:**
```bash
php artisan tinker

# Ver nota fiscal
$note = \App\Models\FiscalNote::latest()->first();
echo $note->nfce_key;
echo $note->status;
```

**3. Consultar na SEFAZ (Homologação):**

Acesse o portal da SEFAZ de homologação e consulte pela chave de acesso.

⚠️ **IMPORTANTE:** Notas de homologação **NÃO são válidas** fiscalmente! São apenas para teste.

---

## 🎯 OPÇÃO 2: Certificado A1 Comercial (Produção)

Se você já tem um certificado A1 válido e quer testar em **produção real:**

### Onde Comprar Certificado A1:

| Certificadora | Preço Médio | Link |
|---------------|-------------|------|
| **Certisign** | R$ 180/ano | https://certisign.com.br |
| **Serasa** | R$ 150/ano | https://www.serasaexperian.com.br/certificado-digital |
| **Valid** | R$ 170/ano | https://www.validcertificadora.com.br |
| **Soluti** | R$ 160/ano | https://www.soluti.com.br |

**📋 Tipos de Certificado:**
- **e-CNPJ A1:** Para pessoa jurídica (mais comum)
- **e-CPF A1:** Para pessoa física / MEI

**⏱️ Tempo de Emissão:**
- Videoconferência: ~2 horas
- Presencial (AR Correios): ~7 dias

**🔄 Renovação:**
- Certificado A1 vale **1 ano**
- Renovar antes do vencimento para não interromper emissões

---

### Obter CSC de Produção

**Passo a passo (SP como exemplo):**

1. Acesse: https://www.fazenda.sp.gov.br/nfce/
2. Login com **certificado A1**
3. Menu: **NFC-e > Gerar CSC**
4. Clique em **"Gerar Novo CSC"**
5. Copie:
   - **ID do CSC:** (ex: 2)
   - **Token do CSC:** (ex: E9F8A7B6C5D4...)
6. ⚠️ **Guarde com segurança!** Não é possível recuperar depois.

**📚 Documentação por Estado:**
- **SP:** https://www.fazenda.sp.gov.br/nfce/
- **RJ:** https://www.fazenda.rj.gov.br/nfce/
- Outros: Procure por "CSC NFC-e" no site da SEFAZ

---

### Configurar Produção no YumGo

1. Acesse: `/painel/configuracoes-fiscais`
2. **Ambiente NFC-e:** Selecione **"Produção"** ⚠️
3. Upload do certificado A1 comercial
4. CSC de **produção** (não use 123456!)
5. Salvar

✅ **Pronto!** Agora as notas serão emitidas com validade fiscal.

---

## 🐛 Troubleshooting (Problemas Comuns)

### ❌ Erro: "Certificado A1 não configurado"

**Solução:**
1. Verifique se fez upload do .pfx
2. Verifique se a senha está correta
3. Recarregue a página de configuração
4. Procure por: **"✅ Certificado instalado"**

---

### ❌ Erro: "CSC inválido"

**Solução:**
1. Homologação: Use CSC padrão (`ID=1`, `Token=123456`)
2. Produção: Gere novo CSC no portal da SEFAZ
3. Verifique se copiou corretamente (sem espaços)

---

### ❌ Erro: "Produto sem NCM"

**Solução:**
1. Edite o produto: `/painel/products/{id}/edit`
2. Aba: **Informações Fiscais**
3. Preencha NCM e CFOP
4. Ou use: **"🤖 Classificar com IA"**

---

### ❌ Erro: "Job falhou após 3 tentativas"

**Solução:**
1. Verifique os logs:
   ```bash
   tail -100 storage/logs/laravel.log | grep "NFC-e\|Erro"
   ```
2. Verifique se o worker está rodando:
   ```bash
   sudo supervisorctl status laravel-queue-nfce
   ```
3. Reprocesse jobs falhados:
   ```bash
   php artisan queue:retry all
   ```

---

### ❌ Erro: "Rejeição 999: Sistema indisponível"

**Solução:**
- A SEFAZ está instável (comum à noite/finais de semana)
- O job tentará novamente automaticamente (3 vezes)
- Aguarde alguns minutos e verifique novamente

---

## 📊 Monitoramento em Tempo Real

### Ver logs ao vivo:
```bash
tail -f storage/logs/laravel.log | grep "NFC-e"
```

### Monitorar fila:
```bash
php artisan queue:monitor redis
```

### Ver jobs falhados:
```bash
php artisan queue:failed
```

### Reprocessar job específico:
```bash
php artisan queue:retry {job-id}
```

---

## ✅ Checklist Completo

Antes de testar, confirme:

- [ ] Certificado A1 instalado (homologação ou produção)
- [ ] Senha do certificado correta
- [ ] CSC configurado (ID + Token)
- [ ] Dados fiscais completos (CNPJ, IE, endereço)
- [ ] Produtos com NCM/CFOP configurados
- [ ] Workers de fila rodando (`supervisorctl status`)
- [ ] Redis funcionando (`php artisan queue:monitor`)

---

## 🎉 Sucesso!

Se seguiu todos os passos, você terá:

✅ NFC-e emitida automaticamente quando cliente paga
✅ Chave de acesso gerada (44 dígitos)
✅ XML armazenado no banco
✅ DANFE disponível para download
✅ Conformidade fiscal automática

**Tempo total de emissão:** ~10 segundos ⚡

---

## 🆘 Suporte

**Documentação Oficial SEFAZ:**
- https://www.nfe.fazenda.gov.br/portal/principal.aspx

**Suporte YumGo:**
- Email: suporte@yumgo.com.br
- WhatsApp: (11) 99999-9999

**Logs Detalhados:**
- `/storage/logs/laravel.log`
- Procure por: `NFC-e`, `Fiscal`, `SEFAZ`

---

**Criado em:** 07/03/2026
**Versão:** 1.0
