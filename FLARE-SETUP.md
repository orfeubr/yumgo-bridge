# 🔥 Flare - Setup Rápido (2 minutos)

## 💰 Por Que Flare?

- ✅ **€19/mês** (~R$ 110) - 4x mais barato que Sentry
- ✅ **10.000 erros/mês** vs 5.000 do Sentry
- ✅ Focado em Laravel (Spatie)
- ✅ Interface linda
- ✅ Zero configuração

## ⚡ Setup Rápido

### 1. Criar Conta (2 minutos)
```
https://flareapp.io/register
```

### 2. Criar Projeto
Nome: **DeliveryPro**

### 3. Copiar API Key
Dashboard → Settings → Projects → Copiar **Flare Key**

### 4. Configurar .env
```bash
FLARE_KEY=flare_your_api_key_here
```

### 5. Testar
```php
// Adicione em qualquer rota para testar
throw new \Exception('Test Flare - Multi-Tenant Error Tracking');
```

Acesse a rota, veja o erro no dashboard do Flare! 🎉

## 🏪 Multi-Tenant Automático

Flare **automaticamente** captura:
- ✅ Tenant ID
- ✅ Request completo
- ✅ SQL queries
- ✅ User autenticado
- ✅ Stack trace

**Sem configuração extra!**

## 📊 Dashboard

- Erros agrupados por tipo
- Filtra por tenant/ambiente
- Stack trace completo
- Timeline de eventos
- SQL queries executadas

## 🔔 Notificações

Configure em: **Settings → Notifications**
- Email
- Slack
- Discord
- Webhooks

## 💡 Dicas

1. **Ignore erros de validação** (economiza quota)
2. **Configure Slack** para notificações em tempo real
3. **Use filtros** por tenant para isolar problemas
4. **Revise semanalmente** os erros mais frequentes

## 📚 Documentação Completa

Leia: `/docs/FLARE-MONITORING.md`

---

**Custo:** €19/mês (~R$ 110)
**Economia vs Sentry:** R$ 60-400/mês
**Setup time:** 2 minutos
**Worth it?** MUITO! 🚀
