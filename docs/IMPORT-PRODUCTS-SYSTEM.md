# 📦 Sistema de Importação de Produtos - YumGo

## 🎯 Visão Geral

Sistema completo para importação em lote de produtos via planilha Excel/CSV, incluindo:
- ✅ Download automático de imagens
- ✅ Geração de thumbnails (400x400px)
- ✅ Criação automática de categorias
- ✅ Importação de variações (tamanhos)
- ✅ Importação de adicionais
- ✅ Validações robustas
- ✅ Relatório detalhado

---

## 📋 Formato da Planilha

### Colunas (8 no total)

| Coluna | Obrigatória | Tipo | Formato | Exemplo |
|--------|-------------|------|---------|---------|
| **categoria** | ✅ Sim | Texto | Nome da categoria | Pizzas |
| **nome** | ✅ Sim | Texto | Nome do produto | Pizza de Mussarela |
| **descricao** | ❌ Não | Texto | Descrição completa | Molho de tomate, mussarela, orégano |
| **preco** | ✅ Sim | Número | Use vírgula ou ponto | 35,00 ou 35.00 |
| **variacoes** | ❌ Não | Texto | Nome:Preço,Nome:Preço | P:30.00,M:35.00,G:45.00 |
| **adicionais** | ❌ Não | Texto | Nome:Preço,Nome:Preço | Borda:5.00,Catupiry:3.00 |
| **foto_url** | ❌ Não | URL | URL completa da imagem | https://exemplo.com/pizza.jpg |
| **ativo** | ❌ Não | Texto | "sim" ou "não" | sim |

### Exemplos de Linhas

**⚠️ IMPORTANTE:** Use **aspas duplas** em campos que contêm vírgulas!

```csv
categoria,nome,descricao,preco,variacoes,adicionais,foto_url,ativo
Pizzas,Pizza Mussarela,Molho de tomate mussarela orégano,35.00,"P:30.00,M:35.00,G:45.00","Borda:5.00,Catupiry:3.00",https://exemplo.com/pizza.jpg,sim
Bebidas,Coca-Cola 2L,Refrigerante sabor cola,10.00,,,https://exemplo.com/coca.jpg,sim
Marmitex,Marmitex Executivo,Arroz feijão carne salada,25.00,"P:20.00,M:25.00,G:30.00","Frango:0.00,Carne:5.00,Peixe:8.00",,sim
```

**Nota:** As aspas duplas (`"`) são necessárias porque as variações e adicionais usam vírgula como separador interno.

---

## 🖼️ Sistema de Imagens

### Download Automático

**Como funciona:**
1. Cliente informa URL da imagem na coluna `foto_url`
2. YumGo faz download da imagem via HTTP
3. Salva no storage: `storage/app/public/products/{tenant-id}/{slug}-{timestamp}.jpg`
4. Gera thumbnail automaticamente: `storage/app/public/products/{tenant-id}/thumbs/{slug}-{timestamp}.jpg`

**Formatos aceitos:**
- JPG/JPEG
- PNG
- GIF
- WEBP

**Validações:**
- URL válida (http/https)
- Resposta HTTP 200
- Tamanho máximo: Ilimitado (servidor decide)
- Thumbnail: 400x400px (fit cover, mantém proporção)

**Onde buscar URLs de fotos?**
- iFood (inspecionar elemento → copiar URL da imagem)
- Dropbox público
- Google Drive (link público)
- Imgur
- Servidor próprio

---

## 🚀 Como Usar

### Opção 1: Painel do Restaurante (Interface Gráfica)

1. **Acesse:** `https://{seu-slug}.yumgo.com.br/painel/import-products`
2. **Baixe o template:** Clique em "Baixar Modelo Excel"
3. **Preencha a planilha** com seus produtos
4. **Faça upload:** Arraste o arquivo ou clique para selecionar
5. **Clique em "Importar"** e aguarde
6. **Revise o relatório:** Veja o que foi importado e possíveis erros

### Opção 2: Linha de Comando (Via SSH)

```bash
# Importar produtos
php artisan products:import marmitaria-gi /caminho/planilha.xlsx

# Modo teste (valida sem salvar)
php artisan products:import marmitaria-gi /caminho/planilha.xlsx --test
```

