# 🧪 Teste de NFC-e em Homologação

## 📋 O QUE VOCÊ PRECISA

### 1. Certificado Digital de Teste (Grátis)

**Opção A: Usar certificado fake/auto-assinado**
```bash
# Gerar certificado de teste (válido apenas para homologação)
openssl req -x509 -newkey rsa:2048 -keyout key.pem -out cert.pem -days 365 -nodes \
  -subj "/CN=Teste NFCe/O=Restaurante Teste/C=BR"

# Converter para PFX (formato aceito pela SEFAZ)
openssl pkcs12 -export -out certificado-teste.pfx -inkey key.pem -in cert.pem -password pass:teste123
```

**Opção B: Baixar certificado de teste da SEFAZ**
- Acesse: https://www.nfe.fazenda.gov.br/portal/principal.aspx
- Ambiente de Homologação → Downloads
- Certificado para testes

### 2. CSC de Teste (Código de Segurança)

**Para homologação, use valores padrão:**
- **CSC ID:** `000001`
- **CSC Token:** `CODIGO-CSC-CONTRIBUINTE-TESTE`

### 3. CNPJ

**Use um CNPJ de teste válido:**
- `11.111.111/0001-11` (formato de teste)
- Ou seu CNPJ real (funciona em homologação)

---

## 🚀 PASSO A PASSO

### **1. Configurar Tenant para Homologação**

```bash
php artisan tinker
```

```php
$tenant = \App\Models\Tenant::where('slug', 'marmitariadagi')->first();

// Configurar para HOMOLOGAÇÃO
$tenant->update([
    'cpf_cnpj' => '11111111000111', // CNPJ de teste
    'nfce_environment' => 'homologacao', // ← IMPORTANTE!
    'nfce_serie' => 1,
    'nfce_numero' => 1,

    // CSC de teste
    'csc_id' => '000001',
    'csc_token' => 'CODIGO-CSC-CONTRIBUINTE-TESTE',

    // Certificado (base64 do arquivo .pfx)
    // 'certificate_a1' => base64_encode(file_get_contents('/path/to/cert.pfx')),
    // 'certificate_password' => 'teste123',

    // Endereço fiscal
    'fiscal_address_street' => 'Rua Teste',
    'fiscal_address_number' => '123',
    'fiscal_address_neighborhood' => 'Centro',
    'fiscal_address_city' => 'São Paulo',
    'fiscal_address_state' => 'SP',
    'fiscal_address_zipcode' => '01001000',
    'fiscal_city_code' => '3550308', // Código IBGE de São Paulo
]);

echo "✅ Configurado para HOMOLOGAÇÃO!\n";
```

### **2. Rodar Teste**

```bash
php artisan nfce:test marmitariadagi
```

**O que vai acontecer:**
1. ✅ Verifica configurações
2. ✅ Cria pedido de teste
3. ✅ Tenta emitir NFC-e
4. ✅ Mostra resultado (sucesso ou erro)

---

## 📊 RESULTADO ESPERADO

### **✅ SUCESSO:**
```
🧪 TESTE DE EMISSÃO NFC-e - Ambiente de Homologação
═══════════════════════════════════════════════════

✅ Tenant: Marmitaria da Gi

📋 VERIFICANDO CONFIGURAÇÕES FISCAIS:
─────────────────────────────────────
✅ CNPJ
✅ Certificado A1
✅ CSC ID
✅ CSC Token
✅ Série NFC-e
✅ Número NFC-e
✅ Ambiente

📦 CRIANDO PEDIDO DE TESTE:
─────────────────────────────────────
✅ Customer: Cliente Teste NFC-e
✅ Pedido: #TEST-20260303123456
✅ Total: R$ 55,00

📄 EMITINDO NFC-e (isso pode levar alguns segundos...):
─────────────────────────────────────
✅ NFC-e EMITIDA COM SUCESSO!

Chave de Acesso: 35260311111111000111650010000000011234567890
Número: 1
Série: 1

📁 XML salvo em: storage/app/nfce/2026/03/35260311111111000111650010000000011234567890.xml

═══════════════════════════════════════════════════
🏁 TESTE CONCLUÍDO
```

### **❌ ERRO COMUM: Certificado não configurado**
```
❌ Certificado A1

⚠️  1 configuração(ões) faltando!

Deseja continuar mesmo assim? (sim/não) [sim]:
```

**Solução:** Configure o certificado (ver Passo 1)

### **❌ ERRO: NFePHP não instalado**
```
❌ SefazService não encontrado!
Execute: composer require nfephp-org/sped-nfe
```

**Solução:**
```bash
composer require nfephp-org/sped-nfe
```

---

## 🔍 VALIDAR XML GERADO

Após emitir com sucesso, você pode validar o XML:

```bash
# Ver XML gerado
cat storage/app/nfce/2026/03/*.xml

# Ou usar validador online da SEFAZ
# https://www.nfe.fazenda.gov.br/portal/validador.aspx
```

---

## 🌐 CONSULTAR NFC-e NO PORTAL DA SEFAZ

1. Acesse: https://www.nfe.fazenda.gov.br/portal/consultaRecaptcha.aspx?tipoConsulta=completa&tipoConteudo=XbSeqxE8pl8=
2. Informe a chave de acesso (44 dígitos)
3. Preencha o captcha
4. Consultar

**OBS:** Em homologação, pode não retornar dados (é normal).

---

## 📝 LOGS E DEBUG

Se der erro, veja os logs:

```bash
# Logs Laravel
tail -50 storage/logs/laravel-$(date +%Y-%m-%d).log

# Logs NFePHP (se habilitado)
tail -50 storage/logs/nfephp.log
```

---

## 🎯 PRÓXIMOS PASSOS

### **Para usar EM PRODUÇÃO:**

1. **Obter Certificado Digital Real:**
   - A1: R$ 150-250/ano
   - A3: R$ 250-400/ano
   - Comprar em: Serasa, Certisign, Soluti, Valid

2. **Obter CSC Real:**
   - Acessar portal da SEFAZ do seu estado
   - Gerar CSC para NFC-e
   - Guardar ID e Token

3. **Configurar Produção:**
```php
$tenant->update([
    'nfce_environment' => 'producao', // ← Mudar para produção
    'certificate_a1' => base64_encode(file_get_contents('certificado-real.pfx')),
    'certificate_password' => 'senha-real',
    'csc_id' => 'ID-REAL',
    'csc_token' => 'TOKEN-REAL',
]);
```

4. **Testar com Pedido Real:**
```bash
# Não usar comando de teste em produção!
# Emissão acontece automaticamente quando pedido é pago
```

---

## 🆘 AJUDA

**Problemas comuns:**

| Erro | Solução |
|------|---------|
| "Certificado inválido" | Usar certificado de teste ou real válido |
| "CSC inválido" | Usar CSC de teste padrão |
| "Rejeição 999" | Problema no XML, verificar logs |
| "Timeout" | SEFAZ indisponível, tentar depois |

**Documentação oficial:**
- NFePHP: https://github.com/nfephp-org/sped-nfe
- SEFAZ: https://www.nfe.fazenda.gov.br/

---

**💡 DICA:** Em homologação, erros são comuns e normais. O importante é validar que o XML está sendo gerado corretamente!
