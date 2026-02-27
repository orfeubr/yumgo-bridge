# 🧾 Integração Tributa AI - Emissão Automática de NFC-e

## 📋 Visão Geral

Sistema completo de emissão automática de **NFC-e (Nota Fiscal de Consumidor Eletrônico)** integrado com Tributa AI.

### ✨ Funcionalidades

- ✅ **Emissão automática** de NFC-e após confirmação de pagamento
- ✅ **Cálculo automático de impostos** pelo Tributa AI (todos os regimes tributários)
- ✅ **Download de PDF e XML** das notas emitidas
- ✅ **Cancelamento de notas** com motivo
- ✅ **Webhook** para receber atualizações de status
- ✅ **Configuração por restaurante** (multi-tenant)
- ✅ **Sandbox e Produção** separados

---

## 🎯 Como Funciona

### Fluxo Automático

```
1. Cliente faz pedido → Pagamento PIX/Cartão
2. Webhook Asaas confirma pagamento → payment_status = 'paid'
3. Observer detecta mudança → OrderFiscalObserver::updated()
4. TributaAiService::emitNFCe() → Envia dados para Tributa AI
5. Tributa AI processa → Calcula impostos automaticamente
6. SEFAZ autoriza → Status = 'authorized'
7. Webhook Tributa AI notifica → Atualiza chave de acesso, PDF, XML
8. Cliente recebe nota por email (futuro)
```

---

## 🛠️ Arquitetura

### Arquivos Criados

```
app/
├── Services/
│   └── TributaAiService.php                 # Service principal
├── Observers/
│   └── OrderFiscalObserver.php              # Emissão automática
├── Models/
│   └── FiscalNote.php                       # Model de notas fiscais
├── Http/Controllers/
│   └── TributaAiWebhookController.php       # Webhook handler
└── Filament/Restaurant/
    ├── Resources/
    │   └── FiscalNoteResource.php           # CRUD de notas
    └── Pages/
        └── FiscalSettings.php               # Configuração fiscal

database/migrations/
├── 2026_02_25_193028_add_tributaai_fields_to_tenants_table.php
└── tenant/
    └── 2026_02_25_193144_create_fiscal_notes_table.php

resources/views/filament/restaurant/pages/
└── fiscal-settings.blade.php

routes/
└── tenant.php                               # Rota do webhook
```

---

## 📊 Tabelas do Banco

### `tenants` (Central) - Novos Campos

```sql
-- Tributa AI
tributaai_token          VARCHAR(255)    # Token API
tributaai_enabled        BOOLEAN         # Ativar/desativar
tributaai_environment    ENUM            # sandbox/production

-- Dados Fiscais
cnpj                     VARCHAR(18)     # 99.999.999/9999-99
razao_social             VARCHAR(255)    # Nome da empresa
inscricao_estadual       VARCHAR(20)     # IE
inscricao_municipal      VARCHAR(20)     # IM (opcional)
regime_tributario        ENUM            # simples_nacional/lucro_presumido/lucro_real/mei

-- Certificado Digital A1
certificate_a1           TEXT            # Base64 do certificado
certificate_password     VARCHAR(255)    # Senha do certificado

-- Configuração NFC-e
nfce_serie              INTEGER          # Série (geralmente 1)
nfce_numero             INTEGER          # Número atual (incrementado automaticamente)
csc_id                  VARCHAR(255)     # ID do CSC (SEFAZ)
csc_token               VARCHAR(255)     # Token CSC (SEFAZ)

-- Endereço Fiscal
fiscal_address          VARCHAR(255)     # Logradouro
fiscal_number           VARCHAR(20)      # Número
fiscal_complement       VARCHAR(255)     # Complemento
fiscal_neighborhood     VARCHAR(255)     # Bairro
fiscal_city             VARCHAR(255)     # Cidade
fiscal_state            VARCHAR(2)       # UF
fiscal_zipcode          VARCHAR(9)       # CEP
```

### `fiscal_notes` (Tenant) - Tabela Completa

```sql
id                      BIGINT PRIMARY KEY
order_id                BIGINT          # FK para orders
tributaai_note_id       VARCHAR(255)    # ID no Tributa AI
note_number             INTEGER         # Número da nota
serie                   INTEGER         # Série
status                  ENUM            # pending/processing/authorized/rejected/cancelled/error
chave_acesso            VARCHAR(44)     # Chave de acesso (44 dígitos)
protocolo               VARCHAR(255)    # Protocolo SEFAZ
pdf_url                 TEXT            # URL do PDF
xml_url                 TEXT            # URL do XML
emission_date           TIMESTAMP       # Data de emissão
authorization_date      TIMESTAMP       # Data de autorização SEFAZ
cancellation_date       TIMESTAMP       # Data de cancelamento
error_message           TEXT            # Mensagem de erro/rejeição
cancellation_reason     VARCHAR(255)    # Motivo do cancelamento
raw_response            JSON            # Response completa da API
total_value             DECIMAL(10,2)   # Valor total da nota
tax_value               DECIMAL(10,2)   # Valor dos impostos
created_at              TIMESTAMP
updated_at              TIMESTAMP

INDEX(status)
INDEX(emission_date)
INDEX(note_number)
```

