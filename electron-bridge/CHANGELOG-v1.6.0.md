# 📋 YumGo Bridge - Versão 1.6.0

## 🎉 Configuração de Impressoras SIMPLIFICADA!

### ❌ ANTES (v1.5.0 e anteriores)

**Problema:** Interface técnica demais para usuários finais

```
Vendor ID: [        ]  ← O que é isso? 🤔
Product ID: [        ]  ← Onde eu encontro?
[Buscar Impressoras USB]
```

Quando clicava "Buscar", mostrava:
```
Impressoras encontradas:
1. Vendor ID: 1208, Product ID: 3603
2. Vendor ID: 1305, Product ID: 32779
```

**Usuário pensava:** "O que são esses números? Qual eu escolho?" 😵

---

### ✅ AGORA (v1.6.0)

**Solução:** Interface intuitiva com nomes amigáveis!

```
Impressora USB: [Clique em "Buscar" abaixo  ▼]
[🔍 Buscar Impressoras USB]

💡 Dica: Conecte sua impressora USB e clique em "Buscar"
```

Quando clica "Buscar", mostra:
```
✅ 2 impressora(s) encontrada(s)!
Selecione uma impressora na lista acima.
```

E o select fica assim:
```
Impressora USB: [Selecione uma impressora  ▼]
                 📄 Epson TM-T20
                 📄 Bematech MP-4200 TH
```

**Usuário pensa:** "Ah! É a Epson que está aqui do lado!" 😄

---

## 🔧 O que mudou tecnicamente?

### 1. Dicionário de Fabricantes Conhecidos

Adicionado mapeamento de Vendor IDs para nomes de fabricantes:

| Vendor ID | Nome         |
|-----------|--------------|
| 0x04b8    | Epson        |
| 0x0519    | Bematech     |
| 0x0483    | Elgin        |
| 0x1504    | Daruma       |
| 0x154f    | Diebold      |
| 0x0fe6    | IVI          |
| 0x0dd4    | Zebra        |
| 0x2730    | Sewoo        |
| 0x0924    | Star Micronics |

### 2. Dicionário de Modelos Conhecidos

Adicionado mapeamento de Product IDs para modelos:

| Product ID | Modelo          |
|------------|-----------------|
| 0x0e15     | TM-T20          |
| 0x0e03     | TM-T88          |
| 0x0e01     | TM-T81          |
| 0x2008     | MP-4200 TH      |
| 0x7070     | i9              |
| 0x0202     | DR700           |

### 3. Nome Completo Amigável

Formato: `{Fabricante} {Modelo}`

Exemplos:
- `Epson TM-T20`
- `Bematech MP-4200 TH`
- `Elgin i9`
- `Daruma DR700`

Se não estiver no dicionário:
- `Desconhecido Modelo 8E0F`

### 4. Interface Simplificada

**ANTES:**
- 2 campos de texto (Vendor ID, Product ID)
- Alert com números hexadecimais
- Usuário tinha que entender IDs

**AGORA:**
- 1 select dropdown (lista de impressoras)
- Nomes descritivos (`Epson TM-T20`)
- Campos técnicos escondidos (preenchidos automaticamente)

---

## 📁 Arquivos Modificados

### 1. `src/main.js` (linhas 484-534)

**Adicionado:**
```javascript
// Dicionários de fabricantes e modelos
const KNOWN_VENDORS = { ... };
const KNOWN_MODELS = { ... };

// Handler melhorado que retorna nomes amigáveis
ipcMain.handle('find-usb-printers', async () => {
    // ...
    return devices.map(device => ({
        vendorId: vendorId,
        productId: productId,
        displayName: `${vendorName} ${modelName}`,  // ← Nome amigável!
        vendorName: vendorName,
        modelName: modelName
    }));
});
```

### 2. `src/renderer.js` (linhas 132-220)

**Modificado:**
```javascript
// UI com select em vez de inputs
fieldsDiv.innerHTML = `
    <select id="${location}PrinterSelect">
        <option>Clique em "Buscar" abaixo</option>
    </select>
    <button onclick="findUSBPrinters('${location}')">
        🔍 Buscar Impressoras USB
    </button>

    <!-- Campos escondidos -->
    <input type="hidden" id="${location}VendorId">
    <input type="hidden" id="${location}ProductId">
