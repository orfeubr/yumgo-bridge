# 🆕 Novas Configurações Implementadas

**Data**: 22/02/2026

---

## ✅ TUDO QUE FOI ADICIONADO

### 1️⃣ **Informações do Estabelecimento** 🏢

Campos profissionais para CNPJ/CPF e dados fiscais:

- **Razão Social** (Nome legal da empresa)
- **Nome Fantasia** (Nome comercial)
- **CNPJ ou CPF** (Documento fiscal)
- **Inscrição Estadual** (IE)
- **Inscrição Municipal** (IM)
- **Segmento** (Pizzaria, Marmitex, Hamburgueria, etc.)

**Exemplo:**
```
Razão Social: RESTAURANTE GI LTDA
Nome Fantasia: Marmitaria da Gi
CNPJ: 12.345.678/0001-90
IE: 123.456.789
IM: 98765
Segmento: Marmitex
```

---

### 2️⃣ **Endereço Completo** 📍

Endereço detalhado do estabelecimento:

- **Endereço** (Rua/Avenida)
- **Número**
- **Complemento**
- **Bairro**
- **Cidade**
- **Estado** (UF)
- **CEP**
- **Website**

**Exemplo:**
```
Rua das Flores, 123
Apto 45 - Bloco B
Bairro: Centro
Cidade: São Paulo
Estado: SP
CEP: 01234-567
Site: www.marmitariadagi.com.br
```

---

### 3️⃣ **Regiões de Atendimento** 🗺️

#### **Zonas de Entrega Personalizadas**

Configure taxas diferentes por região:

```json
[
  {
    "name": "Centro",
    "fee": 5.00,
    "time": 30
  },
  {
    "name": "Zona Sul",
    "fee": 8.00,
    "time": 45
  },
  {
    "name": "Zona Norte",
    "fee": 10.00,
    "time": 60
  }
]
```

#### **Lista de Bairros Atendidos**

```
Centro, Jardins, Vila Mariana, Pinheiros, Moema,
Itaim Bibi, Brooklin, Vila Olímpia...
```

#### **Raio de Atendimento**

- **Raio em KM**: 10km (configurável)
- **Entrega grátis acima de**: R$ 50,00
- **Pedido mínimo**: R$ 20,00

---

### 4️⃣ **Tempo de Entrega (Min/Max)** ⏱️

Agora com faixa de tempo:

- **Mínimo**: 30 minutos
- **Máximo**: 60 minutos

**Exibido para o cliente:**
```
🕐 Entrega em 30-60 minutos
```

**Ajuste por região:**
```
Centro: 20-30 min
Zona Sul: 30-45 min
Zona Norte: 45-60 min
```

---

### 5️⃣ **Formas de Entrega** 🚚

Três opções configuráveis:

- ✅ **Entregador próprio** (restaurante faz a entrega)
- ⬜ **Cliente busca** (retirada no local)
- ⬜ **Motoboy parceiro** (terceirizado)

**Múltiplas formas simultaneamente!**

---

### 6️⃣ **Pagamento na Entrega** 💵

#### **Opções:**
- ✅ Aceitar pagamento na entrega
- ✅ Dinheiro na entrega
- ✅ Maquininha na entrega (débito/crédito)

#### **Troco:**
- **Troco para quanto?** R$ 50,00, R$ 100,00, etc.

**Exemplo no app:**
```
💵 Pagamento na Entrega
   ○ Dinheiro (Troco para R$ 100)
   ○ Cartão (Maquininha)
```

---

### 7️⃣ **Numeração de Pedidos** 🔢

Configure como seus pedidos são numerados:

- **Prefixo**: PED, ORD, MAR, PIZ, etc.
- **Número inicial**: 1, 100, 1000, etc.
- **Zeros à esquerda**: 4, 6, 8 dígitos
- **Reiniciar diariamente**: Sim/Não

**Exemplos:**
```
PED-000001
PED-000002
...

MAR-2024-001
MAR-2024-002
...

PIZ-100
PIZ-101
```

**Com reinício diário:**
```
22/02: PED-000001, PED-000002
23/02: PED-000001, PED-000002 (reiniciou)
```

---

### 8️⃣ **NFCe - Nota Fiscal Eletrônica** 🧾

Sistema completo de emissão de NFCe:

#### **Configurações:**
- **Habilitar NFCe**: Sim/Não
- **Ambiente**: Homologação ou Produção
- **Certificado A1**: Upload do arquivo .pfx
- **Senha do Certificado**: ••••••
- **Série**: 1 (padrão)
- **Último número**: 0 (auto-incrementa)
- **CSC**: Código de Segurança do Contribuinte
- **ID do CSC**: ID do CSC
- **Regime Tributário**:
  - 1 = Simples Nacional
  - 2 = Simples Nacional - Excesso
  - 3 = Regime Normal
- **Emissão automática**: Sim/Não
- **Informações adicionais**: Texto livre

#### **Fluxo:**
```
1. Cliente faz pedido
2. Sistema gera NFCe automaticamente (se habilitado)
3. Envia para SEFAZ
4. Retorna XML + DANFE (PDF)
5. Envia por e-mail para cliente
6. Imprime na térmica (se configurado)
```

#### **Homologação vs Produção:**
- **Homologação**: Testes (notas não valem)
- **Produção**: Notas oficiais

