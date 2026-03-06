# 🔧 Troubleshooting - Soluções para Erros Comuns de NFC-e

**Plataforma YumGo** | Guia Prático de Resolução de Problemas

---

## 📋 Índice Rápido

1. [Erros de Certificado](#erros-de-certificado)
2. [Erros de CSC](#erros-de-csc)
3. [Erros de Numeração](#erros-de-numeração)
4. [Erros de Dados Cadastrais](#erros-de-dados-cadastrais)
5. [Erros de Produtos (NCM/CFOP)](#erros-de-produtos)
6. [Erros de Comunicação com SEFAZ](#erros-de-comunicação)
7. [Erros de Validação de XML](#erros-de-validação)
8. [Problemas de Performance](#problemas-de-performance)

---

## 🔐 Erros de Certificado

### ❌ Erro 214: "Certificado Transmissor inválido"

**Causa:** Certificado vencido, senha errada ou arquivo corrompido

**Solução:**
1. Verificar validade do certificado:
   - Abrir arquivo .pfx no computador
   - Verificar data de validade
   - Se vencido: comprar novo certificado

2. Verificar senha:
   - Tentar abrir .pfx com a senha configurada
   - Se não abrir: senha está errada
   - Corrigir no sistema: Configurações > Configuração Fiscal

3. Re-upload do certificado:
   - Fazer upload novamente do arquivo .pfx
   - Inserir senha correta
   - Salvar configurações

---

### ❌ Erro 280: "Certificado não está na cadeia de confiança"

**Causa:** Certificado intermediário ou raiz não foi enviado

**Solução:**
1. Verificar se o .pfx contém a cadeia completa
2. Re-exportar certificado incluindo toda a cadeia
3. Fazer novo upload no sistema

**⚠️ Nota:** O sistema YumGo faz isso automaticamente, mas se persistir, entre em contato com o suporte.

---

### ❌ Erro 286: "Assinatura difere do calculado"

**Causa:** Certificado incompatível ou corrompido

**Solução:**
1. Baixar certificado novamente da autoridade certificadora
2. Verificar se é **e-CNPJ A1** (não A3)
3. Fazer novo upload
4. Se persistir: solicitar reemissão do certificado

---

## 🔑 Erros de CSC

### ❌ Erro 539: "CSC inválido para o emitente"

**Causa:** CSC ID ou Token incorretos, ou CSC inativo

**Solução:**
1. Acessar portal da SEFAZ
2. Menu: NFC-e > Consultar CSC
3. Verificar se CSC está ativo
4. Se inativo: gerar novo CSC
5. Atualizar no sistema YumGo:
   - Configurações > Configuração Fiscal
   - Inserir novo CSC ID e Token
   - Salvar

**⚠️ IMPORTANTE:** Ao gerar novo CSC, o antigo é invalidado. Atualize imediatamente no sistema!

---

### ❌ Erro 801: "CSC expirado"

**Causa:** CSC foi revogado ou expirou (raro)

**Solução:**
1. Gerar novo CSC na SEFAZ
2. Atualizar no sistema
3. Testar emissão

---

## 🔢 Erros de Numeração

### ❌ Erro 204: "Duplicidade de NFC-e"

**Causa:** Mesma série e número já foram utilizados

**Solução:**
1. **NÃO tente emitir novamente!**
2. Verificar no sistema qual foi o último número emitido:
   - Menu: Notas Fiscais
   - Ordenar por número decrescente
   - Anotar último número

3. Atualizar numeração:
   - Configurações > Configuração Fiscal
   - Campo "Número Atual": último número + 1
   - Salvar

4. Tentar emitir novamente

---

### ❌ Erro 225: "Falha no Schema XML - nNF"

**Causa:** Número da nota inválido (0, negativo ou muito grande)

**Solução:**
1. Verificar campo "Número Atual" nas configurações
2. Deve ser número positivo entre 1 e 999.999.999
3. Corrigir se necessário
4. Salvar e tentar novamente

---

### ❌ Problema: Numeração pulou/ficou com buraco

**Causa:** Falha na emissão e sistema incrementou incorretamente

**Solução:**
1. **Identificar números faltantes:**
   - Ex: emitiu 1, 2, 3, 5 (faltou o 4)

2. **Inutilizar números:**
   - Acessar portal da SEFAZ
   - Menu: NFC-e > Inutilizar Numeração
   - Informar série e números (ex: 4 a 4)
   - Confirmar

3. **⚠️ Consulte contador antes de inutilizar!**

---

## 📋 Erros de Dados Cadastrais

### ❌ Erro 226: "Código da UF do emitente divergente"

**Causa:** UF do CNPJ diferente da UF da SEFAZ

**Solução:**
1. Verificar UF correta do CNPJ no Cartão CNPJ
2. Verificar se está credenciado na SEFAZ certa:
   - CNPJ de SP → credenciar na SEFAZ-SP
   - CNPJ do RJ → credenciar na SEFAZ-RJ

3. Se credenciou na SEFAZ errada:
   - Solicitar credenciamento na SEFAZ correta

---

### ❌ Erro 227: "CNPJ do emitente inválido"

**Causa:** CNPJ incorreto ou com formatação errada

**Solução:**
1. Verificar CNPJ no Cartão CNPJ
2. Corrigir em: Configurações > Configuração Fiscal
3. Formato correto: apenas números (00000000000000)
4. Salvar e tentar novamente

---

### ❌ Erro 228: "IE do emitente inválida"

**Causa:** Inscrição Estadual incorreta ou não cadastrada na SEFAZ

**Solução:**
1. Verificar IE no portal da SEFAZ
2. Menu: Consulta de Contribuinte
3. Verificar se IE está ativa
4. Corrigir no sistema se necessário
5. Se IE inativa: regularizar na SEFAZ primeiro

---

### ❌ Erro 520: "CEP do emitente inválido"

**Causa:** CEP incorreto ou com formatação errada

**Solução:**
1. Verificar CEP correto do endereço fiscal
2. Corrigir em: Configurações > Configuração Fiscal > Endereço Fiscal
3. Formato: apenas números (00000000)
4. Salvar

---

## 🏷️ Erros de Produtos

### ❌ Erro 518: "NCM inexistente"

**Causa:** NCM inválido ou desatualizado

**Solução:**
1. Verificar NCM correto na tabela oficial:
   - https://portalunico.siscomex.gov.br/

2. Corrigir produto:
   - Menu: Produtos > Editar
   - Campo "NCM": corrigir
   - Ou usar "🤖 Classificar com IA"
   - Salvar

3. **NCMs comuns para restaurantes:**
   - 19059090 - Alimentos preparados
   - 22021000 - Águas
   - 22029900 - Refrigerantes
   - 22030000 - Cervejas

---

### ❌ Erro 519: "CFOP inválido"

**Causa:** CFOP não permitido para NFC-e ou incorreto

**Solução:**
1. Usar CFOP correto:
   - **5102** - Venda dentro do estado (padrão)
   - **6102** - Venda fora do estado (raro)

2. Corrigir produto:
   - Menu: Produtos > Editar
   - Campo "CFOP": 5102
   - Salvar

3. **⚠️ Consulte contador para casos especiais**

---

### ❌ Erro 540: "CEST inválido"

**Causa:** CEST obrigatório não informado ou inválido

**Solução:**
1. Verificar se produto exige CEST:
   - Consultar tabela CEST da SEFAZ

2. Se obrigatório:
   - Informar CEST correto no cadastro do produto

3. Se não obrigatório:
   - Deixar campo vazio

4. **⚠️ Consulte contador para classificação CEST**

---

## 🌐 Erros de Comunicação

### ❌ Erro 503: "Serviço indisponível"

**Causa:** SEFAZ fora do ar ou em manutenção

**Solução:**
1. Aguardar 5-10 minutos
2. Verificar status da SEFAZ:
   - https://www.nfe.fazenda.gov.br/portal/disponibilidade.aspx

3. Se SEFAZ OK mas erro persiste:
   - Verificar conexão com internet
   - Tentar novamente

4. Se persistir por mais de 1 hora:
   - Consultar contador sobre emissão em contingência

---

### ❌ Erro 108: "Serviço paralisado momentaneamente"

**Causa:** SEFAZ em manutenção programada

**Solução:**
1. Aguardar normalização (geralmente 30 min - 2h)
2. Acompanhar avisos no portal da SEFAZ
3. Sistema YumGo tentará reenviar automaticamente

---

### ❌ Timeout na emissão

**Causa:** Resposta da SEFAZ demorou muito

**Solução:**
1. Aguardar 2-3 minutos
2. Verificar se nota foi autorizada:
   - Menu: Notas Fiscais
   - Verificar status

3. Se não autorizada:
   - Tentar emitir novamente

4. **⚠️ NÃO emita múltiplas vezes!** Risco de duplicar numeração.

---

## ✅ Erros de Validação

### ❌ Erro 225: "Falha no Schema XML"

**Causa:** XML malformado ou campo obrigatório faltando

**Solução:**
1. Verificar logs de erro no sistema
2. Identificar campo problemático
3. Corrigir dados (produto, cliente, etc.)
4. Tentar novamente

5. Se persistir: contatar suporte YumGo

---

### ❌ Erro 404: "Uso denegado pela SEFAZ"

**Causa:** Irregularidade cadastral do emitente

**Solução:**
1. **GRAVE!** Consulte contador URGENTEMENTE
2. Possíveis causas:
   - IE suspensa
   - CNPJ com pendências
   - Débitos fiscais

3. Regularizar situação na SEFAZ
4. Pode levar dias/semanas para resolver

---

## ⚡ Problemas de Performance

### 🐌 Emissão está muito lenta

**Causa:** Muitas notas na fila ou SEFAZ lenta

**Solução:**
1. Verificar fila de emissão:
   - Menu: Notas Fiscais > Pendentes

2. Aguardar processamento
3. Sistema processa em ordem
4. Geralmente 1-2 minutos por nota

---

### 🔄 Nota ficou "processando" por muito tempo

**Causa:** Timeout ou erro de comunicação

**Solução:**
1. Aguardar até 5 minutos
2. Recarregar página
3. Verificar status final
4. Se ainda "processando" após 10 minutos:
   - Contatar suporte YumGo

---

## 📞 Quando Contatar Suporte

**Contate Suporte YumGo se:**
- Erro persistir após seguir soluções acima
- Erro não estiver listado neste guia
- Sistema não responder
- Dados foram corrompidos

**Contate Contador se:**
- Dúvidas sobre classificação fiscal
- Irregularidades cadastrais
- Débitos fiscais
- Contingência offline
- Inutilização de numeração

**Contate SEFAZ se:**
- Problemas no portal da SEFAZ
- Credenciamento não aprovado
- CSC não está sendo gerado
- Dúvidas sobre legislação

---

## 📋 Checklist de Diagnóstico

Antes de contatar suporte, verifique:

- [ ] Certificado está válido (não venceu)?
- [ ] CSC está correto e ativo?
- [ ] Dados cadastrais (CNPJ, IE) estão corretos?
- [ ] Produtos têm NCM e CFOP válidos?
- [ ] Numeração está correta (sem duplicatas)?
- [ ] Internet está funcionando?
- [ ] SEFAZ está disponível?
- [ ] Já tentou novamente após 5 minutos?

---

## 🆘 Suporte de Emergência

### Suporte YumGo (24x7):
- **Email:** suporte@yumgo.com.br
- **WhatsApp:** (11) 99999-9999
- **Urgente:** Ligue e informe código do erro

### Informações para fornecer ao suporte:
1. Código do erro (ex: Erro 539)
2. Quando ocorreu
3. O que estava fazendo
4. Prints de tela
5. Número da nota (se aplicável)

---

**📌 Última atualização:** Março/2026
**💡 Dica:** Salve este guia nos favoritos para consulta rápida!
