# 📖 Guia do Usuário - YumGo Bridge

> Guia simples para configurar impressão automática de pedidos

---

## 🎯 O que é o YumGo Bridge?

**YumGo Bridge** é um app que você instala no computador do seu restaurante para **imprimir pedidos automaticamente**.

### Como Funciona?

```
Cliente faz pedido → Paga → App imprime automaticamente na sua impressora!
```

**Tempo de impressão:** ~3 segundos após pagamento confirmado

---

## 📥 Passo 1: Download

### Windows (Recomendado)

1. Acesse: https://github.com/orfeubr/yumgo/releases/latest
2. Baixe: `YumGo Bridge-X.X.X-win-x64.exe`
3. Execute o instalador
4. Siga as instruções na tela

### macOS

1. Acesse: https://github.com/orfeubr/yumgo/releases/latest
2. Baixe: `YumGo Bridge-X.X.X.dmg`
3. Abra o arquivo DMG
4. Arraste para "Aplicativos"

### Linux

1. Acesse: https://github.com/orfeubr/yumgo/releases/latest
2. Baixe: `YumGo Bridge-X.X.X.AppImage`
3. Dê permissão: `chmod +x YumGo*.AppImage`
4. Execute: `./YumGo*.AppImage`

---

## 🔑 Passo 2: Obter Credenciais

1. **Acesse o painel do seu restaurante:**
   ```
   https://SEU-RESTAURANTE.yumgo.com.br/painel
   ```

2. **Vá em: Configurações → Impressora**

3. **Clique em: "Gerar Token de Acesso"**

   ⚠️ **IMPORTANTE:** Token só aparece 1 vez! Copie agora!

4. **Copie 2 coisas:**
   - ✅ ID do Restaurante
   - ✅ Token de Acesso

---

## 💻 Passo 3: Configurar o App

### Primeira Abertura

1. **Abra o YumGo Bridge** (ícone na área de trabalho)

2. **Cole as credenciais:**
   ```
   ID do Restaurante: [Cole aqui]
   Token de Acesso: [Cole aqui]
   ```

3. **Clique em: "Conectar"**

   ✅ Se conectou: "Status: Conectado"

   ❌ Se erro: Verifique se copiou corretamente

---

## 🖨️ Passo 4: Configurar Impressora

### Impressora USB (Mais Comum)

1. **Conecte a impressora** no computador (USB)

2. **Ligue a impressora**

3. **No app, clique em:** "Cozinha" (ou "Balcão"/ "Bar")

4. **Selecione:** Tipo = USB

5. **Clique em:** "🔍 Buscar Impressoras USB"

6. **Resultado:**
   ```
   ✅ 1 impressora(s) encontrada(s)!
   ```

7. **Selecione no dropdown:**
   ```
   Impressora USB: [📄 Epson TM-T20  ▼]
   ```

8. **Configure opções avançadas:**
   - Número de cópias: 2 (1 cozinha, 1 entregador)
   - Largura do papel: 80mm (padrão)
   - Tamanho da fonte: Normal
   - Logo: ✓ (se quiser) → Selecionar arquivo PNG/JPG
   - Remover acentos: ☐ (só se impressora antiga)

9. **Clique em:** "Salvar Configuração"

   ✅ Status: "Configurada ✓"

### Impressora de Rede (Menos Comum)

1. **Selecione:** Tipo = Rede

2. **Preencha:**
   - IP da impressora: `192.168.1.100` (exemplo)
   - Porta: `9100` (padrão)

3. **Configure opções avançadas** (igual USB acima)

4. **Salve**

---

## ✅ Passo 5: Testar

### Teste Rápido

1. **App deve estar:**
   - ✅ Conectado
   - ✅ Impressora configurada

2. **Faça um pedido teste:**
   - Acesse o site do seu restaurante
   - Faça um pedido
   - Pague com PIX ou Cartão
   - **Aguarde confirmação de pagamento**

3. **Resultado esperado:**
   - 🔔 Notificação no computador
   - 🖨️ Impressora imprime automaticamente!
   - 🎵 Som de alerta

