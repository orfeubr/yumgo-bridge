# 🧪 Guia de Teste - YumGo Bridge v1.6.0

## 🎯 O que testar?

**Feature:** Configuração simplificada de impressoras USB

**Problema resolvido:** Usuários não sabiam o que eram "Vendor ID" e "Product ID"

**Solução:** Lista selecionável com nomes amigáveis (ex: "Epson TM-T20")

---

## 🚀 Como Testar

### 1. Buildar a nova versão

```bash
cd /var/www/restaurante/electron-bridge

# Instalar dependências (se necessário)
npm install

# Testar em modo dev
npm run dev

# Ou fazer build completo
npm run build:win   # Windows
npm run build:linux # Linux
npm run build:mac   # macOS
```

---

### 2. Cenário de Teste: Configurar Impressora USB

#### Pré-requisitos
- Impressora térmica USB conectada e ligada
- Drivers instalados (se necessário)

#### Passos

1. **Abrir YumGo Bridge**
   - Execute o app
   - Faça login com credenciais do restaurante

2. **Ir para configuração de impressora**
   - Clique em "Configurações" ou "Impressoras"
   - Escolha "Cozinha", "Balcão" ou "Borda"

3. **Selecionar tipo USB**
   - Dropdown "Tipo": Selecione "USB"

4. **🔍 Buscar impressoras**
   - Você deve ver:
     ```
     Impressora USB: [Clique em "Buscar" abaixo  ▼]
     [🔍 Buscar Impressoras USB]

     💡 Dica: Conecte sua impressora USB...
     ```
   - Clique no botão "🔍 Buscar Impressoras USB"

5. **✅ Verificar resultado positivo**
   - Se impressoras encontradas:
     ```
     ✅ 2 impressora(s) encontrada(s)!
     Selecione uma impressora na lista acima.
     ```
   - O select deve mostrar:
     ```
     Impressora USB: [Selecione uma impressora  ▼]
                      📄 Epson TM-T20
                      📄 Bematech MP-4200 TH
     ```

6. **❌ Verificar resultado negativo**
   - Se nenhuma impressora:
     ```
     ❌ Nenhuma impressora USB encontrada.

     Certifique-se de que:
     • A impressora está conectada via USB
     • A impressora está ligada
     • Os drivers estão instalados
     ```

7. **Selecionar impressora**
   - Clique no dropdown
   - Escolha "📄 Epson TM-T20" (ou outra disponível)

8. **Verificar console (DevTools)**
   - Abra DevTools (Ctrl+Shift+I ou F12)
   - No console deve aparecer:
     ```javascript
     Impressora selecionada: Epson TM-T20
     Vendor ID: 0x04b8
     Product ID: 0x0e15
     ```

9. **Salvar configuração**
   - Clique "Salvar Configuração"
   - Deve mostrar mensagem de sucesso

10. **Testar impressão (opcional)**
    - Se houver botão "Testar Impressão", clique
    - Impressora deve imprimir cupom de teste

---

### 3. Cenário de Teste: Impressora Desconhecida

**Objetivo:** Verificar comportamento quando fabricante não está no dicionário

#### Passos

1. Conecte uma impressora USB que **NÃO** esteja no dicionário
   - Exemplo: Citizen, Honeywell, etc.

2. Clique "🔍 Buscar Impressoras USB"

3. **Resultado esperado:**
   ```
   Impressora USB: [📄 Desconhecido Modelo 1A2B]
   ```

4. **Verifique console:**
   ```javascript
   Impressora selecionada: Desconhecido Modelo 1A2B
   Vendor ID: 0x????
   Product ID: 0x????
   ```

5. **Ainda deve funcionar!** (mesmo sem nome amigável)

---

### 4. Cenário de Teste: Múltiplas Impressoras

**Objetivo:** Verificar se lista mostra todas corretamente

#### Passos

1. Conecte 2 ou mais impressoras USB diferentes

2. Clique "🔍 Buscar Impressoras USB"

3. **Resultado esperado:**
   ```
   ✅ 3 impressora(s) encontrada(s)!
   ```

4. **Dropdown deve listar todas:**
   ```
   [Selecione uma impressora         ▼]
    📄 Epson TM-T20
    📄 Bematech MP-4200 TH
    📄 Elgin i9
   ```

5. Selecione cada uma e verifique console (IDs diferentes)

---

### 5. Cenário de Teste: Sem Impressoras

**Objetivo:** Mensagem de erro amigável

#### Passos

1. **Desconecte** todas as impressoras USB

2. Clique "🔍 Buscar Impressoras USB"

3. **Resultado esperado:**
   ```
   ❌ Nenhuma impressora USB encontrada.

   Certifique-se de que:
   • A impressora está conectada via USB
   • A impressora está ligada
   • Os drivers estão instalados
   ```

4. **Dropdown não deve mudar** (ainda "Clique em Buscar...")

---

### 6. Cenário de Teste: Re-buscar Impressoras

**Objetivo:** Atualizar lista ao buscar novamente

#### Passos

1. Busque impressoras (encontra 1)
   - Dropdown: `📄 Epson TM-T20`

2. **Conecte outra impressora** (agora tem 2)

3. Clique "🔍 Buscar Impressoras USB" novamente

4. **Resultado esperado:**
   ```
   ✅ 2 impressora(s) encontrada(s)!
   ```

