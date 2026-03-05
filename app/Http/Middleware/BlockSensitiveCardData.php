<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * 🔒 Middleware de Segurança PCI-DSS
 *
 * Bloqueia qualquer tentativa de enviar dados sensíveis de cartão
 * sem tokenização para o backend.
 *
 * ⚠️ NUNCA deve receber:
 * - card_number / number
 * - card_cvv / cvv
 * - card_expiry / exp_month / exp_year (sem tokenização)
 *
 * ✅ Deve receber apenas:
 * - card_id (token do Pagar.me)
 * - card_token (alternativa)
 */
class BlockSensitiveCardData
{
    /**
     * Lista de campos sensíveis que NÃO devem ser enviados
     */
    private const BLOCKED_FIELDS = [
        'card_number',
        'number',
        'cvv',
        'card_cvv',
        'security_code',
        'cvc',
        'card_cvc',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar se a requisição contém campos sensíveis
        $sensitiveData = $this->detectSensitiveData($request);

        if (!empty($sensitiveData)) {
            // 🚨 ALERTA DE SEGURANÇA
            Log::alert('🚨 TENTATIVA DE ENVIAR DADOS DE CARTÃO SEM TOKENIZAÇÃO BLOQUEADA!', [
                'url' => $request->url(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'tenant' => tenant() ? tenant()->id : null,
                'user_id' => $request->user() ? $request->user()->id : null,
                'sensitive_fields' => $sensitiveData,
                'timestamp' => now()->toDateTimeString(),
            ]);

            // Retornar erro 400 Bad Request
            return response()->json([
                'message' => 'Dados de cartão devem ser tokenizados no frontend.',
                'error' => 'SENSITIVE_DATA_NOT_ALLOWED',
                'fields_blocked' => $sensitiveData,
                'documentation' => 'https://docs.pagar.me/docs/tokenizacao-de-cartoes',
            ], 400);
        }

        // 🔐 VALIDAÇÃO EXTRA: Se é pagamento com cartão, DEVE ter token
        if ($this->isCardPaymentRequest($request)) {
            $hasToken = $request->has('card_id') || $request->has('card_token');

            if (!$hasToken) {
                Log::warning('⚠️ Tentativa de pagamento com cartão sem token', [
                    'url' => $request->url(),
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'message' => 'Pagamento com cartão requer tokenização.',
                    'error' => 'CARD_TOKEN_REQUIRED',
                    'hint' => 'Use Pagar.me JS SDK para tokenizar o cartão antes de enviar.',
                ], 400);
            }
        }

        return $next($request);
    }

    /**
     * Detecta se a requisição contém dados sensíveis de cartão
     *
     * @param Request $request
     * @return array - Lista de campos sensíveis encontrados
     */
    private function detectSensitiveData(Request $request): array
    {
        $foundFields = [];

        // Verificar no body da requisição
        foreach (self::BLOCKED_FIELDS as $field) {
            if ($request->has($field)) {
                $foundFields[] = $field;
            }
        }

        // Verificar em arrays aninhados (ex: data.card.number)
        $allInput = $request->all();
        $this->searchNestedArrays($allInput, $foundFields);

        return $foundFields;
    }

    /**
     * Busca recursivamente em arrays aninhados por campos sensíveis
     *
     * @param array $data
     * @param array &$foundFields
     * @return void
     */
    private function searchNestedArrays(array $data, array &$foundFields): void
    {
        foreach ($data as $key => $value) {
            // Se a chave é um campo bloqueado
            if (in_array($key, self::BLOCKED_FIELDS)) {
                $foundFields[] = $key;
            }

            // Se o valor é um array, buscar recursivamente
            if (is_array($value)) {
                $this->searchNestedArrays($value, $foundFields);
            }
        }
    }

    /**
     * Verifica se é uma requisição de pagamento com cartão
     *
     * @param Request $request
     * @return bool
     */
    private function isCardPaymentRequest(Request $request): bool
    {
        // Verificar se o método de pagamento é cartão
        $paymentMethod = $request->input('payment_method') ?? $request->input('method');

        return in_array($paymentMethod, ['credit_card', 'debit_card'], true);
    }
}
