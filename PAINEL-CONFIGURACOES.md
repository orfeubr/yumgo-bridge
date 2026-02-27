# 🎛️ Painel de Configurações Completo

**Data**: 22/02/2026

---

## 🎯 Sistema de Configurações do Restaurante

Agora cada restaurante pode **personalizar completamente** seu sistema através do painel Filament!

### 📍 Como Acessar
```
https://SEU-RESTAURANTE.eliseus.com.br/painel/settings
```

---

## 📑 10 Abas de Configurações

### 1️⃣ **Identidade Visual** 🎨
Personalize a aparência do seu aplicativo:

- **Logo** (512×512px recomendado)
- **Banner** (1920×1080px recomendado)
- **Cor Primária** (botões, destaques)
- **Cor Secundária** (textos, elementos)
- **Cor de Destaque** (promoções, badges)

**Exemplo:**
```
Marmitaria da Gi:
- Primária: #EA1D2C (vermelho)
- Secundária: #333333 (preto)
- Destaque: #FFA500 (laranja)
```

---

### 2️⃣ **Contato** 📞
Informações de contato:

- Telefone
- WhatsApp (formato: 5511987654321)
- E-mail
- Endereço completo
- Instagram (@usuario)
- Facebook (link)

---

### 3️⃣ **Horários** ⏰
Configure quando você está aberto:

- **Toggle "Aberto/Fechado"** (emergências, feriados)
- **Horários por dia da semana**:
  ```json
  Segunda: 18:00 - 23:00
  Terça:   18:00 - 23:00
  ...
  Domingo: 18:00 - 23:00
  ```
- **Mensagem de fechamento** personalizada

**Recurso Inteligente:**
- Sistema detecta automaticamente se está aberto
- Exibe mensagem para clientes quando fechado

---

### 4️⃣ **Delivery** 🚚
Configurações de entrega:

- ✅ Permitir delivery
- ✅ Permitir retirada no local
- **Taxa de entrega** (R$ 0 = grátis)
- **Pedido mínimo** (R$)
- **Raio de entrega** (km)
- **Tempo estimado** (minutos)

**Exemplo:**
```
Taxa: R$ 5,00
Mínimo: R$ 20,00
Raio: 10km
Tempo: 45 min
```

---

### 5️⃣ **Pagamentos** 💳
Métodos aceitos (ative/desative):

- ✅ PIX
- ✅ Cartão de Crédito
- ✅ Cartão de Débito
- ✅ Dinheiro
- ⬜ Vale-Refeição

---

### 6️⃣ **Impressora** 🖨️
Configure sua impressora térmica:

**Tipos suportados:**
- Rede (IP)
- USB
- Bluetooth
- Nenhuma

**Configurações:**
- IP da impressora (ex: 192.168.1.100)
- Porta (padrão: 9100)
- Modelo (ex: Epson TM-T20)
- Largura do papel (58mm ou 80mm)
- **Auto-imprimir pedidos** ✅
- Número de cópias (1-5)

**Modelos compatíveis:**
- Epson TM-T20
- Bematech MP-4200
- Daruma DR-800
- Elgin i9
- E outras ESC/POS

---

### 7️⃣ **Pedidos** 📦
Gestão de pedidos:

- **Auto-aceitar pedidos** ✅/❌
  - ON: Pedidos aprovados automaticamente
  - OFF: Você precisa aceitar manualmente

- **Tempo de preparo** (minutos)
- **Exigir telefone do cliente** ✅
- **Exigir CPF do cliente** ⬜
- **Instruções para pedidos** (texto livre)

**Exemplo de instrução:**
```
"Pedidos acima de R$ 50 ganham refrigerante grátis! 🥤"
```

---

### 8️⃣ **Notificações** 🔔
Como você quer ser notificado de novos pedidos:

- ✅ E-mail
- ⬜ SMS
- ⬜ WhatsApp

**Configurar:**
- E-mail para receber notificações
- Telefone para SMS/WhatsApp

**Som no painel:**
- Notificação sonora automática quando chega pedido
- Ícone piscando na aba do navegador

---

### 9️⃣ **Recursos** ✨
Ative/desative funcionalidades:

