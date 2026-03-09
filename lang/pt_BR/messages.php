<?php

/**
 * Mensagens padronizadas da aplicação
 *
 * Usar: __('messages.order.not_found')
 *
 * Organizado por contexto:
 * - order: Mensagens relacionadas a pedidos
 * - customer: Mensagens relacionadas a clientes
 * - cashback: Mensagens relacionadas a cashback
 * - coupon: Mensagens relacionadas a cupons
 * - payment: Mensagens relacionadas a pagamentos
 * - auth: Mensagens relacionadas a autenticação
 * - validation: Mensagens customizadas de validação
 */

return [
    // Pedidos
    'order' => [
        'not_found' => 'Pedido não encontrado.',
        'created' => 'Pedido criado com sucesso!',
        'canceled' => 'Pedido cancelado com sucesso!',
        'only_pending_can_be_canceled' => 'Apenas pedidos pendentes podem ser cancelados.',
        'limit_reached' => 'O restaurante atingiu o limite de pedidos deste mês. Por favor, tente novamente mais tarde.',
        'already_processed' => 'Este pedido já foi processado.',
        'payment_method_invalid' => 'Método de pagamento inválido para este pedido.',
    ],

    // Clientes
    'customer' => [
        'not_found' => 'Cliente não encontrado neste restaurante.',
        'not_found_login_again' => 'Cliente não encontrado no restaurante. Por favor, faça login novamente.',
        'registered' => 'Cliente registrado com sucesso!',
        'profile_updated' => 'Perfil atualizado com sucesso!',
        'unauthorized_access' => 'Você não tem permissão para acessar este recurso.',
    ],

    // Cashback
    'cashback' => [
        'insufficient' => 'Saldo de cashback insuficiente.',
        'below_minimum' => 'O valor mínimo para usar cashback é :minimum.',
        'used_successfully' => 'Cashback usado com sucesso!',
        'earned' => 'Você ganhou :amount de cashback!',
    ],

    // Cupons
    'coupon' => [
        'invalid' => 'Cupom inválido ou expirado.',
        'exhausted' => 'Cupom esgotado.',
        'minimum_not_reached' => 'Valor mínimo do pedido não atingido. Mínimo: :minimum.',
        'limit_per_customer_reached' => 'Você já atingiu o limite de uso deste cupom.',
        'applied' => 'Cupom aplicado com sucesso!',
    ],

    // Pagamentos
    'payment' => [
        'not_found' => 'Pagamento não encontrado.',
        'approved' => 'Pagamento aprovado!',
        'pending' => 'Pagamento em análise.',
        'failed' => 'Falha ao processar pagamento.',
        'expired' => 'Este pedido expirou e não pode mais ser pago.',
        'already_paid' => 'Este pedido já foi pago.',
        'restaurant_closed' => 'O restaurante está fechado. Não é possível processar o pagamento agora.',
        'gateway_not_supported' => 'Gateway não suportado para pagamento direto.',
        'processing_error' => 'Erro ao processar pagamento. Por favor, tente novamente.',
    ],

    // Autenticação
    'auth' => [
        'invalid_credentials' => 'Celular/email ou senha incorretos.',
        'logout_success' => 'Logout realizado com sucesso!',
        'login_success' => 'Login realizado com sucesso!',
        'password_reset_sent' => 'Se o email existir, você receberá instruções para redefinir sua senha.',
        'unauthorized' => 'Não autorizado.',
        'token_invalid' => 'Token inválido ou expirado.',
    ],

    // Validações customizadas
    'validation' => [
        'neighborhood_not_found' => 'Não atendemos o bairro informado. Por favor, selecione um bairro válido.',
        'restaurant_closed' => 'O restaurante está fechado no momento. Pedidos não podem ser criados fora do horário de funcionamento.',
        'restaurant_closed_today' => 'O restaurante está fechado. Horário de funcionamento hoje: :open às :close.',
    ],

    // Genéricas
    'generic' => [
        'success' => 'Operação realizada com sucesso!',
        'error' => 'Erro ao processar solicitação. Por favor, tente novamente.',
        'not_found' => 'Recurso não encontrado.',
        'forbidden' => 'Acesso negado.',
        'validation_error' => 'Dados inválidos. Por favor, verifique e tente novamente.',
    ],
];
