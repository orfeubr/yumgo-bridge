# 📄 Guia NFC-e - São Paulo (SP)

**Estado:** São Paulo
**SEFAZ:** Secretaria da Fazenda e Planejamento do Estado de São Paulo
**Atualizado:** Março/2026

---

## 🎯 Resumo Rápido

| Item | Informação |
|------|------------|
| **Portal SEFAZ** | https://www.fazenda.sp.gov.br/nfce/ |
| **Prazo credenciamento** | 1-3 dias úteis |
| **Obrigatoriedade** | Faturamento > R$ 180 mil/ano |
| **Código UF** | 35 |
| **Série padrão** | 1 |
| **Contingência** | SVC (SEFAZ Virtual Contingência) |

---

## 📋 Passo a Passo - São Paulo

### **1. Comprar Certificado Digital** (3-7 dias)

Autoridades Certificadoras credenciadas em SP:
- **Serasa Experian:** https://certificadodigital.serasa.com.br
- **Certisign:** https://www.certisign.com.br
- **Valid:** https://www.validcertificadora.com.br
- **Soluti:** https://www.soluti.com.br

**Tipo necessário:** e-CNPJ A1
**Preço médio:** R$ 200-300
**Validade:** 1 ano

---

### **2. Credenciar na SEFAZ-SP** (2-3 dias)

#### Passo 2.1: Acessar Portal

URL: https://www.fazenda.sp.gov.br/nfce/

#### Passo 2.2: Fazer Login

- Instale o certificado digital no navegador
- Acesse o portal
- Clique em "Acesso com Certificado Digital"
- Selecione seu certificado

#### Passo 2.3: Solicitar Credenciamento

**Menu:** Credenciamento > NFC-e > Solicitar Credenciamento

**Informações necessárias:**
- CNPJ
- Inscrição Estadual (IE)
- Razão Social
- Endereço fiscal
- Email de contato
- Telefone

**Documentos para anexar:**
- Comprovante de Inscrição Estadual
- Cartão CNPJ atualizado
- Contrato social (se solicitado)

#### Passo 2.4: Confirmar Dados

- Revise todas as informações
- Confirme regime tributário
- Assine digitalmente com certificado

#### Passo 2.5: Aguardar Aprovação

- **Prazo:** 1-3 dias úteis
- **Notificação:** Email cadastrado
- **Consulta:** Portal > Status do Credenciamento

---

### **3. Gerar CSC** (Imediato após aprovação)

#### Passo 3.1: Acessar Menu CSC

**Menu:** NFC-e > Gerenciar CSC

#### Passo 3.2: Gerar Novo CSC

- Clique em "Gerar CSC"
- Sistema gera automaticamente
- **NÃO FECHE A TELA!**

#### Passo 3.3: Anotar CSC ID e Token

Exemplo:
```
CSC ID: 1
CSC Token: A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0
```

**⚠️ IMPORTANTE:**
- Guarde em local seguro
- Não compartilhe com terceiros
- Anote fisicamente (papel) como backup
- Se perder, gere novo CSC

#### Passo 3.4: Testar CSC

- Clique em "Testar CSC"
- Sistema valida conectividade
- Status deve ser: **"Ativo"**

---

### **4. Configurar Ambiente de Homologação** (SP)

#### URLs da SEFAZ-SP:

**Homologação:**
```
Autorização: https://homologacao.nfce.fazenda.sp.gov.br/ws/nfeautorizacao.asmx
Consulta: https://homologacao.nfce.fazenda.sp.gov.br/ws/nfeconsulta2.asmx
Inutilização: https://homologacao.nfce.fazenda.sp.gov.br/ws/nfeinutilizacao2.asmx
```

**Produção:**
```
Autorização: https://nfce.fazenda.sp.gov.br/ws/nfeautorizacao.asmx
Consulta: https://nfce.fazenda.sp.gov.br/ws/nfeconsulta2.asmx
Inutilização: https://nfce.fazenda.sp.gov.br/ws/nfeinutilizacao2.asmx
```

**Obs:** O sistema YumGo já tem essas URLs configuradas automaticamente!

---

## 📋 Particularidades de São Paulo

### 1. Obrigatoriedade

**Em SP, é obrigatório para:**
- Estabelecimentos com faturamento > R$ 180 mil/ano
- Delivery e vendas presenciais
- Vendas com cartão

