# 🔐 Configuração SSL Cloudflare - DeliveryPro

## ⚠️ Problema Atual

O domínio `pizzaria-bella.eliseus.com.br` está tentando redirecionar para HTTPS, mas não temos certificado SSL wildcard.

## ✅ Solução: Usar Cloudflare SSL

### **Opção 1: Origin Certificate (RECOMENDADO)** ⭐

A Cloudflare fornece certificado gratuito que cobre wildcards!

#### **Na Cloudflare:**

1. **SSL/TLS** → **Origin Server**
2. **Create Certificate**
3. Configurar:
   ```
   Private key type: RSA (2048)
   Hostnames:
   *.eliseus.com.br
   eliseus.com.br

   Certificate Validity: 15 years
   ```
4. **Create**
5. Copiar:
   - **Origin Certificate** (arquivo .pem)
   - **Private Key** (arquivo .key)

#### **No Servidor:**

```bash
# Criar diretórios
sudo mkdir -p /etc/ssl/cloudflare

# Salvar certificado
sudo nano /etc/ssl/cloudflare/cert.pem
# Cole o Origin Certificate

# Salvar chave privada
sudo nano /etc/ssl/cloudflare/key.pem
# Cole o Private Key

# Definir permissões
sudo chmod 600 /etc/ssl/cloudflare/*.pem
```

#### **Atualizar Nginx:**

```nginx
ssl_certificate /etc/ssl/cloudflare/cert.pem;
ssl_certificate_key /etc/ssl/cloudflare/key.pem;
```

---

### **Opção 2: Let's Encrypt Wildcard**

Requer validação DNS manual (mais complexo).

---

## 📋 Checklist

**Na Cloudflare:**
- [ ] SSL/TLS Mode: **Full (strict)**
- [ ] DNS Record: Type **A**, Name **\***, Proxy **Proxied** (laranja)
- [ ] Origin Certificate criado e copiado

**No Servidor:**
- [ ] Certificados salvos em /etc/ssl/cloudflare/
- [ ] Nginx atualizado com novo caminho dos certificados
- [ ] Nginx reload

## 🧪 Teste

Depois de configurar:

```bash
curl -I https://pizzaria-bella.eliseus.com.br
curl -I https://food.eliseus.com.br
```

Ambos devem retornar **200 OK** ou **302 Found**
