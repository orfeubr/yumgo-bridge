# 🖨️ Guia Completo de Impressoras - DeliveryPro

**Data:** 22/02/2026
**Status:** 📖 GUIA COMPLETO

---

## 🎯 TIPOS DE IMPRESSÃO SUPORTADOS

### 1. **Impressora Térmica USB/Rede** ⭐ RECOMENDADO
- Modelos: Elgin, Daruma, Epson, Bematech
- Conexão: USB ou Rede (Ethernet/WiFi)
- Papel: 58mm ou 80mm
- Uso: Cupom de pedido, comanda

### 2. **Impressora Bluetooth**
- Modelos: Portáteis para entregadores
- Conexão: Bluetooth
- Papel: 58mm
- Uso: Comprovante de entrega

### 3. **Impressão via Navegador**
- Sem impressora dedicada
- Usa impressora padrão do Windows/Mac/Linux
- Formato: PDF ou HTML

---

## 🔌 COMO CONECTAR IMPRESSORA TÉRMICA

### **Opção 1: Impressora USB (Mais Comum)**

#### 1. Conectar Fisicamente
```
Impressora → Cabo USB → Computador
```

#### 2. Instalar Driver
- **Windows:** Drivers vêm no CD ou site do fabricante
- **Linux:** Geralmente plug-and-play
- **Mac:** Configurar em Preferências → Impressoras

#### 3. Configurar no Sistema

**No Painel do Restaurante:**
```
Settings (Configurações)
└─ Impressão
   ├─ Tipo de Impressora: USB
   ├─ Modelo: Elgin i9
   ├─ Largura do Papel: 80mm
   ├─ Impressão Automática: ✅ Sim
   └─ Número de Vias: 2
```

#### 4. Instalar Software Bridge (Necessário)

O sistema web não acessa USB diretamente. Você precisa de um **software bridge**:

**Opções:**

**A) Usando PrintNode** (Recomendado) 💰 Pago
```bash
# Instalar PrintNode Client
# Windows: Baixar .exe
# Linux: sudo apt install printnode

# Configurar
printnode register <SEU_EMAIL>
```

**B) Usando ESCPOS Print Driver** (Gratuito)
```bash
# Instalar driver ESCPOS
npm install -g escpos-server

# Iniciar servidor
escpos-server --port 9100
```

**C) Usando QZ Tray** (Gratuito)
```
1. Baixar: https://qz.io/download
2. Instalar no computador
3. Configurar certificado
4. Integrar com sistema
```

---

### **Opção 2: Impressora de Rede (Ethernet/WiFi)**

#### 1. Conectar na Rede
```
Impressora → Cabo Ethernet → Roteador
ou
Impressora → WiFi → Rede Local
```

#### 2. Descobrir IP da Impressora
```bash
# Método 1: Imprimir página de configuração
Apertar botão FEED por 5 segundos

# Método 2: Verificar no roteador
Admin do roteador → Dispositivos conectados

# Método 3: Usar ferramenta
ping 192.168.1.100
```

#### 3. Testar Conexão
```bash
# Linux/Mac
echo "Teste" | nc 192.168.1.100 9100

# Windows PowerShell
Test-NetConnection -ComputerName 192.168.1.100 -Port 9100
```

#### 4. Configurar no Sistema
```
Settings → Impressão
├─ Tipo: Rede (TCP/IP)
├─ IP da Impressora: 192.168.1.100
├─ Porta: 9100
├─ Modelo: Epson TM-T20
└─ Largura: 80mm
```

---

## 📋 CONFIGURAÇÕES DISPONÍVEIS NO SISTEMA

### **No Painel:** `Settings` → `Impressão`

```
┌─────────────────────────────────────────┐
│  CONFIGURAÇÕES DE IMPRESSÃO             │
├─────────────────────────────────────────┤
│                                         │
│  Tipo de Impressora                     │
│  ○ Nenhuma (desabilitado)              │
│  ○ USB                                  │
│  ● Rede (TCP/IP) ← SELECIONADO         │
│  ○ Bluetooth                            │
│                                         │
│  IP da Impressora: 192.168.1.100       │
│  Porta: 9100                            │
│                                         │
│  Modelo da Impressora                   │
│  ├─ Elgin i9                           │
│  ├─ Daruma DR700                       │
│  ├─ Epson TM-T20                       │
│  └─ Bematech MP-4200                   │
│                                         │
│  Largura do Papel                       │
│  ○ 58mm (pequena)                      │
│  ● 80mm (padrão) ← SELECIONADO         │
│                                         │
│  Impressão Automática                   │
│  ✅ Imprimir ao receber pedido         │
│                                         │
│  Número de Vias: 2                     │
│  ├─ Via 1: Cozinha                     │
│  └─ Via 2: Cliente                     │
│                                         │
└─────────────────────────────────────────┘
```