5. **Dropdown deve atualizar:**
   ```
   [Selecione uma impressora         ▼]
    📄 Epson TM-T20
    📄 Bematech MP-4200 TH          ← NOVA!
   ```

---

## ✅ Checklist de Testes

### Interface Visual
- [ ] Dropdown aparece corretamente
- [ ] Botão "🔍 Buscar" tem ícone
- [ ] Dica (💡) aparece abaixo do botão
- [ ] Campos técnicos estão escondidos (não visíveis)
- [ ] Mensagens de erro/sucesso são claras

### Funcionalidade
- [ ] Busca detecta impressoras conectadas
- [ ] Nomes amigáveis aparecem corretamente
- [ ] Selecionar impressora preenche campos escondidos
- [ ] Salvar configuração funciona
- [ ] Impressão de teste funciona (se disponível)

### Casos Extremos
- [ ] Nenhuma impressora conectada → Erro amigável
- [ ] Múltiplas impressoras → Lista todas
- [ ] Impressora desconhecida → Mostra "Desconhecido Modelo"
- [ ] Re-buscar → Atualiza lista
- [ ] Desconectar impressora selecionada → Erro ao salvar

---

## 🐛 Bugs a Observar

### Bug Potencial #1: Select vazio após buscar sem impressoras
**Sintoma:** Dropdown fica vazio em vez de "Clique em Buscar..."

**Como verificar:**
1. Desconecte todas impressoras
2. Clique "🔍 Buscar"
3. Dropdown deve manter opção padrão

**Fix:** Verificar que `select.innerHTML` mantém option default

---

### Bug Potencial #2: Vendor/Product ID não preenchidos
**Sintoma:** Ao salvar, erro "Preencha Vendor ID e Product ID"

**Como verificar:**
1. Buscar impressoras
2. Selecionar uma
3. Abrir DevTools → Elements → Inspecionar hidden inputs
4. Devem ter value="0x04b8" etc

**Fix:** Verificar função `selectPrinter()` está funcionando

---

### Bug Potencial #3: IDs duplicados (múltiplas localizações)
**Sintoma:** Configurar "Cozinha" afeta "Balcão"

**Como verificar:**
1. Configurar impressora para "Cozinha"
2. Ir para "Balcão"
3. Buscar impressoras
4. Selecionar outra
5. "Cozinha" deve manter configuração original

**Fix:** Verificar que `location` está sendo usado corretamente em IDs

---

## 📊 Resultados Esperados

### Comparação v1.5.0 vs v1.6.0

| Critério | v1.5.0 (Antes) | v1.6.0 (Agora) |
|----------|----------------|----------------|
| **Campos visíveis** | 2 (Vendor ID, Product ID) | 1 (Dropdown) |
| **Usuário precisa saber** | IDs hexadecimais | Nome da impressora |
| **Cliques para configurar** | 4 (Buscar → Copiar ID1 → Copiar ID2 → Salvar) | 3 (Buscar → Selecionar → Salvar) |
| **Clareza da mensagem** | "Vendor ID: 1208" | "📄 Epson TM-T20" |
| **Suporte necessário** | Alto (usuários confusos) | Baixo (intuitivo) |

---

## 🎯 Critérios de Sucesso

### Mínimo Aceitável (MVP)
- ✅ Dropdown aparece e funciona
- ✅ Buscar retorna impressoras
- ✅ Nomes amigáveis aparecem
- ✅ Salvar funciona corretamente

### Ideal
- ✅ Mensagens de erro claras
- ✅ Suporta impressoras desconhecidas
- ✅ Funciona com múltiplas impressoras
- ✅ Re-buscar atualiza lista
- ✅ Console logs ajudam debug

---

## 🚨 Reportar Bugs

Se encontrar problemas, abra issue no GitHub com:

1. **Título:** "[v1.6.0] Descrição curta"
2. **Passos para reproduzir:** Lista numerada
3. **Resultado esperado:** O que deveria acontecer
4. **Resultado atual:** O que aconteceu
5. **Logs:** Console do DevTools (Ctrl+Shift+I)
6. **Sistema:** Windows/Linux/macOS + versão
7. **Impressora:** Marca e modelo

---

## ✅ Aprovação para Produção

**Antes de fazer release v1.6.0:**

- [ ] Todos testes passaram
- [ ] Nenhum bug crítico encontrado
- [ ] Testado em Windows (principal)
- [ ] Testado em Linux (opcional)
- [ ] Testado com ao menos 2 fabricantes diferentes
- [ ] Documentação atualizada (✅ CHANGELOG-v1.6.0.md)
- [ ] Git commit feito
- [ ] Tag v1.6.0 criada

**Comando para release:**
```bash
git add .
git commit -m "feat: Simplifica configuração de impressoras USB com nomes amigáveis

- Adiciona dicionário de fabricantes conhecidos (Epson, Bematech, Elgin, etc)
- Substitui campos técnicos por dropdown selecionável
- Mostra nomes amigáveis (ex: 'Epson TM-T20')
- Esconde Vendor/Product IDs (preenchidos automaticamente)
- Melhora mensagens de erro e sucesso

Resolve: 'Usuário não sabe o que é vendor ou product ID'

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>"

git tag v1.6.0
git push origin master --tags
```

---

**Data:** 06/03/2026
**Testador:** [SEU NOME]
**Status:** [ ] Aguardando teste | [ ] EM TESTE | [ ] ✅ APROVADO | [ ] ❌ REPROVADO
