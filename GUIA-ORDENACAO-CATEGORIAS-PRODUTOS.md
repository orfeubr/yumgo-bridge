# 📋 Guia: Categorias e Ordenação de Produtos

**Restaurante:** Todos
**Painel:** https://{seu-restaurante}.yumgo.com.br/painel

---

## 📂 1. Gerenciar Categorias

### 🔍 Onde encontrar:
**Menu Lateral → Categorias**

### ✏️ O que você pode fazer:

**Criar Nova Categoria:**
1. Clique em **"Nova Categoria"**
2. Preencha:
   - **Nome** - Ex: "Cervejas", "Porções", "Marmitas"
   - **Descrição** (opcional)
   - **Imagem** (opcional) - Ícone da categoria
   - **Ordem de Exibição** - Número que define a posição (0, 10, 20, 30...)
   - **Categoria Ativa** - Ativado/Desativado
3. Salvar

**Editar Categoria:**
- Clique no ícone de lápis (✏️) na linha da categoria
- Modifique os dados
- Salvar

**Deletar Categoria:**
- Clique no ícone de lixeira (🗑️)
- Confirme a exclusão
- ⚠️ **Atenção:** Só pode deletar se não houver produtos vinculados

---

## 🔢 2. Ordenar Categorias

### Como funciona:
- As categorias são exibidas **em ordem crescente** do campo **"Ordem de Exibição"**
- Quanto **menor** o número, **mais no topo** aparece

### Exemplo prático:

| Categoria | Ordem | Posição na Home |
|-----------|-------|-----------------|
| Marmitas | 10 | 1º (topo) |
| Porções | 20 | 2º |
| Cervejas | 30 | 3º |
| Petiscos | 40 | 4º |
| Sobremesas | 50 | 5º (fim) |

### Como ordenar:

1. Acesse **Categorias**
2. Clique em **"Ordem"** no cabeçalho da tabela para ordenar
3. Edite cada categoria e ajuste o número no campo **"Ordem de Exibição"**

**Dica:** Use números com espaçamento (10, 20, 30) para facilitar inserir categorias entre outras no futuro.

---

## 📦 3. Ordenar Produtos

### 🔍 Onde encontrar:
**Menu Lateral → Produtos**

### 🔢 Como ordenar:

**Dentro de cada categoria**, os produtos são exibidos pela **"Ordem"** (campo numérico).

**Exemplo:**

**Categoria: Marmitas**
| Produto | Ordem | Posição |
|---------|-------|---------|
| Marmita Executiva | 10 | 1º |
| Marmita Fit | 20 | 2º |
| Marmita Feijoada | 30 | 3º |

**Categoria: Cervejas**
| Produto | Ordem | Posição |
|---------|-------|---------|
| Heineken | 10 | 1º |
| Brahma | 20 | 2º |
| Skol | 30 | 3º |

### Como ajustar:

1. Acesse **Produtos**
2. Edite o produto (ícone ✏️)
3. Role até a seção **"Organização e Disponibilidade"**
4. Ajuste o campo **"Ordem de Exibição"**
5. Salvar

---

## 🎯 4. Exemplo Completo: Mostrar Comidas Primeiro

**Objetivo:** Mostrar Marmitas e Porções no topo, depois Bebidas

### Passo 1: Ordenar Categorias

1. Acesse **Categorias**
2. Edite cada categoria e defina a ordem:

```
Marmitas        → Ordem: 10
Porções         → Ordem: 20
Petiscos        → Ordem: 30
Cervejas        → Ordem: 40
Água            → Ordem: 50
Sucos Naturais  → Ordem: 60
Cachaças        → Ordem: 70
Sobremesas      → Ordem: 80
```

### Passo 2: Ordenar Produtos Dentro de Cada Categoria

**Exemplo: Categoria Marmitas**

1. Acesse **Produtos**
2. Filtre por categoria "Marmitas"
3. Edite cada produto:

```
Marmita Executiva           → Ordem: 10
Marmita de Frango Grelhado  → Ordem: 20
Marmita de Carne de Panela  → Ordem: 30
Marmita Fit                 → Ordem: 40
Marmita de Feijoada         → Ordem: 50
```

**Resultado na Home:**
```
📂 Marmitas
   - Marmita Executiva
   - Marmita de Frango Grelhado
   - Marmita de Carne de Panela
   - Marmita Fit
   - Marmita de Feijoada

📂 Porções
   - (seus produtos em ordem)

📂 Petiscos
   - (seus produtos em ordem)

📂 Cervejas
   - (seus produtos em ordem)
```

---

## 🚀 Acesso Rápido

| Item | Menu | URL |
|------|------|-----|
| **Categorias** | Menu → Categorias | `/painel/categorias` |
| **Produtos** | Menu → Produtos | `/painel/produtos` |
| **Ordenar** | Editar → Campo "Ordem de Exibição" | - |

---

## 💡 Dicas Importantes

1. **Números Espaçados:** Use 10, 20, 30... para facilitar inserções futuras
2. **Categorias Inativas:** Desative categorias sazonais em vez de deletar
3. **Produtos Inativos:** Desative produtos temporariamente indisponíveis
4. **Ordenação Automática:** A home sempre respeita a ordem configurada
5. **Cardápio Semanal:** Se usar cardápio semanal, a ordem continua valendo dentro dos produtos do dia

---

## ❓ FAQ

**P: Posso ter dois produtos com a mesma ordem?**
R: Sim, mas eles serão ordenados alfabeticamente entre si.

**P: Preciso reordenar todos os produtos manualmente?**
R: Sim, mas só uma vez. Depois, novos produtos podem ser inseridos com a ordem adequada.

**P: A ordenação afeta o cardápio semanal?**
R: Sim! No cardápio semanal, os produtos aparecem na ordem configurada aqui.

**P: Posso reordenar arrastando?**
R: Não ainda. Use o campo numérico "Ordem de Exibição".

---

**✨ Bom trabalho organizando seu cardápio!**
