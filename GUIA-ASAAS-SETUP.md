# 💳 Guia Completo: Configurar Asaas para Receber Pagamentos

**Data:** 22/02/2026
**Status:** ✅ PÁGINA CRIADA E FUNCIONAL

---

## 🎯 O QUE FOI CRIADO

### **Nova Página no Painel:** `Pagamentos Asaas`

Agora você tem uma interface completa para configurar sua conta Asaas diretamente pelo painel do restaurante!

**URL:** `https://marmitaria-gi.eliseus.com.br/painel/asaas-setup`

---

## 🚀 COMO CONFIGURAR (3 PASSOS)

### **Passo 1: Criar Conta Asaas** (5 minutos)

1. **Acesse:** https://www.asaas.com
2. **Clique em:** "Criar Conta Grátis"
3. **Preencha:**
   - Nome completo
   - E-mail
   - Telefone
   - CPF ou CNPJ
4. **Confirme:** E-mail de verificação
5. **Pronto!** Conta criada

### **Passo 2: Gerar API Key** (2 minutos)

1. **Faça login** no Asaas
2. **Vá em:** Menu → Integrações → API Key
3. **Escolha o ambiente:**
   - **🧪 Sandbox (Testes):** Para testar sem cobrar de verdade
   - **🚀 Produção (Real):** Para cobranças reais

4. **Clique em:** "Gerar Nova Chave"
5. **Copie** a chave (algo como: `$aact_YTU5YTE0M2M2N2I4MTliNzk0YTI...`)
6. **IMPORTANTE:** Guarde em local seguro, não compartilhe!

### **Passo 3: Configurar no Sistema** (1 minuto)

1. **Acesse:** `Painel → Pagamentos Asaas`
2. **Preencha:**
   - **Ambiente:** Sandbox (para testar) ou Produção (para valer)
   - **API Key:** Cole a chave que você copiou
   - **Wallet ID:** Deixe vazio por enquanto (opcional)

3. **Clique em:** "🔍 Testar Conexão"
4. **Se der sucesso:** Clique em "💾 Salvar Configurações"
5. **Pronto!** ✅ Configuração completa

---

## 📋 AMBIENTES: SANDBOX vs PRODUÇÃO

### **🧪 Sandbox (Testes)**

**Use para:**
- Testar pagamentos sem cobrar de verdade
- Desenvolver e debugar
- Treinar equipe

**Vantagens:**
- ✅ Grátis
- ✅ Pagamentos fictícios
- ✅ Não precisa de aprovação de documentos
- ✅ Perfeito para aprender

**Limitações:**
- ❌ Não recebe dinheiro real
- ❌ Clientes não podem pagar de verdade

**Como testar:**
```
1. Configure com API Key do Sandbox
2. Faça um pedido no seu site
3. Sistema gera link de pagamento
4. Use dados de teste para pagar
5. Veja o pagamento sendo confirmado
```

### **🚀 Produção (Real)**

**Use para:**
- Receber pagamentos reais
- Vender de verdade

**Requisitos:**
- ✅ Conta Asaas aprovada
- ✅ Documentos enviados
- ✅ Dados bancários configurados

**Vantagens:**
- ✅ Recebe dinheiro real
- ✅ Clientes podem pagar de verdade
- ✅ Transferência automática para sua conta

---

## 💰 TAXAS ASAAS (Produção)

```
┌─────────────────────────────────────────────┐
│  MÉTODO          │  TAXA POR TRANSAÇÃO      │
├─────────────────────────────────────────────┤
│  💰 PIX          │  R$ 0,99                 │
│  💳 Crédito      │  2,99% + R$ 0,49         │
│  💵 Débito       │  2,49% + R$ 0,49         │
│  📄 Boleto       │  R$ 3,49                 │
│  ⚡ Link Pag     │  Mesmas taxas acima      │
└─────────────────────────────────────────────┘
```

**Exemplo prático:**
- Pedido de R$ 50,00 via PIX = Você recebe R$ 49,01 (desconta R$ 0,99)
- Pedido de R$ 50,00 via Cartão = Você recebe R$ 47,56 (desconta 2,99% + R$ 0,49)

---

## 🎯 O QUE ACONTECE DEPOIS DE CONFIGURAR?

### **1. Pedidos geram cobranças automáticas**

Quando um cliente faz pedido online:
```
Cliente escolhe produtos → Adiciona ao carrinho
→ Vai para checkout → Escolhe forma de pagamento
→ Sistema gera cobrança no Asaas automaticamente
→ Cliente recebe link/QR Code para pagar
→ Paga via PIX/Cartão
→ Asaas confirma pagamento
→ Sistema atualiza status do pedido
→ Você é notificado
→ Prepara o pedido
```

### **2. Você recebe o dinheiro**

