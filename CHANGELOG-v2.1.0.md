# 🚀 YumGo Bridge v2.1.0 - Suporte a Impressoras do Sistema

**Data de Release:** 14/03/2026
**Versão:** 2.1.0

---

## ✨ Novidades

### **Suporte a Impressoras do Sistema Windows/macOS/Linux**

Agora o Bridge suporta **qualquer impressora instalada** no sistema operacional, não apenas impressoras térmicas USB/Network!

**Tipos de impressora suportados:**
- ✅ **USB** - Impressoras térmicas ESC/POS (modo original)
- ✅ **Network** - Impressoras de rede TCP/IP ESC/POS (modo original)
- ✅ **System** - **NOVO!** Impressoras do sistema Windows/macOS/Linux

---

## 🎯 Benefícios

### **Antes (v2.0.0):**
- ❌ Só aceitava impressoras térmicas USB com VendorID/ProductID
- ❌ Impressoras de rede precisavam suportar ESC/POS na porta 9100
- ❌ Não funcionava com impressoras comuns do Windows

### **Agora (v2.1.0):**
- ✅ **Qualquer impressora** instalada no Windows/macOS/Linux
- ✅ Configuração **simples** (só nome da impressora)
- ✅ Não precisa VendorID/ProductID
- ✅ Funciona com drivers genéricos

---

## 📋 Como Usar

### **1. Configurar Impressora do Sistema:**

No YumGo Bridge:

1. **Tipo:** Selecione **"Sistema"** (System)
2. **Nome:** Digite o nome EXATO da impressora instalada no Windows
   - Exemplo: "Knup Termica"
   - Exemplo: "Generic / Text Only"
   - Exemplo: "Epson TM-T20"
3. **Largura do papel:** 58mm ou 80mm
4. **Cópias:** 1-3 (opcional)
5. Salvar configuração

### **2. Teste de Impressão:**

1. Clique em **"Imprimir Teste"**
2. Deve imprimir um recibo formatado ✅

---

## 🔧 Mudanças Técnicas

### **Arquivos Modificados:**

#### **package.json**
```diff
+ "printer": "^0.4.0",  // Biblioteca de impressão nativa
+ "version": "2.1.0",
- "escpos-network": "^3.0.0-alpha.1",
+ "escpos-network": "^3.0.0-alpha.8",  // Versão mais estável
```

#### **src/printer.js**
```diff
+ const printer = require('printer');  // Impressão nativa

- // VALIDAÇÃO: Impressoras do sistema ainda não suportadas
- if (config.type === 'system') {
-     throw new Error('Impressoras do sistema não suportadas');
- }

+ // v2.1.0: Suporte a impressoras do sistema
+ if (config.type === 'system') {
+     return this.printSystemPrinter(orderData, location, copies);
+ }

+ /**
+  * Novo método: printSystemPrinter()
+  * Imprime em qualquer impressora Windows/macOS/Linux
+  */
+ async printSystemPrinter(orderData, location, copies) { ... }

+ /**
+  * Novo método: generateTextReceipt()
+  * Gera recibo em texto puro (sem ESC/POS)
+  */
+ generateTextReceipt(order, location) { ... }
```

---

## 📊 Comparação de Modos

| Recurso | USB/Network (ESC/POS) | System (Nativo) |
|---------|----------------------|-----------------|
| **Formatação** | Rica (negrito, tamanhos) | Texto simples |
| **Logo** | ✅ Suporta | ❌ Não suporta |
| **Corte automático** | ✅ Suporta | ❌ Não suporta |
| **Compatibilidade** | Só térmicas ESC/POS | Qualquer impressora |
| **Configuração** | VID/PID ou IP:porta | Nome da impressora |
| **Velocidade** | Rápida | Média |

---

## ⚠️ Notas Importantes

### **Limitações do Modo System:**

1. **Sem formatação avançada** (negrito, corte, logo)
2. **Texto puro** (similar a imprimir do Bloco de Notas)
3. **Depende do driver Windows** estar funcionando
4. **Não corta papel automaticamente** (se impressora térmica)

### **Quando usar cada modo:**

**USB/Network (ESC/POS):**
- ✅ Impressoras térmicas (Epson, Bematech, Elgin, Daruma)
- ✅ Quando quer formatação rica
- ✅ Quando precisa corte automático

**System (Nativo):**
- ✅ Impressoras genéricas
- ✅ Quando VID/PID não é detectado
- ✅ Quando ESC/POS não funciona
- ✅ Configuração rápida e simples

---

## 🐛 Correções

- ✅ Atualizado `escpos-network` para versão `alpha.8` (mais estável)
- ✅ Corrigido erro EINVAL em conexões de rede
- ✅ Melhorado log de erros de impressão

---

## 📖 Exemplos de Uso

### **Exemplo 1: Impressora Térmica USB (modo ESC/POS)**
```json
{
  "type": "usb",
  "vendorId": "0x0416",
  "productId": "0x5011",
  "paperWidth": 80,
  "copies": 2
}
```

### **Exemplo 2: Impressora do Sistema Windows (modo nativo)**
```json
{
  "type": "system",
  "printerName": "Knup Termica",
  "paperWidth": 58,
  "copies": 1
}
```

### **Exemplo 3: Impressora de Rede (modo ESC/POS)**
```json
{
  "type": "network",
  "ip": "192.168.1.100",
  "port": 9100,
  "paperWidth": 80,
  "copies": 1
}
```

---

## 🔄 Como Atualizar

### **1. Reinstalar dependências:**
```bash
cd electron-bridge
rm -rf node_modules package-lock.json
npm install
```

### **2. Fazer rebuild para Electron:**
```bash
npm run postinstall
```

### **3. Testar localmente:**
```bash
npm run dev
```

### **4. Gerar build para produção:**
```bash
# Windows
npm run build:win

# macOS
npm run build:mac

# Todos
npm run build:all
```

**Instalador:** `/electron-bridge/dist/YumGo Bridge-2.1.0-win-x64.exe`

---

## ✅ Checklist de Teste

Antes de usar em produção, teste:

- [ ] Impressão de teste funciona?
- [ ] Pedido real imprime corretamente?
- [ ] Formatação está legível?
- [ ] Múltiplas cópias funcionam?
- [ ] Diferentes locais (Cozinha, Bar, Balcão) funcionam?

---

## 🆘 Problemas Conhecidos

### **Impressora não aparece na lista:**
- Verifique se está instalada no Windows (`Win + I` → Impressoras)
- Status deve ser "Pronta" (não "Offline")

### **Erro ao imprimir:**
- Verifique nome EXATO da impressora (case-sensitive)
- Teste imprimir do Bloco de Notas primeiro

### **Formatação ruim:**
- Ajuste largura do papel (58mm ou 80mm)
- Se possível, use modo USB/Network com ESC/POS

---

## 📞 Suporte

**Dúvidas ou problemas?**
- GitHub: https://github.com/orfeubr/yumgo/issues
- Email: suporte@yumgo.com.br

---

**Desenvolvido com ❤️ por Claude Sonnet 4.5**
