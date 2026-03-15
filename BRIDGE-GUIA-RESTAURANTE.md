# 🖨️ YumGo Bridge - Guia para Restaurantes

## 📖 O Que é o YumGo Bridge?

O **YumGo Bridge** é um aplicativo para Windows que **imprime automaticamente** os pedidos do seu restaurante assim que eles chegam.

### ✨ Recursos:

- 🔔 **Impressão automática** quando chega um pedido
- 🖨️ **Múltiplas impressoras** (balcão, cozinha, bar)
- ⚙️ **Configuração de papel** (32-48 caracteres por linha)
- 🚀 **Inicia com o Windows** (opcional)
- 💾 **Configurações salvas** permanentemente
- 📡 **Conexão em tempo real** via WebSocket

---

## 📥 Como Instalar

### 1. Download

Baixe a última versão:
```
https://github.com/orfeubr/yumgo/releases/latest
```

Arquivo: `YumGo-Bridge-X.X.X-win-x64.exe`

### 2. Instalação

1. **Execute o instalador** (duplo clique)
2. **Clique em "Avançar"** 3 vezes
3. **Clique em "Instalar"**
4. **Aguarde** a instalação (1-2 minutos)
5. **Clique em "Concluir"**

✅ O YumGo Bridge será instalado em:
```
C:\Users\{SeuUsuário}\AppData\Local\Programs\yumgo-bridge\
```

---

## ⚙️ Como Configurar

### 1. Primeiro Acesso

Ao abrir o YumGo Bridge pela primeira vez:

1. **Aba "Conexão"**:
   - **Restaurant ID**: Seu ID único (ex: `marmitariadagi`)
   - **Token**: Seu token de autenticação
   - Clique em **"Conectar"**

2. **Aba "Impressoras"**:
   - Configure cada impressora (balcão, cozinha, bar)
   - Escolha o tipo (USB ou Rede)
   - Selecione a impressora
   - Ajuste caracteres por linha (32-48)
   - Escolha espaçamento e número de cópias

3. **Aba "Configurações"**:
   - Marque **"Iniciar com o Windows"** (recomendado)
   - Escolha tema (claro/escuro)

---

## 🖨️ Tipos de Impressora

### USB (Mais Comum)

**Exemplo:** Impressora térmica conectada via USB

1. Conecte a impressora no computador
2. Instale o driver (se necessário)
3. No Bridge, selecione tipo: **"USB"**
4. Escolha a impressora na lista

### Rede (Wi-Fi/Ethernet)

**Exemplo:** Impressora com IP fixo (192.168.1.100)

1. Configure IP fixo na impressora
2. No Bridge, selecione tipo: **"Rede"**
3. Digite o IP da impressora
4. Digite a porta (geralmente `9100`)

---

## 📏 Configuração de Papel

### Caracteres por Linha

Ajuste conforme a largura do papel:

- **80mm** → 48 caracteres (padrão)
- **58mm** → 32 caracteres

**Teste visual:**
- 32 chars: ```================================```
- 48 chars: ```================================================```

### Espaçamento

- **Compacto**: Menos espaço entre linhas (economiza papel)
- **Normal**: Espaçamento padrão
- **Espaçoso**: Mais espaço (melhor leitura)

---

## 🔔 Como Funciona

### 1. Cliente Faz Pedido

Cliente faz pedido pelo app/site do YumGo.

### 2. Pedido Chega em Tempo Real

O Bridge **recebe automaticamente** via WebSocket.

### 3. Impressão Automática

O pedido é **impresso automaticamente** nas impressoras configuradas:

- **Balcão**: Recibo completo (sempre)
- **Cozinha**: Apenas itens da cozinha
- **Bar**: Apenas bebidas

### 4. Som de Notificação

Um **beep** toca quando o pedido é impresso.

---

## 🎨 Exemplo de Impressão

```
========================================
        MARMITARIA DA GI
========================================

Pedido: #0042
Data: 15/03/2026 14:30

CLIENTE
Nome: João Silva
Tel: (11) 99999-9999

ENTREGA
Rua das Flores, 123 - Centro
Ref: Próximo ao mercado

ITEMS
2x Marmita Grande (Frango)    R$ 30,00
   + Arroz, Feijão, Salada
   + OBS: Sem cebola

1x Refrigerante Lata          R$ 5,00

========================================
SUBTOTAL                      R$ 35,00
Taxa de Entrega               R$ 5,00
----------------------------------------
TOTAL                         R$ 40,00
========================================

Pagamento: PIX
Status: PAGO

Obs: Entregar antes das 12h

========================================
        Powered by YumGo
========================================
```

---

## 🔧 Solução de Problemas

### ❌ "Desconectado"

**Problema:** Bridge não conecta

**Soluções:**
1. Verifique sua internet
2. Verifique Restaurant ID e Token
3. Clique em "Reconectar"
4. Reinicie o Bridge

### ❌ "Impressora não encontrada"

**Problema:** Bridge não acha a impressora

**Soluções:**
1. Verifique se a impressora está ligada
2. Verifique conexão USB/Rede
3. Reinstale o driver da impressora
4. Teste imprimir direto do Windows

### ❌ "Impressão cortada"

**Problema:** Texto cortado nas bordas

**Solução:**
- Reduza os caracteres por linha (48 → 42 → 38 → 32)

### ❌ "Muito espaçado"

**Problema:** Muito espaço entre linhas

**Solução:**
- Mude espaçamento para "Compacto"
- Reduza número de cópias

---

## 📞 Suporte

### Como Obter Restaurant ID e Token?

1. Acesse o painel do restaurante
2. Menu **"Configurações"** → **"Integração"**
3. Seção **"YumGo Bridge"**
4. Copie seu **Restaurant ID** e **Token**

### Problemas?

Entre em contato:
- **Email:** suporte@yumgo.com.br
- **WhatsApp:** (11) 99999-9999
- **GitHub Issues:** https://github.com/orfeubr/yumgo/issues

---

## ⚡ Dicas Pro

### 1. Teste Antes de Usar

Use a função **"Teste de Impressão"** para verificar se está tudo OK.

### 2. Configure Múltiplas Impressoras

- **Balcão**: Recibo completo
- **Cozinha**: Sem bebidas
- **Bar**: Apenas bebidas

### 3. Economize Papel

- Use espaçamento "Compacto"
- Configure 1 cópia (não 2 ou 3)
- Desative impressão de logo (se não usar)

### 4. Mantenha Atualizado

O Bridge verifica atualizações automaticamente.
Quando houver update, aceite para ter novos recursos!

---

## 📋 Requisitos do Sistema

- **Windows:** 10 ou 11 (64-bit)
- **RAM:** 2GB mínimo
- **Espaço:** 200MB livres
- **Internet:** Conexão estável
- **Impressora:** Térmica USB ou Rede (ESC/POS)

---

## 🎯 Checklist de Configuração

Antes de começar a usar:

- [ ] Bridge instalado
- [ ] Restaurant ID configurado
- [ ] Token configurado
- [ ] Status: "Conectado" (verde)
- [ ] Impressora configurada
- [ ] Teste de impressão funcionando
- [ ] Autostart ativado (opcional)
- [ ] Configurações salvas

---

## 🎉 Pronto!

Agora seu restaurante está pronto para receber pedidos automaticamente!

Quando um cliente fizer um pedido, você vai ouvir um **beep** e o pedido será **impresso automaticamente**! 🖨️🔔

---

**Última atualização:** 15/03/2026
**Versão do Bridge:** 3.0.0
**Versão do Guia:** 1.0