- ✅ **Avaliações de clientes**
  - Clientes podem avaliar produtos e pedidos

- ✅ **Programa de fidelidade (Cashback)**
  - Bronze → Prata → Ouro → Platina

- ✅ **Cupons de desconto**
  - Criar promoções personalizadas

- ⬜ **Agendamento de pedidos**
  - Cliente escolhe data/hora futura

---

### 🔟 **Políticas** 📄
Textos legais (editor rico):

- **Termos de Serviço**
- **Política de Privacidade**
- **Política de Trocas e Devoluções**

---

## 💾 Como o Sistema Funciona

### Salvamento Automático
- Clique em "Salvar Alterações" no topo
- Todas as abas são salvas de uma vez
- Notificação de sucesso aparece

### Persistência
- Configurações salvas no banco de dados
- Uma tabela `settings` por tenant
- Valores padrão inteligentes

### API de Acesso
```php
// No código do aplicativo
$settings = Settings::current();

echo $settings->primary_color; // #EA1D2C
echo $settings->delivery_fee;  // 5.00
echo $settings->isOpenNow();   // true/false
```

---

## 🎨 Exemplo: Personalização de Cores

### Antes (padrão vermelho)
```
Primária: #EA1D2C (vermelho iFood)
```

### Depois (personalizado)
```
Pizzaria Italiana:
- Primária: #00843D (verde Itália)
- Secundária: #DC143C (vermelho Itália)
- Destaque: #FFDD00 (amarelo queijo)

Marmitaria Fitness:
- Primária: #7CB342 (verde saudável)
- Secundária: #558B2F (verde escuro)
- Destaque: #FFC107 (amarelo energia)
```

---

## 🖨️ Exemplo: Impressora Térmica

### Configuração
```
Tipo: Rede (IP)
IP: 192.168.1.100
Porta: 9100
Modelo: Epson TM-T20
Papel: 58mm
Auto-imprimir: SIM
Cópias: 2 (1 cozinha + 1 cliente)
```

### Resultado
Quando chega pedido:
1. Sistema recebe pedido
2. Salva no banco
3. **Imprime automaticamente** 2 vias:
   - Via 1: Cozinha (fica na copa)
   - Via 2: Cliente (vai com entregador)

---

## 📊 Recursos Adicionais (já implementados)

### Dashboard
- Vendas do dia/mês
- Produtos mais vendidos
- Clientes ativos
- Pedidos pendentes

### Relatórios
- Vendas por período
- Produtos por categoria
- Clientes fiéis
- Cashback distribuído

### Gestão
- Produtos (CRUD completo)
- Categorias
- Variações (P, M, G)
- Adicionais
- Cupons
- Clientes
- Pedidos

---

## 🚀 Funcionalidades Inspiradas no AnotaAI

Já incluídas:
- ✅ Cardápio digital com QR Code
- ✅ Personalização de cores
- ✅ Logo customizada
- ✅ Horário de funcionamento
- ✅ Impressora térmica
- ✅ Gestão simples e eficiente
- ✅ Notificações em tempo real
- ✅ Painel mobile-friendly

Diferenciais DeliveryPro:
- ✅ Cashback configurável
- ✅ Multi-tenant
- ✅ Comissão baixa (1-3%)
- ✅ Asaas integrado
- ✅ PWA instalável

---

## 📱 Acesso ao Painel

### Marmitaria da Gi
```
URL: https://marmitaria-gi.eliseus.com.br/painel
Login: admin@marmitaria-gi.com
```

### Pizzaria Bella
```
URL: https://pizzaria-bella.eliseus.com.br/painel
Login: admin@pizzaria-bella.com
```

---

## 🎯 Próximas Configurações

Em desenvolvimento:
- [ ] Zonas de entrega (mapa)
- [ ] Integração com Correios (CEP)
- [ ] Google Analytics
- [ ] Facebook Pixel
- [ ] WhatsApp Business API
- [ ] Backup automático
- [ ] Exportar dados (LGPD)

---

**✅ PAINEL DE CONFIGURAÇÕES COMPLETO E PROFISSIONAL!**

**Inspirado no AnotaAI, melhor que o iFood!** 🚀

**Desenvolvido para DeliveryPro**
