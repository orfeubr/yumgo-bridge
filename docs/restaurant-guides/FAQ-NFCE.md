# ❓ FAQ - Perguntas Frequentes sobre NFC-e

**Plataforma YumGo** | Atualizado: Março/2026

---

## 📋 Índice por Categoria

1. [Geral](#geral)
2. [Certificado Digital](#certificado-digital)
3. [Credenciamento e CSC](#credenciamento-e-csc)
4. [Emissão e Operação](#emissão-e-operação)
5. [Fiscal e Tributário](#fiscal-e-tributário)
6. [Problemas e Erros](#problemas-e-erros)
7. [Custos e Manutenção](#custos-e-manutenção)

---

## 🔷 Geral

### 1. O que é NFC-e?

**R:** NFC-e (Nota Fiscal do Consumidor Eletrônica) é o documento fiscal eletrônico que substituiu o cupom fiscal para vendas ao consumidor final. É 100% digital e emitida direto na SEFAZ.

### 2. Qual a diferença entre NFe e NFC-e?

**R:**
- **NFe:** Vendas entre empresas (B2B), destinatário com CNPJ
- **NFC-e:** Vendas ao consumidor (B2C), destinatário com CPF ou sem identificação

### 3. Preciso emitir NFC-e no meu restaurante?

**R:** SIM, se você:
- Faz delivery
- Vende no balcão/salão
- Aceita cartão de crédito/débito
- Tem faturamento acima do limite do seu estado (geralmente R$ 180 mil/ano)

### 4. É difícil configurar?

**R:** NÃO! Com o guia da YumGo, você configura em 2-3 semanas (incluindo tempo de aprovação da SEFAZ). O sistema faz tudo automaticamente depois.

### 5. Preciso de contador?

**R:** **Altamente recomendado!** O contador te orienta sobre regime tributário, classificação fiscal (NCM/CFOP) e obrigações acessórias. Evita multas e problemas.

---

## 🔐 Certificado Digital

### 6. Qual tipo de certificado preciso?

**R:** **e-CNPJ A1** (arquivo .pfx). NÃO compre A3 (token/cartão), pois é menos prático para sistemas.

### 7. Onde comprar certificado digital?

**R:** Autoridades Certificadoras credenciadas:
- Serasa Experian
- Certisign
- Valid
- Soluti
- AC Digital

### 8. Quanto custa o certificado?

**R:** Entre R$ 150-300/ano. Preço varia por certificadora e promoções.

### 9. Quanto tempo demora para receber o certificado?

**R:** 3-7 dias após validação presencial ou por videoconferência.

### 10. Preciso renovar o certificado?

**R:** SIM! **A cada 1 ano.** O sistema YumGo te avisa 60 dias antes do vencimento.

### 11. Posso usar o certificado de outro CNPJ?

**R:** **NÃO!** Cada empresa deve ter seu próprio certificado (do CNPJ dela). É crime usar certificado de terceiros.

### 12. Esqueci a senha do certificado. E agora?

**R:** **Não há como recuperar.** Você precisará comprar um novo certificado.

### 13. Certificado A1 vs A3 - qual a diferença?

**R:**
- **A1:** Arquivo .pfx, válido 1 ano, mais prático para sistemas ✅
- **A3:** Token/cartão físico, válido 3 anos, mais seguro mas menos prático

---

## 🏛️ Credenciamento e CSC

### 14. O que é CSC?

**R:** CSC (Código de Segurança do Contribuinte) é um código gerado pela SEFAZ usado para assinar digitalmente a NFC-e. É composto por um **ID** (número) e um **Token** (código alfanumérico).

### 15. Como obter o CSC?

**R:** Após o credenciamento ser aprovado, acesse o portal da SEFAZ e gere o CSC no menu "NFC-e > Gerar CSC".

### 16. Quanto tempo demora o credenciamento?

**R:** **1-3 dias úteis** na maioria dos estados. Alguns estados podem demorar até 5 dias.

### 17. O credenciamento tem custo?

**R:** **NÃO! É 100% GRÁTIS.**

### 18. Posso credenciar mais de uma vez?

**R:** Sim, mas não é necessário. O credenciamento é **vitalício**. Só precisa credenciar uma vez.

### 19. Perdi meu CSC. E agora?

**R:** Acesse o portal da SEFAZ e **gere um novo CSC**. O antigo será invalidado automaticamente.

### 20. Posso ter mais de um CSC ativo?

**R:** Depende do estado. Alguns permitem até 3 CSCs ativos simultaneamente. Consulte a SEFAZ do seu estado.

---

## 📝 Emissão e Operação

### 21. Preciso imprimir a NFC-e?

**R:** **NÃO!** A impressão é **opcional**. A nota é válida apenas no formato eletrônico (XML).

### 22. Como o cliente recebe a nota?

**R:** Por **email**, **SMS** ou **QR Code** (cliente escaneia e visualiza).

### 23. Posso emitir NFC-e sem CPF do cliente?

**R:** **SIM!** É permitido emitir sem identificação do destinatário.

### 24. E se o cliente pedir nota com CNPJ?

**R:** Para CNPJ, você deve emitir **NFe** (não NFC-e). O processo é diferente. Consulte seu contador.

### 25. Como funciona a emissão automática?

**R:** Quando um pedido é marcado como "pago", o sistema YumGo emite a NFC-e automaticamente em segundo plano. Você não precisa fazer nada!

### 26. Posso cancelar uma NFC-e?

**R:** **SIM!** Prazo: **30 minutos** após emissão (alguns estados permitem até 24h). Menu: Notas Fiscais > Ações > Cancelar.

### 27. O que acontece se eu cancelar o pedido?

**R:** Se a nota já foi emitida, você deve **cancelá-la manualmente**. O sistema não cancela automaticamente para evitar problemas fiscais.

### 28. Posso editar uma NFC-e após emitida?

**R:** **NÃO!** Notas autorizadas não podem ser editadas. Você deve cancelar e emitir nova.

### 29. Como funciona a numeração das notas?

**R:** O sistema incrementa automaticamente (1, 2, 3, 4...). Você configura o **número inicial** apenas uma vez.

### 30. Pulei um número na numeração. E agora?

**R:** Você deve **inutilizar** o número pulado no portal da SEFAZ. Consulte seu contador.

---

## 💼 Fiscal e Tributário

### 31. O que é NCM?

**R:** NCM (Nomenclatura Comum do Mercosul) é um código de 8 dígitos que classifica o produto. Exemplo: 19059090 = alimentos preparados.

### 32. O que é CFOP?

**R:** CFOP (Código Fiscal de Operações e Prestações) indica o tipo de operação. Exemplo: 5102 = venda dentro do estado.

### 33. Como saber o NCM do meu produto?

**R:** Use a **classificação automática com IA** do YumGo (botão "🤖 Classificar com IA" no cadastro de produtos) ou consulte seu contador.

### 34. Qual CFOP usar para delivery?

**R:** Geralmente **5102** (venda dentro do estado). Consulte seu contador para confirmar.

### 35. Preciso calcular impostos manualmente?

**R:** **NÃO!** O sistema calcula automaticamente baseado no regime tributário configurado.

### 36. Qual regime tributário devo escolher?

**R:** Depende do seu enquadramento na Receita Federal:
- **Simples Nacional** (maioria dos restaurantes)
- **Lucro Presumido**
- **Lucro Real**

**⚠️ Consulte seu contador!**

### 37. O que é CRT?

**R:** CRT (Código de Regime Tributário) indica seu regime:
- **1** = Simples Nacional
- **3** = Lucro Presumido/Real

### 38. Preciso pagar mais impostos emitindo NFC-e?

**R:** **NÃO!** A NFC-e apenas **documenta** a venda que você já faz. Os impostos são os mesmos.

---

## 🚨 Problemas e Erros

### 39. A SEFAZ está fora do ar. E agora?

**R:** O sistema tentará reenviar automaticamente. Se persistir, você pode emitir em **contingência offline** (consulte contador) ou aguardar normalização.

### 40. Recebi erro "Certificado inválido". O que fazer?

**R:** Verifique:
1. Certificado não está vencido
2. Senha está correta
3. Fez upload do arquivo correto (.pfx)

### 41. Recebi erro "CSC inválido". O que fazer?

**R:**
1. Verifique se CSC ID e Token estão corretos
2. Gere novo CSC na SEFAZ
3. Atualize no sistema YumGo

### 42. A numeração ficou desorganizada. Como corrigir?

**R:** **NÃO tente corrigir sozinho!** Consulte seu contador. Pode ser necessário inutilizar números.

### 43. Emiti uma nota errada. E agora?

**R:**
1. **Se ainda não passou 30 minutos:** Cancele a nota
2. **Se já passou 30 minutos:** Consulte contador (pode precisar emitir nota de devolução)

### 44. Cliente não recebeu o email da nota.

**R:** Verifique:
1. Email cadastrado está correto
2. Nota foi emitida com sucesso (status "Autorizada")
3. Email não caiu no spam
4. Reenvie manualmente: Menu > Notas Fiscais > Reenviar

---

## 💰 Custos e Manutenção

### 45. Quanto custa emitir NFC-e pela YumGo?

**R:** **R$ 0/mês!** Está **INCLUSO** no seu plano YumGo. Sem custo adicional.

### 46. Tenho que pagar por nota emitida?

**R:** **NÃO!** Emissões ilimitadas sem custo adicional.

### 47. Quais são os custos totais?

**R:**
- **Certificado A1:** R$ 150-300/ano (~R$ 20/mês)
- **Credenciamento:** GRÁTIS
- **CSC:** GRÁTIS
- **Sistema YumGo:** INCLUSO
- **Contador (opcional):** R$ 300-800/mês

### 48. Preciso contratar serviço adicional?

**R:** **NÃO!** Tudo já está incluso na YumGo.

### 49. Quanto tempo de suporte eu tenho?

**R:** Suporte ilimitado via email e WhatsApp durante todo o plano YumGo.

### 50. Onde ficam armazenados os XMLs?

**R:** No sistema YumGo (armazenamento seguro e automático). Você pode fazer download a qualquer momento.

---

## 📞 Ainda tem dúvidas?

### Suporte Técnico YumGo:
- **Email:** suporte@yumgo.com.br
- **WhatsApp:** (11) 99999-9999
- **Horário:** Segunda a Sexta, 9h às 18h

### Dúvidas Fiscais:
- Consulte seu contador
- Portal da SEFAZ do seu estado

---

**📌 Última atualização:** Março/2026 | 50 perguntas respondidas