---

## 🖨️ O QUE É IMPRESSO

### **Cupom de Pedido** (Auto ao receber)

```
┌────────────────────────────────────┐
│     MARMITARIA DA GI               │
│  Rua das Flores, 123               │
│  (11) 98765-4321                   │
├────────────────────────────────────┤
│                                    │
│  PEDIDO #000123                    │
│  Data: 22/02/2026 14:30           │
│                                    │
│  CLIENTE                           │
│  João da Silva                     │
│  (11) 91234-5678                   │
│  Rua das Palmeiras, 456, Apt 10   │
│                                    │
├────────────────────────────────────┤
│  ITENS                             │
├────────────────────────────────────┤
│  1x Feijoada Completa              │
│     R$ 25,00                       │
│                                    │
│  2x Refrigerante 2L                │
│     R$ 8,00 x 2     R$ 16,00      │
│                                    │
│  OBS: Sem pimenta                  │
│                                    │
├────────────────────────────────────┤
│  Subtotal:        R$   41,00       │
│  Taxa Entrega:    R$    5,00       │
│  Desconto:       -R$    2,00       │
│  ─────────────────────────         │
│  TOTAL:           R$   44,00       │
│                                    │
│  PAGAMENTO: PIX                    │
│  ENTREGA: 30-45 min                │
│                                    │
├────────────────────────────────────┤
│  Obrigado pela preferência! ❤️     │
└────────────────────────────────────┘
```

---

## 🔧 IMPLEMENTAÇÃO TÉCNICA

### **Arquitetura do Sistema**

```
┌──────────────────────────────────────────────┐
│  Sistema Web (Laravel)                       │
│  ├─ Recebe pedido                           │
│  ├─ Salva no banco                          │
│  └─ Envia para fila de impressão           │
└──────────────────────────────────────────────┘
         ↓
┌──────────────────────────────────────────────┐
│  Queue Worker (Redis/Database)               │
│  ├─ Processa fila                           │
│  └─ Formata dados para impressão            │
└──────────────────────────────────────────────┘
         ↓
┌──────────────────────────────────────────────┐
│  Adapter de Impressão                        │
│  ├─ ESC/POS Commands                        │
│  ├─ Socket TCP/IP                           │
│  └─ Envia para impressora                   │
└──────────────────────────────────────────────┘
         ↓
┌──────────────────────────────────────────────┐
│  Impressora Térmica                          │
│  ├─ IP: 192.168.1.100:9100                  │
│  └─ Imprime cupom                           │
└──────────────────────────────────────────────┘
```

### **Pacotes PHP Necessários**

```bash
composer require mike42/escpos-php
composer require pelmered/fake-car
```

### **Exemplo de Código (Simplificado)**

```php
// app/Services/PrintService.php

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

class PrintService
{
    public function printOrder(Order $order): void
    {
        $settings = Settings::current();

        if ($settings->printer_type === 'network') {
            $connector = new NetworkPrintConnector(
                $settings->printer_ip,
                $settings->printer_port
            );

            $printer = new Printer($connector);

            // Cabeçalho
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("MARMITARIA DA GI\n");
            $printer->text("================\n");

            // Pedido
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("PEDIDO #{$order->order_number}\n");
            $printer->text("Data: " . $order->created_at->format('d/m/Y H:i') . "\n");

            // Cliente
            $printer->text("\nCLIENTE\n");
            $printer->text($order->customer->name . "\n");
            $printer->text($order->customer->phone . "\n");

            // Itens
            $printer->text("\nITENS\n");
            $printer->text("================\n");

            foreach ($order->items as $item) {
                $printer->text(
                    sprintf(
                        "%dx %s\n   R$ %s\n",
                        $item->quantity,
                        $item->product_name,
                        number_format($item->unit_price, 2, ',', '.')
                    )
                );
            }

            // Total
            $printer->text("\n================\n");
            $printer->text(
                sprintf(
                    "TOTAL: R$ %s\n",
                    number_format($order->total, 2, ',', '.')
                )
            );

            // Cortar papel
            $printer->cut();
            $printer->close();
        }
    }
}
```

---

## 📱 ALTERNATIVAS MODERNAS

### **1. Google Cloud Print** (Descontinuado)
❌ Não usar - foi descontinuado pelo Google

### **2. PrintNode** (Comercial)
✅ **Recomendado para produção**
- Preço: ~$20/mês
- Funciona 100%
- Suporte completo
- Cloud-based