**Tempo:** ~3 segundos após pagamento confirmado

---

## 🔧 Problemas Comuns

### "Nenhuma impressora encontrada"

**Possíveis causas:**
- ❌ Impressora desligada
- ❌ Cabo USB desconectado
- ❌ Driver não instalado

**Solução:**
1. Verifique se impressora está ligada
2. Reconecte o USB
3. Instale driver do fabricante
4. Tente "Buscar" novamente

---

### "Status: Desconectado"

**Possíveis causas:**
- ❌ Internet caiu
- ❌ Token expirou
- ❌ Token revogado

**Solução:**
1. Verifique internet
2. Gere novo token no painel
3. Cole no app novamente
4. Clique "Conectar"

---

### "Impressora não imprime"

**Possíveis causas:**
- ❌ Pedido não foi pago ainda
- ❌ Papel acabou
- ❌ Impressora travou

**Solução:**
1. Verifique se pedido está PAGO no painel
2. Verifique papel na impressora
3. Desligue e ligue impressora
4. No painel: "Reimprimir" (botão no pedido)

---

## 💡 Dicas

### Manter App Funcionando

- ✅ Deixe app aberto sempre (minimiza para bandeja)
- ✅ Configure para iniciar com Windows (futuro)
- ✅ Não revogue token sem necessidade

### Economizar Papel

- Configuração "Número de cópias": 1 via
- Desative logo (se não precisar)
- Use papel 58mm (mais barato)

### Melhor Configuração

**Cozinha:**
- 2 vias (1 fica, 1 vai com entregador)
- 80mm
- Fonte normal
- Sem logo (economiza papel)

**Balcão:**
- 1 via (cliente leva)
- 80mm
- Fonte normal
- COM logo (profissionalismo)

---

## 🔄 Atualizações

### Auto-Update (Automático)

O app verifica atualizações automaticamente ao iniciar.

**Quando houver atualização:**

1. **Notificação aparece:**
   ```
   Nova versão X.X.X disponível!
   Deseja baixar?
   ```

2. **Clique:** "Sim, Baixar"

3. **Aguarde download:** Barra de progresso

4. **Clique:** "Instalar e Reiniciar"

5. **Pronto!** App atualizado

### Manual (Se Necessário)

1. Menu bandeja → "🔄 Verificar Atualizações"
2. Ou baixe nova versão do GitHub

---

## 📞 Suporte

### Precisa de ajuda?

**Email:** suporte@yumgo.com.br

**WhatsApp:** (11) 99999-9999

**Site:** https://yumgo.com.br/suporte

---

## ❓ Perguntas Frequentes

### "Preciso deixar o painel web aberto?"

❌ **NÃO!** Pode fechar o navegador.

O app roda **no computador**, não no navegador.

### "Impressora funciona só quando app está aberto?"

✅ **SIM!** App precisa estar aberto (pode minimizar).

### "Posso instalar em vários computadores?"

✅ **SIM!** Mas só 1 imprime (primeiro que conectar).

Recomendado: 1 computador por restaurante.

### "Cliente pode ver minhas impressoras?"

❌ **NÃO!** Impossível. Navegador não acessa USB.

Só o app instalado no seu computador vê.

### "Token expira?"

❌ **NÃO expira** (válido para sempre).

Mas você pode revogar no painel e gerar novo.

---

## ✅ Checklist de Instalação

- [ ] App baixado e instalado
- [ ] Token gerado no painel
- [ ] Credenciais coladas no app
- [ ] Status: Conectado
- [ ] Impressora configurada (USB ou Rede)
- [ ] Teste realizado (pedido imprimiu)
- [ ] App minimizado na bandeja

---

**Pronto! Seu sistema está configurado! 🎉**

Pedidos pagos imprimirão automaticamente daqui pra frente.

---

**Data:** 06/03/2026
**Versão do Guia:** 1.0
**Para App:** YumGo Bridge v1.8.0+