---

## 📊 Relatório de Importação

Após a importação, você recebe um relatório detalhado:

```
📊 RELATÓRIO DE IMPORTAÇÃO
═══════════════════════════
✅ Produtos importados: 25
📁 Categorias criadas: 3
📦 Produtos criados: 25
🔢 Variações criadas: 60
➕ Adicionais criados: 45

⚠️ AVISOS (2):
  • Linha 10: Erro ao baixar foto - HTTP 404
  • Linha 15: Formato inválido de variação 'P-30.00'

🎉 Importação concluída com sucesso!
```

---

## ⚠️ Regras Importantes

### Categorias
- Se a categoria não existir, ela é **criada automaticamente**
- Nome exato: "Pizzas" ≠ "pizzas" (cria 2 categorias diferentes)
- Slug gerado automaticamente (ex: "Pizzas" → "pizzas")

### Produtos
- **Duplicação permitida:** Se já existe produto com o mesmo nome, um novo é criado
- Slug único: Laravel adiciona sufixo (ex: "pizza-mussarela-1", "pizza-mussarela-2")
- Preço: Sempre positivo (validação automática)

### Variações
- **Formato:** `Nome:Preço,Nome:Preço` (separado por vírgula)
- **No CSV:** Use aspas duplas → `"P:30.00,M:35.00,G:45.00"`
- **Preço:** Deve ser maior que zero
- **Exemplo correto:** `"P:30.00,M:35.00,G:45.00"`
- **Exemplo errado:** `P:30.00;M:35.00;G:45.00` (ponto e vírgula não funciona)
- Se formato inválido: Linha é pulada, aviso no relatório

### Adicionais
- **Formato:** `Nome:Preço,Nome:Preço` (separado por vírgula)
- **No CSV:** Use aspas duplas → `"Borda:5.00,Catupiry:3.00"`
- **Preço:** Pode ser zero (ex: "Sem cebola:0.00")
- **Exemplo correto:** `"Borda Catupiry:8.00,Borda Cheddar:7.00"`
- **Exemplo errado:** `Borda:5.00;Catupiry:3.00` (ponto e vírgula não funciona)
- Se formato inválido: Linha é pulada, aviso no relatório

### Fotos
- **URL obrigatória** (não aceita upload direto na planilha)
- **Download automático** em background
- **Thumbnail gerado** automaticamente (400x400px)
- Se falhar: Produto é criado sem foto, aviso no relatório

**⚠️ URLs de Fotos - IMPORTANTE:**

**URLs que FUNCIONAM:**
- ✅ Dropbox público
- ✅ Google Drive (compartilhamento público)
- ✅ Imgur
- ✅ Unsplash
- ✅ Servidor próprio (deve permitir acesso externo)

**URLs que NÃO FUNCIONAM:**
- ❌ iFood (bloqueado por CORS/autenticação - HTTP 403)
- ❌ URLs privadas que exigem login
- ❌ URLs com proteção CAPTCHA

**Como conseguir URLs do iFood:**
1. Abra o produto no iFood
2. Clique com botão direito na imagem
3. "Salvar imagem como..." e salve no seu computador
4. Faça upload para Imgur ou Dropbox
5. Use a URL pública gerada

---

## 🔧 Solução de Problemas

### "Erro ao baixar foto"
**Causa:** URL inválida, foto privada, servidor offline
**Solução:** Verifique se a URL abre no navegador. Use links públicos.

### "Formato inválido de variação"
**Causa:** Formato incorreto (ex: "P-30" em vez de "P:30.00")
**Solução:** Use formato `Nome:Preço` com dois pontos e ponto decimal.

### "Categoria é obrigatória"
**Causa:** Célula da coluna "categoria" está vazia
**Solução:** Preencha o nome da categoria.

### "Preço deve ser maior que zero"
**Causa:** Preço vazio ou negativo
**Solução:** Informe um preço válido (ex: 10.00 ou 10,00).

### "Produto não aparece no painel"
**Causa:** Importação bem-sucedida mas produto inativo
**Solução:** Edite o produto e ative-o, ou coloque "sim" na coluna "ativo".

---

## 📁 Estrutura de Arquivos