### **3. QZ Tray** (Open Source)
✅ **Recomendado para começar**
- Gratuito
- Java-based
- Funciona bem
- Comunidade ativa

### **4. Impressão via WhatsApp**
✅ **Alternativa criativa**
- Sistema envia foto do pedido via WhatsApp
- Você imprime manualmente
- Bom para começar

---

## 🚀 PASSO A PASSO RÁPIDO (Para Começar HOJE)

### **Solução Temporária (Sem Hardware Extra)**

1. **Usar Impressão Manual**
   ```
   Painel → Pedidos → Ver Pedido → Imprimir (Ctrl+P)
   ```

2. **Ou exportar PDF**
   ```
   Painel → Pedidos → Ver Pedido → Exportar PDF
   ```

3. **Ou receber por WhatsApp**
   ```
   Sistema envia pedido formatado no WhatsApp
   Você copia e cola onde quiser
   ```

### **Solução Profissional (Com Impressora)**

1. **Comprar Impressora Térmica**
   - Recomendo: Elgin i9 (~R$ 400)
   - Ou: Daruma DR700 (~R$ 350)

2. **Conectar na Rede**
   ```
   Impressora → WiFi → Mesma rede do computador
   ```

3. **Anotar IP**
   ```
   Imprimir página de auto-teste
   Pegar IP: 192.168.1.xxx
   ```

4. **Configurar no Sistema**
   ```
   Painel → Settings → Impressão
   ├─ Tipo: Rede
   ├─ IP: 192.168.1.xxx
   └─ Porta: 9100
   ```

5. **Testar**
   ```
   Botão "Testar Impressão"
   ```

---

## ❓ PERGUNTAS FREQUENTES

### **Posso usar impressora comum?**
✅ Sim, mas não é recomendado:
- Gasta muito papel
- Tinta/toner caro
- Lenta
- Cupom fica grande

### **Preciso de impressora térmica?**
🟡 Não é obrigatório, mas é **muito melhor**:
- Cupom compacto
- Sem tinta (papel térmico)
- Rápida
- Barata a longo prazo

### **Qual modelo comprar?**
⭐ **Recomendações:**
1. **Elgin i9** (R$ 400) - Mais confiável
2. **Daruma DR700** (R$ 350) - Custo-benefício
3. **Epson TM-T20** (R$ 500) - Profissional

### **Precisa de computador ligado?**
✅ Sim, se usar impressora USB
❌ Não, se usar impressora de rede e servidor cloud

### **Funciona com tablet/celular?**
✅ Sim, se a impressora for de rede (WiFi)
❌ Não, se for USB (precisa de computador)

---

## 📊 COMPARATIVO DE CUSTOS

```
┌────────────────────────────────────────────────────┐
│  OPÇÃO               │  CUSTO    │  CUSTO MENSAL   │
├────────────────────────────────────────────────────┤
│  Sem Impressora      │  R$ 0     │  R$ 0           │
│  (Manual)            │           │                 │
├────────────────────────────────────────────────────┤
│  Impressora Térmica  │  R$ 400   │  R$ 15          │
│  (USB)               │           │  (papel)        │
├────────────────────────────────────────────────────┤
│  Impressora de Rede  │  R$ 500   │  R$ 15          │
│  (WiFi)              │           │  (papel)        │
├────────────────────────────────────────────────────┤
│  PrintNode Service   │  R$ 400   │  R$ 90          │
│  (Cloud + Hardware)  │           │  (serviço)      │
└────────────────────────────────────────────────────┘
```

---

## 🎯 RECOMENDAÇÃO FINAL

### **Para Começar (Sem Investir)**
✅ Usar impressão manual via navegador
✅ Ou WhatsApp

### **Para Crescer (Investimento Pequeno)**
✅ Comprar Elgin i9 ou Daruma DR700
✅ Conectar via USB no computador da cozinha
✅ Deixar navegador aberto

### **Para Profissionalizar (Investimento Médio)**
✅ Impressora de rede WiFi
✅ Tablet na cozinha
✅ Impressão automática

### **Para Escalar (Investimento Alto)**
✅ PrintNode + várias impressoras
✅ Múltiplos pontos de impressão
✅ Integração total

---

## 📞 PRECISA DE AJUDA?

1. **Configurar impressora** → Me chame que eu ajudo
2. **Comprar hardware** → Recomendo modelos
3. **Instalar software** → Posso criar script
4. **Integração custom** → Podemos desenvolver

---

**Sistema pronto para impressão! 🖨️**
**Escolha a opção que melhor se adapta ao seu negócio!**

---

**Desenvolvido com ❤️ por Claude Code**
**DeliveryPro - Sistema Multi-Tenant de Delivery**