### 2. Regime Tributário Específico

**Simples Nacional:**
- CRT: 1
- CSOSN: 102 (maioria dos casos)

**Lucro Presumido/Real:**
- CRT: 3
- CST: 00, 10, 20, etc. (conforme enquadramento)

**⚠️ Consulte seu contador!**

### 3. NCM para Alimentos

Principais NCMs para restaurantes em SP:
- **19059090** - Alimentos preparados (pizza, hambúrguer, marmita)
- **22021000** - Águas minerais
- **22029900** - Refrigerantes e sucos
- **22030000** - Cervejas e bebidas alcoólicas
- **21050000** - Sorvetes

### 4. CFOP Padrão

- **5102** - Venda de mercadoria adquirida ou recebida de terceiros (dentro do estado)
- **6102** - Venda para fora do estado (raramente usado em restaurante)

### 5. Alíquota de ICMS

**São Paulo:**
- Alimentos: 12% (maioria)
- Bebidas alcoólicas: 18%
- Consulte seu contador para casos específicos

---

## 🧪 Testando em Homologação (SP)

### Dados para Teste

**Ambiente homologação SP aceita:**
- CNPJs de teste (fornecidos pela SEFAZ)
- Seu CNPJ real (recomendado)
- Valores fictícios

### Cenários Obrigatórios de Teste

1. **Venda Simples**
   - 1 produto
   - Pagamento PIX
   - Sem CPF do cliente

2. **Venda com CPF**
   - 2-3 produtos
   - Pagamento cartão
   - Com CPF do cliente

3. **Venda com Múltiplos Itens**
   - 5+ produtos diferentes
   - Diferentes NCMs
   - Mix de alimentos e bebidas

4. **Cancelamento**
   - Emitir nota
   - Cancelar em até 30 minutos

5. **Inutilização**
   - Simular falha na numeração
   - Inutilizar número pulado

### Validação de Teste

✅ **Aprovado se:**
- Chave de acesso gerada (44 dígitos)
- Status: "Autorizada"
- QR Code funcional
- XML válido no validador da SEFAZ

---

## 🚨 Erros Comuns em SP

### Erro 214: "Certificado Transmissor inválido"

**Causa:** Certificado vencido ou senha errada
**Solução:** Verificar validade e senha do certificado

### Erro 539: "CSC inválido"

**Causa:** CSC ID ou Token incorretos
**Solução:** Gerar novo CSC e reconfigurar

### Erro 204: "Duplicidade de NFC-e"

**Causa:** Mesma numeração emitida 2x
**Solução:** Incrementar número da nota

### Erro 226: "Código da UF do emitente divergente"

**Causa:** UF errada no XML
**Solução:** Verificar se código UF = 35 (São Paulo)

---

## 📞 Contatos SEFAZ-SP

**Portal da Fazenda SP:**
- Site: https://www.fazenda.sp.gov.br
- Central de Atendimento: 0800 170 110
- Email: atendimento@fazenda.sp.gov.br

**Plantão Fiscal (Dúvidas Técnicas):**
- Telefone: (11) 3243-3300
- Horário: Segunda a Sexta, 8h às 17h

**Portal NFC-e:**
- Site: https://www.fazenda.sp.gov.br/nfce/
- Consulta Nota: https://www.fazenda.sp.gov.br/nfce/consultaQRCode

---

## 📚 Documentação Oficial

- [Manual de Integração NFC-e SP](https://www.fazenda.sp.gov.br/nfe/manuais/ManualIntegracaoContribuinte.pdf)
- [Perguntas Frequentes SEFAZ-SP](https://www.fazenda.sp.gov.br/nfe/perguntas_frequentes.asp)
- [Códigos de Erro](https://www.fazenda.sp.gov.br/nfe/erros.asp)

---

## ✅ Checklist Final

Antes de ativar produção em SP:

- [ ] Certificado A1 válido e configurado
- [ ] Credenciamento aprovado pela SEFAZ-SP
- [ ] CSC ativo e testado
- [ ] 10-20 notas em homologação testadas com sucesso
- [ ] NCMs e CFOPs corretos
- [ ] Regime tributário validado
- [ ] Numeração das notas controlada
- [ ] Backup dos XMLs configurado
- [ ] Contador orientando

---

**📌 Última atualização:** Março/2026 | Específico para São Paulo