```
app/
├── Console/Commands/
│   └── ImportProducts.php          # Comando Artisan
├── Services/
│   └── ProductImportService.php    # Lógica de importação
├── Http/Controllers/
│   └── TemplateController.php      # Download do template
└── Filament/Restaurant/Pages/
    └── ImportProducts.php          # Interface gráfica

resources/views/filament/restaurant/
├── pages/
│   └── import-products.blade.php
└── components/
    └── import-instructions.blade.php

storage/app/public/products/
├── {tenant-id}/
│   ├── pizza-mussarela-1234567890.jpg
│   └── thumbs/
│       └── pizza-mussarela-1234567890.jpg
└── {tenant-id-2}/
    └── ...
```

---

## 🎓 Casos de Uso

### Pizza com Tamanhos e Bordas

```csv
categoria,nome,descricao,preco,variacoes,adicionais,foto_url,ativo
Pizzas,Calabresa,Molho calabresa cebola,45.00,"P:35.00,M:45.00,G:55.00","Borda Catupiry:8.00,Borda Cheddar:7.00",https://exemplo.com/calabresa.jpg,sim
```

**Observação:** Note as aspas duplas em `"P:35.00,M:45.00,G:55.00"` e `"Borda Catupiry:8.00,Borda Cheddar:7.00"`

**Resultado:**
- Produto: "Calabresa" (R$ 45,00 base)
- Variações: P (R$ 35), M (R$ 45), G (R$ 55)
- Adicionais: Borda Catupiry (R$ 8), Borda Cheddar (R$ 7)

### Marmitex com Proteínas

```csv
categoria,nome,descricao,preco,variacoes,adicionais,foto_url,ativo
Marmitex,Executivo,Arroz feijão salada,25.00,"P:20.00,G:30.00","Frango:0.00,Carne:5.00,Peixe:8.00",,sim
```

**Observação:** Campo `foto_url` está vazio (dois vírgulas seguidas `,,`), mas ainda funciona

**Resultado:**
- Produto: "Executivo" (R$ 25,00 base)
- Variações: P (R$ 20), G (R$ 30)
- Adicionais: Frango (grátis), Carne (+R$ 5), Peixe (+R$ 8)

### Bebida Simples

```csv
categoria,nome,descricao,preco,variacoes,adicionais,foto_url,ativo
Bebidas,Suco de Laranja 500ml,Suco natural,8.00,,,https://exemplo.com/suco.jpg,sim
```

**Resultado:**
- Produto simples sem variações
- Preço fixo: R$ 8,00

---

## 🔒 Segurança

### Multi-Tenant
- Cada restaurante tem sua própria pasta de imagens
- Impossível acessar imagens de outro restaurante
- Schema isolado no PostgreSQL

### Validações
- URLs são validadas antes do download
- Timeout de 30s por imagem (evita travamento)
- Extensões de arquivo permitidas (JPG, PNG, GIF, WEBP)
- Tamanho de upload limitado a 10MB

### Permissões
- Apenas usuários autenticados do restaurante
- Arquivos salvos em `storage/app/public` (acessível via `/storage/...`)

---

## 📈 Performance

### Otimizações
- Download de imagens em paralelo (futuro)
- Processamento em lote (100 produtos por vez)
- Validação prévia antes de salvar no banco
- Geração de thumbnails em background

### Limites Recomendados
- Máximo 500 produtos por arquivo
- Acima de 500: dividir em múltiplos arquivos
- Tempo estimado: 1-2 minutos para 100 produtos

---

## 🛠️ Manutenção

### Limpar Arquivos de Importação

```bash
# Remove arquivos temporários de importação
php artisan storage:cleanup imports
```

### Regenerar Thumbnails

```bash
# Regenera thumbnails de todos os produtos
php artisan products:regenerate-thumbnails {tenant-slug}
```

---

## 📞 Suporte

**Dúvidas?**
- 📧 Email: suporte@yumgo.com.br
- 💬 WhatsApp: (11) 9xxxx-xxxx
- 📚 Documentação: https://docs.yumgo.com.br

**Bugs?**
- Reporte em: https://github.com/orfeubr/yumgo/issues

---

**Última atualização:** 07/03/2026
**Versão:** 1.0.0