- **PIX:** Cai na hora na sua conta Asaas
- **Cartão Crédito:** D+1 (1 dia útil)
- **Boleto:** D+1 após confirmação

### **3. Transferir para sua conta bancária**

```
Asaas → Transferir
├─ Transferência agendada (grátis) - D+1
└─ Transferência imediata (paga taxa) - na hora
```

---

## 🔒 SEGURANÇA

### **API Key é SENSÍVEL!**

⚠️ **NUNCA compartilhe sua API Key!**

```
✅ BOM:
- Guardar em gerenciador de senhas
- Usar variáveis de ambiente
- Configurar apenas em produção

❌ RUIM:
- Compartilhar por WhatsApp
- Postar em redes sociais
- Commitar no GitHub público
- Dar para funcionários
```

### **Se vazar, revogue imediatamente:**

1. Acesse Asaas → Integrações → API Key
2. Clique em "Revogar chave"
3. Gere nova chave
4. Atualize no sistema

---

## 🧪 TESTANDO PAGAMENTOS (SANDBOX)

### **Dados para Teste**

**Cartão de Crédito (Aprovado):**
```
Número: 5162 3060 8285 9820
CVV: 318
Validade: 12/2030
Nome: APROVADO
```

**Cartão de Crédito (Negado):**
```
Número: 5186 0511 4275 1483
CVV: 318
Validade: 12/2030
Nome: NEGADO
```

**PIX:**
```
Qualquer QR Code gerado no Sandbox
será automaticamente aprovado após 10 segundos
```

---

## 📊 STATUS DA CONTA

### **Possíveis Status:**

```
⚪ Não Configurada
   └─ Você ainda não configurou a API Key

🟡 Em Análise (Produção)
   └─ Aguardando aprovação de documentos

🟢 Ativa (Produção)
   └─ Pode receber pagamentos reais

🔵 Sandbox
   └─ Modo de testes ativo

🔴 Suspensa
   └─ Conta bloqueada (contate Asaas)
```

---

## ❓ PERGUNTAS FREQUENTES

### **Preciso pagar mensalidade?**
❌ Não! Asaas não cobra mensalidade.
Você paga apenas pelas transações.

### **Quanto tempo demora para aprovar minha conta?**
⏱️ Geralmente 1-3 dias úteis (Produção).
Sandbox é instantâneo.

### **Posso usar Asaas com MEI?**
✅ Sim! Aceita MEI, ME, EPP e LTDA.

### **E se eu for Pessoa Física?**
✅ Sim! Pode usar com CPF.

### **Preciso ter CNPJ?**
🟡 Não é obrigatório, mas é recomendado para taxas melhores.

### **Posso trocar de Sandbox para Produção?**
✅ Sim! Basta gerar nova API Key em Produção e atualizar.

### **Os dados de teste funcionam em Produção?**
❌ Não! Dados de teste só funcionam no Sandbox.

### **Posso cancelar a integração?**
✅ Sim, a qualquer momento. Basta remover a API Key.

---

## 🎯 PRÓXIMOS PASSOS

Depois de configurar:

1. **✅ Teste no Sandbox primeiro**
   - Faça pedidos de teste
   - Veja pagamentos sendo confirmados
   - Entenda o fluxo

2. **✅ Envie documentos (Produção)**
   - Documento pessoal
   - Comprovante de endereço
   - Dados bancários

3. **✅ Aguarde aprovação**
   - Asaas analisa documentos
   - Geralmente 1-3 dias

4. **✅ Mude para Produção**
   - Gere API Key de Produção
   - Atualize no sistema
   - Comece a vender!

---

## 📞 SUPORTE

### **Asaas:**
- 📧 suporte@asaas.com
- 📞 (11) 4950-2656
- 💬 Chat no site
- 📚 docs.asaas.com

### **Sistema:**
- Me chame se precisar de ajuda com a integração
- Posso ajudar com testes
- Troubleshooting de problemas

---

## ✅ CHECKLIST DE CONFIGURAÇÃO

```
[ ] Criar conta Asaas
[ ] Verificar e-mail
[ ] Gerar API Key (Sandbox)
[ ] Configurar no painel
[ ] Testar conexão
[ ] Fazer pedido de teste
[ ] Confirmar pagamento de teste
[ ] Enviar documentos (Produção)
[ ] Aguardar aprovação
[ ] Gerar API Key (Produção)
[ ] Trocar para Produção
[ ] Fazer primeiro pedido real
[ ] Receber primeiro pagamento! 🎉
```

---

## 🎉 ESTÁ PRONTO!

**Acesse agora:**
```
https://marmitaria-gi.eliseus.com.br/painel/asaas-setup
```

**Configure em 3 minutos e comece a receber pagamentos online!** 🚀

---

**Desenvolvido com ❤️ por Claude Code**
**DeliveryPro - Sistema Multi-Tenant de Delivery**