#### **Onde conseguir:**
- **Certificado A1**: Contador ou e-CNPJ
- **CSC**: Portal SEFAZ do seu estado
- **Série**: Definir com contador

---

## 📊 Comparação: Antes vs Depois

| Recurso | Antes | Depois |
|---------|-------|--------|
| Endereço | 1 campo | 7 campos detalhados |
| Tempo entrega | 1 valor fixo | Min e Max |
| Zonas | Apenas raio | Zonas personalizadas |
| Bairros | ❌ | ✅ Lista completa |
| Pagamento entrega | ❌ | ✅ Dinheiro + Cartão |
| Troco | ❌ | ✅ Configurável |
| Numeração pedidos | Automática | Personalizável |
| NFCe | ❌ | ✅ Completo |
| CNPJ/IE | ❌ | ✅ Campos fiscais |

---

## 🎯 Casos de Uso Reais

### **Caso 1: Pizzaria com zonas**
```
Centro: R$ 5,00 (20-30 min)
Vila Nova: R$ 8,00 (30-45 min)
Periferia: R$ 12,00 (45-60 min)
Grátis acima de R$ 60
```

### **Caso 2: Marmitaria com NFCe**
```
Emissão automática de NFCe
Simples Nacional
Envia por e-mail
Imprime na térmica
```

### **Caso 3: Hamburgueria delivery próprio**
```
Entregador próprio: ✅
Pagamento na entrega: ✅
Dinheiro (Troco R$ 50): ✅
Cartão na maquininha: ✅
```

### **Caso 4: Restaurante com MEI**
```
CNPJ/CPF: CPF do MEI
Sem NFCe (MEI isento)
Pedidos: RES-001, RES-002...
```

---

## 💰 iFood Pago vs Asaas - Análise Detalhada

### **Custos Reais (1000 pedidos/mês, R$ 50 média)**

#### **iFood Pago:**
```
PIX (70% dos pedidos):
  700 × R$ 50 = R$ 35.000
  Taxa 3,99% = R$ 1.396,50

Cartão (30% dos pedidos):
  300 × R$ 50 = R$ 15.000
  Taxa 4,99% = R$ 748,50

TOTAL MÊS: R$ 2.145,00
TOTAL ANO: R$ 25.740,00
```

#### **Asaas:**
```
PIX (70% dos pedidos):
  700 pedidos × R$ 0,99 = R$ 693,00

Cartão (30% dos pedidos):
  R$ 15.000 × 2,99% = R$ 448,50
  300 pedidos × R$ 0,49 = R$ 147,00
  Subtotal cartão: R$ 595,50

TOTAL MÊS: R$ 1.288,50
TOTAL ANO: R$ 15.462,00
```

### **💰 ECONOMIA ANUAL: R$ 10.278,00!**

### **Recursos:**
| Recurso | iFood Pago | Asaas |
|---------|-----------|-------|
| Split automático | ❌ | ✅ |
| Sub-contas | ❌ | ✅ |
| Independência | ❌ | ✅ |
| Antecipação | D+30 | D+1 (1,99%) |
| Suporte | Limitado | Dedicado |

### **🏆 VEREDICTO: ASAAS É 45% MAIS BARATO!**

---

## 🛠️ Como Configurar (Passo a Passo)

### **1. Informações do Estabelecimento**
1. Painel → Configurações
2. Aba "Contato"
3. Preencher todos os campos
4. Salvar

### **2. Zonas de Entrega**
1. Aba "Delivery"
2. Adicionar zonas:
   ```
   Nome: Centro
   Taxa: R$ 5,00
   Tempo: 30 min
   ```
3. Listar bairros atendidos
4. Definir raio em KM
5. Salvar

### **3. NFCe (opcional)**
1. Obter certificado A1 com contador
2. Obter CSC no portal SEFAZ
3. Aba "NFCe" (nova)
4. Upload do certificado
5. Preencher CSC e ID
6. Testar em homologação
7. Ativar produção
8. Salvar

### **4. Numeração de Pedidos**
1. Aba "Pedidos"
2. Definir prefixo (ex: MAR)
3. Número inicial (ex: 1)
4. Zeros à esquerda (ex: 6)
5. Resultado: MAR-000001
6. Salvar

---

## 📝 Próximas Funcionalidades

- [ ] Mapa interativo de zonas
- [ ] Integração com Correios (cálculo frete por CEP)
- [ ] NFe (Nota Fiscal de Serviço)
- [ ] Nota Fiscal Paulista automática
- [ ] Exportação XML para contabilidade
- [ ] Dashboard com mapa de entregas
- [ ] Integração com Google Maps (raio real)

---

## 🎓 Glossário Fiscal

**CNPJ**: Cadastro Nacional de Pessoa Jurídica
**CPF**: Cadastro de Pessoa Física (MEI usa CPF)
**IE**: Inscrição Estadual
**IM**: Inscrição Municipal
**NFCe**: Nota Fiscal de Consumidor Eletrônica
**CSC**: Código de Segurança do Contribuinte
**DANFE**: Documento Auxiliar da Nota Fiscal
**SEFAZ**: Secretaria da Fazenda
**Certificado A1**: Certificado digital para NFe
**Simples Nacional**: Regime tributário simplificado

---

**✅ SISTEMA COMPLETO E PROFISSIONAL PARA DELIVERY!**

**Todas as funcionalidades de um sistema premium!** 🚀

**Desenvolvido para DeliveryPro**
