# 🎯 Próximos Passos - Emitir NFC-e de Verdade

## ✅ O QUE JÁ FIZEMOS HOJE:

- ✅ Instalado NFePHP
- ✅ Configurado tenant para homologação
- ✅ Criado comando de teste `php artisan nfce:test`
- ✅ Identificamos TODOS os campos necessários
- ✅ Geração de certificado de teste automática

## 📋 O QUE FALTA PARA EMITIR NFC-e:

### **1. Dados Fiscais Completos**

Configure no painel Filament (Configurações Fiscais):

```
✅ CNPJ: 11111111000111 (já configurado)
❌ Razão Social: "MARMITARIA DA GI EIRELI"
❌ Inscrição Estadual: "123456789"
❌ Endereço Fiscal Completo:
   - Rua, Número, Complemento
   - Bairro, Cidade, Estado, CEP
   - Código IBGE da cidade
```

### **2. Certificado Digital**

**Para Produção (obrigatório):**
- Comprar Certificado A1: R$ 150-250/ano
- Sites: Serasa, Certisign, Valid, Soluti

**Para Homologação (teste):**
- O sistema JÁ gera automático! ✅

### **3. CSC (Código de Segurança)**

- Obter no portal da SEFAZ do seu estado
- Grátis
- Necessário para assinar NFC-e

### **4. Configurar Produtos**

Cada produto precisa:
- ✅ NCM (código fiscal) - **Tributa AI já faz!** ✨
- ✅ CFOP (operação fiscal)
- Valor unitário
- Quantidade

---

## 🚀 ROTEIRO COMPLETO:

### **FASE 1: Documentos e Cadastros** (1-2 semanas)

1. **Certificado Digital**
   - Comprar em: https://www.certisign.com.br
   - Escolher: A1 (arquivo) ou A3 (cartão/token)
   - Custo: R$ 150-400/ano

2. **CSC na SEFAZ**
   - Acessar portal SEFAZ do seu estado
   - Gerar CSC para NFC-e
   - Anotar: CSC ID e CSC Token

3. **Inscrição Estadual**
   - Se não tem, solicitar na SEFAZ
   - Processo varia por estado

### **FASE 2: Configurar no Sistema** (1 dia)

```bash
# Acessar painel do restaurante
https://marmitariadagi.yumgo.com.br/painel

# Ir em: Configurações → Fiscal

# Preencher TODOS os campos:
- Certificado A1 (upload do arquivo .pfx)
- Senha do certificado
- CNPJ
- Razão Social
- Inscrição Estadual
- CSC ID
- CSC Token
- Endereço fiscal completo
- Código IBGE da cidade
```

### **FASE 3: Testar em Homologação** (1 dia)

```bash
# Configurar ambiente de homologação
php artisan tinker

$tenant = \App\Models\Tenant::first();
$tenant->update(['nfce_environment' => 'homologacao']);

# Fazer pedido de teste
# Sistema emite NFC-e automaticamente

# Validar XML gerado
```

### **FASE 4: Ir para Produção** (após testes OK)

```bash
# Mudar para produção
php artisan tinker

$tenant = \App\Models\Tenant::first();
$tenant->update(['nfce_environment' => 'producao']);

# Pronto! Notas fiscais reais serão emitidas automaticamente
```

---

## 💰 CUSTOS

| Item | Valor | Recorrência |
|------|-------|-------------|
| Certificado A1 | R$ 150-250 | Anual |
| CSC (SEFAZ) | R$ 0 | Grátis |
| NFePHP | R$ 0 | Grátis |
| Tributa AI (IA) | R$ 0,20/consulta | Por uso |

**Total inicial:** ~R$ 200 (certificado)
**Mensal:** ~R$ 20-50 (Tributa AI)

---

## 🎯 DECISÃO IMPORTANTE:

### **Você TEM empresa legalizada (CNPJ, IE)?**

✅ **SIM** → Siga o roteiro acima

❌ **NÃO** → Duas opções:

**Opção A: Não emitir nota fiscal**
- Válido para MEI com faturamento <R$ 81k/ano
- Clientes não podem exigir nota
- Simplifica operação

**Opção B: Legalizar empresa**
- Abrir MEI ou LTDA
- Obter CNPJ e Inscrição Estadual
- Depois seguir roteiro acima

---

## ⏱️ TEMPO ESTIMADO:

| Tarefa | Tempo |
|--------|-------|
| Comprar certificado | 1-2 dias |
| Obter CSC | 1 dia |
| Configurar sistema | 2 horas |
| Testes homologação | 1 dia |
| **TOTAL** | **1 semana** |

---

## 🆘 AJUDA

**Estou aqui para:**
- ✅ Configurar sistema
- ✅ Debugar erros
- ✅ Ajustar código
- ✅ Explicar processo

**NÃO posso:**
- ❌ Comprar certificado por você
- ❌ Obter CSC (precisa acessar SEFAZ)
- ❌ Legalizar empresa

---

## 📞 PRÓXIMA SESSÃO:

**Quando tiver:**
1. Certificado digital (.pfx)
2. CSC (ID + Token)
3. Dados fiscais completos

**Me chame e eu:**
- Configuro tudo no sistema
- Testo emissão
- Valido XML
- Coloco em produção

---

**IMPORTANTE:** Emitir NFC-e SEM legalização é **crime fiscal**! Só faça se tiver empresa regularizada.

---

**Desenvolvido com ❤️ por Claude Code** 🤖
**Data:** 03/03/2026