`;

// Função melhorada que popula o select
async function findUSBPrinters(location) {
    printers.forEach((printer, index) => {
        option.textContent = `📄 ${printer.displayName}`;  // ← Nome amigável!
    });
}

// Nova função ao selecionar impressora
function selectPrinter(location) {
    // Preenche campos escondidos automaticamente
    document.getElementById(`${location}VendorId`).value = ...;
    document.getElementById(`${location}ProductId`).value = ...;
}
```

---

## 🎯 Fluxo do Usuário (v1.6.0)

1. **Usuário abre configuração de impressora**
   - Vê: "Impressora USB: [Clique em 'Buscar' abaixo ▼]"

2. **Usuário clica em "🔍 Buscar Impressoras USB"**
   - App escaneia portas USB
   - Encontra impressoras conectadas
   - Mapeia para nomes amigáveis

3. **Mensagem de sucesso**
   ```
   ✅ 2 impressora(s) encontrada(s)!
   Selecione uma impressora na lista acima.
   ```

4. **Usuário abre o select**
   - Vê: "📄 Epson TM-T20"
   - Vê: "📄 Bematech MP-4200 TH"

5. **Usuário seleciona "📄 Epson TM-T20"**
   - App preenche automaticamente:
     - VendorId: 0x04b8 (escondido)
     - ProductId: 0x0e15 (escondido)

6. **Usuário clica "Salvar Configuração"**
   - Impressora configurada! ✅

---

## 🚀 Benefícios

### Para o Usuário
- ✅ Não precisa saber o que é Vendor ID ou Product ID
- ✅ Vê nome da impressora que reconhece ("Epson TM-T20")
- ✅ Configuração em 3 cliques (Buscar → Selecionar → Salvar)
- ✅ Mensagens claras e amigáveis

### Para o Desenvolvedor
- ✅ Menos suporte técnico ("o que é vendor ID?")
- ✅ Menos erros de configuração
- ✅ Facilita onboarding de novos restaurantes
- ✅ Código mais limpo (dicionários centralizados)

---

## 🔮 Melhorias Futuras

### v1.7.0 (Planejado)
- [ ] Detectar automaticamente na inicialização
- [ ] Lembrar última impressora usada
- [ ] Botão "Testar Impressão" antes de salvar
- [ ] Suporte a mais fabricantes (Custom, Citizen, etc)
- [ ] Auto-atualização de dicionários via API

### v1.8.0 (Planejado)
- [ ] Impressoras de rede: auto-descoberta via mDNS
- [ ] QR Code para configuração remota
- [ ] Exportar/importar configurações

---

## ⚙️ Para Adicionar Novos Fabricantes

### Descobrir Vendor ID e Product ID

**Windows:**
```powershell
Get-PnpDevice -Class USB | Select-Object InstanceId
```

**Linux:**
```bash
lsusb
```

**Resultado:**
```
Bus 001 Device 005: ID 04b8:0e15 Epson Corp. TM-T20
                       ^^^^  ^^^^
                       |     |
                       |     Product ID
                       Vendor ID
```

### Adicionar ao Dicionário

**src/main.js:**
```javascript
const KNOWN_VENDORS = {
    0x04b8: 'Epson',
    0x1234: 'Novo Fabricante',  // ← Adicionar aqui
    // ...
};

const KNOWN_MODELS = {
    0x0e15: 'TM-T20',
    0x5678: 'Modelo XYZ',  // ← Adicionar aqui
    // ...
};
```

---

## 🐛 Problemas Conhecidos

### Nenhum até o momento! 🎉

---

## 🙏 Feedback do Usuário

**Comentário Original:**
> "ACHO QUE aquela configuração de impressora não está muito fácil..
> eu não sei o que é vendor ou product id"

**Status:** ✅ RESOLVIDO na v1.6.0!

---

**Data:** 06/03/2026
**Autor:** Claude Code + User
**Versão:** 1.6.0