---

## 🚀 Como Configurar

### 1. Criar Conta no Tributa AI

1. Acesse [tributa.ai](https://tributa.ai)
2. Crie sua conta (tem plano gratuito para sandbox)
3. Obtenha seu **Token API** em Configurações > API

### 2. Obter CSC (Código de Segurança do Contribuinte)

**O que é?**
Código obrigatório para emissão de NFC-e, obtido no portal da SEFAZ do seu estado.

**Como obter:**
1. Acesse o portal da SEFAZ do seu estado
2. Vá em Serviços > NFC-e > CSC
3. Gere um novo CSC (você receberá ID + Token)

### 3. Configurar no Painel do Restaurante

1. Acesse: `https://seu-restaurante.yumgo.com.br/painel/configuracoes-fiscais`
2. Preencha:
   - **Token API**: Token do Tributa AI
   - **Ambiente**: Sandbox (para testes)
   - **CNPJ**: 99.999.999/9999-99
   - **Razão Social**: Nome da empresa
   - **Inscrição Estadual**: IE
   - **Regime Tributário**: Simples Nacional (ou outro)
   - **CSC ID + CSC Token**: Obtidos na SEFAZ
   - **Endereço Fiscal**: Dados da sede
3. Clique em "Salvar Configurações"

### 4. Certificado Digital A1

O Tributa AI gerencia seu certificado A1. Faça upload no painel deles:
- Tributa AI Dashboard > Certificados > Upload Certificado A1

---

## 📝 Dados Enviados para Tributa AI

```json
{
  "natureza_operacao": "VENDA",
  "tipo_documento": "1",
  "cnpj_emitente": "99999999000199",
  "regime_tributario": "1",
  "serie": 1,
  "numero": 123,
  "indicador_presenca": "1",
  "indicador_consumidor_final": "1",
  "nome_destinatario": "João Silva",
  "cpf_destinatario": "12345678900",
  "email_destinatario": "joao@example.com",
  "itens": [
    {
      "numero_item": 1,
      "codigo_produto": "456",
      "descricao": "Pizza Grande Mussarela",
      "ncm": "19059090",
      "cfop": "5405",
      "unidade_comercial": "UN",
      "quantidade_comercial": 1,
      "valor_unitario_comercial": 45.00,
      "valor_total": 45.00
    }
  ],
  "valor_produtos": 45.00,
  "valor_frete": 5.00,
  "valor_desconto": 0,
  "valor_total": 50.00,
  "forma_pagamento": "0",
  "meio_pagamento": "17",
  "valor_pagamento": 50.00,
  "csc_id": "1",
  "csc": "SEU_CSC_TOKEN"
}
```

### 🔢 Códigos Importantes

**NCM (Nomenclatura Comum do Mercosul):**
- `19059090` - Produtos de padaria/alimentação (padrão)
- Pode ser customizado por produto (futuro)

**CFOP (Código Fiscal de Operações):**
- `5405` - Venda de alimentação (bares, restaurantes)
- `5102` - Venda de mercadoria (dentro do estado)
- `6102` - Venda de mercadoria (fora do estado)

**Regime Tributário:**
- `1` - Simples Nacional
- `2` - Simples Nacional - Excesso
- `3` - Lucro Presumido / Lucro Real

**Forma de Pagamento:**
- `0` - À vista
- `1` - Parcelado

**Meio de Pagamento:**
- `01` - Dinheiro
- `03` - Cartão de Crédito
- `04` - Cartão de Débito
- `17` - PIX
- `99` - Outros

---

## 🔄 Webhook do Tributa AI

### Configurar no Painel Tributa AI

1. Acesse: Tributa AI Dashboard > Webhooks
2. Adicione webhook para cada tenant:
   - **URL**: `https://seu-restaurante.yumgo.com.br/api/v1/webhooks/tributaai`
   - **Eventos**: `nfce.authorized`, `nfce.rejected`, `nfce.cancelled`, `nfce.error`

### Eventos Suportados

#### `nfce.authorized` - Nota Autorizada pela SEFAZ
```json
{
  "event": "nfce.authorized",
  "nfce": {
    "id": "abc123",
    "status": "autorizada",
    "chave_acesso": "35240199999999000199650010001234561123456789",
    "protocolo": "135240099999999",
    "pdf_url": "https://...",
    "xml_url": "https://..."
  }
}
```

#### `nfce.rejected` - Nota Rejeitada
```json
{
  "event": "nfce.rejected",
  "nfce": {
    "id": "abc123",
    "status": "rejeitada",
    "mensagem": "Rejeição 530: CPF inválido"
  }
}
```

---

## 🎯 Uso no Painel

### Ver Notas Fiscais

1. Acesse: `/painel/notas-fiscais`
2. Listagem com:
   - Número da nota
   - Pedido vinculado
   - Status (badge colorido)
   - Valor
   - Data de emissão
   - Chave de acesso

### Ações Disponíveis

- **📄 Download PDF**: Baixar DANFE (Documento Auxiliar da NFC-e)
- **📄 Download XML**: Baixar XML da nota
- **👁️ Visualizar**: Ver detalhes completos
- **❌ Cancelar**: Cancelar nota autorizada (requer motivo mínimo de 15 caracteres)

### Detalhes da Nota

Ao visualizar uma nota:
- Informações da nota (número, série, status)
- Dados fiscais (chave de acesso, protocolo SEFAZ)
- Datas (emissão, autorização, cancelamento)
- Mensagens de erro/cancelamento (se houver)

---

## ⚠️ Tratamento de Erros

### Erros Comuns

1. **Token inválido**
   - Verificar se o token está correto
   - Verificar se o ambiente está correto (sandbox/produção)

2. **CSC inválido**
   - Verificar ID e Token do CSC
   - Gerar novo CSC na SEFAZ se necessário

3. **Certificado expirado**
   - Renovar certificado A1 no Tributa AI

4. **Dados incompletos**
   - Verificar se CNPJ, IE, Endereço Fiscal estão preenchidos
   - Verificar se cliente tem CPF válido

5. **Rejeição SEFAZ**
   - Ver mensagem de erro na nota fiscal
   - Corrigir dados e tentar novamente

### Logs

Todos os eventos são logados:
- Emissão de nota
- Autorização
- Rejeição
- Cancelamento
- Erros

Acesse: `storage/logs/laravel.log`

---

## 🧪 Testando no Sandbox

1. Configure ambiente = "Sandbox"
2. Use dados de teste do Tributa AI
3. Crie um pedido de teste
4. Confirme o pagamento (via API ou webhook simulado)
5. Verifique se a nota foi emitida em `/painel/notas-fiscais`
6. Baixe PDF/XML para validar

---

## 🔐 Segurança

- ✅ Token armazenado criptografado no banco
- ✅ Certificado A1 gerenciado pelo Tributa AI (não no nosso servidor)
- ✅ CSC nunca exposto no frontend
- ✅ Webhook valida origem (header do Tributa AI)
- ✅ Isolamento por tenant (multi-tenant seguro)

---

## 📊 Cálculo Automático de Impostos

### Como Funciona

O **Tributa AI calcula TODOS os impostos automaticamente** baseado em:

1. **Regime Tributário** (Simples Nacional, Lucro Presumido, etc.)
2. **NCM** (Classificação fiscal do produto)
3. **CFOP** (Tipo de operação)
4. **Valores** (unitário, quantidade, total)

### Impostos Calculados

- **ICMS** (Imposto sobre Circulação de Mercadorias)
- **PIS** (Programa de Integração Social)
- **COFINS** (Contribuição para Financiamento da Seguridade Social)
- **IPI** (Imposto sobre Produtos Industrializados) - se aplicável
- **ISS** (Imposto sobre Serviços) - se aplicável

**Você NÃO precisa calcular manualmente!** 🎉

---

## 🚀 Produção

### Checklist antes de ir para produção:

- [ ] Ambiente = "Produção"
- [ ] Token API de produção (não sandbox)
- [ ] Certificado A1 válido e atualizado
- [ ] CSC de produção (não homologação)
- [ ] CNPJ, IE, Endereço Fiscal validados
- [ ] Série NFC-e autorizada pela SEFAZ
- [ ] Webhook configurado com URL de produção
- [ ] Testes realizados em ambiente sandbox
- [ ] Backup dos dados fiscais

---

## 💰 Custos Tributa AI

### Planos

- **Gratuito**: Até 100 notas/mês (sandbox ilimitado)
- **Básico**: R$ 49/mês - Até 500 notas
- **Pro**: R$ 99/mês - Até 2.000 notas
- **Empresarial**: R$ 199/mês - Ilimitado

**Vantagem**: Sem taxa por nota (fixed price), ideal para alto volume!

---

## 🆘 Suporte

### Problemas com a Integração

1. Verificar logs: `storage/logs/laravel.log`
2. Verificar tabela `fiscal_notes` no banco
3. Verificar webhook do Tributa AI
4. Contatar suporte: [suporte@tributa.ai](mailto:suporte@tributa.ai)

### Problemas com SEFAZ

1. Portal da SEFAZ do seu estado
2. Validar certificado A1
3. Validar CSC
4. Consultar status de notas: Tributa AI Dashboard

---

## 📚 Documentação Adicional

- [Tributa AI - Documentação](https://docs.tributa.ai)
- [SEFAZ - Portal NFC-e](https://www.nfe.fazenda.gov.br)
- [Códigos NCM](https://www.gov.br/receitafederal/pt-br/assuntos/aduana-e-comercio-exterior/manuais/ncm)

---

**Desenvolvido com ❤️ para DeliveryPro**
**Data**: 25/02/2026
