<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Tributa AI Service - APENAS para Classificação Fiscal com IA
 *
 * Emissão de NFC-e é feita diretamente via SefazService
 */
class TributaAiService
{
    private string $baseUrl = 'https://api.tributaai.com.br/v1';
    private ?string $token;

    public function __construct()
    {
        $tenant = tenant();

        // Prioridade de token:
        // 1º - Token do restaurante (se configurado) - Apenas planos Enterprise
        // 2º - Token da plataforma (compartilhado) - Padrão para todos
        // 3º - Null (sem token) - Fallback NCM padrão
        $this->token = $tenant?->tributaai_token ?? config('services.tributaai.platform_token');
    }

    /**
     * Verificar se o serviço está disponível
     */
    public function isAvailable(): bool
    {
        return !empty($this->token);
    }

    /**
     * Classificar produto automaticamente com IA
     *
     * @param string $descricao Descrição do produto (ex: "Pizza Mussarela Grande")
     * @param string|null $categoria Categoria do produto (ex: "Alimentos")
     * @return array ['ncm' => '19059090', 'cfop' => '5405', 'cest' => null, 'descricao_ncm' => '...']
     */
    public function classificarProduto(string $descricao, ?string $categoria = null): array
    {
        if (!$this->isAvailable()) {
            throw new \Exception('Token Tributa AI não configurado. Configure em Configurações Fiscais.');
        }

        // Cache por 30 dias (classificação não muda)
        $cacheKey = 'tributaai:classificacao:' . md5($descricao . $categoria);

        return Cache::remember($cacheKey, 60 * 60 * 24 * 30, function () use ($descricao, $categoria) {
            return $this->fetchClassificacao($descricao, $categoria);
        });
    }

    /**
     * Buscar classificação na API do Tributa AI
     */
    private function fetchClassificacao(string $descricao, ?string $categoria): array
    {
        Log::info('🤖 Classificando produto com IA Tributa AI', [
            'descricao' => $descricao,
            'categoria' => $categoria,
        ]);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->baseUrl . '/classificacao/produto', [
                    'descricao' => $descricao,
                    'categoria' => $categoria,
                    'tipo_operacao' => 'venda', // Sempre venda para restaurantes
                    'finalidade' => 'consumidor_final',
                ]);

            if ($response->failed()) {
                $error = $response->json('message') ?? $response->body();

                Log::error('❌ Erro ao classificar produto', [
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                throw new \Exception('Erro na classificação: ' . $error);
            }

            $result = $response->json();

            Log::info('✅ Produto classificado com sucesso', [
                'ncm' => $result['ncm'] ?? null,
                'cfop' => $result['cfop'] ?? null,
                'descricao_ncm' => $result['descricao_ncm'] ?? null,
            ]);

            return [
                'ncm' => $result['ncm'] ?? '19059090', // Fallback
                'cfop' => $result['cfop'] ?? '5405', // Venda de alimentação
                'cest' => $result['cest'] ?? null,
                'descricao_ncm' => $result['descricao_ncm'] ?? null,
                'aliquota_icms' => $result['aliquota_icms'] ?? null,
                'confianca' => $result['confianca'] ?? null, // % de confiança da IA
            ];

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('❌ Exceção ao classificar produto', [
                'error' => $e->getMessage(),
            ]);

            // Retornar fallback em caso de erro
            return $this->getFallbackClassificacao();
        }
    }

    /**
     * Classificação fallback (padrão para alimentos)
     */
    private function getFallbackClassificacao(): array
    {
        return [
            'ncm' => '19059090', // Outros produtos de padaria
            'cfop' => '5405', // Venda de alimentação
            'cest' => null,
            'descricao_ncm' => 'Outros produtos de padaria, pastelaria e da indústria de bolachas e biscoitos',
            'aliquota_icms' => null,
            'confianca' => 0,
        ];
    }

    /**
     * Classificar múltiplos produtos em lote
     */
    public function classificarLote(array $produtos): array
    {
        if (!$this->isAvailable()) {
            throw new \Exception('Token Tributa AI não configurado');
        }

        $resultados = [];

        foreach ($produtos as $produto) {
            try {
                $descricao = is_array($produto) ? $produto['descricao'] : $produto;
                $categoria = is_array($produto) ? ($produto['categoria'] ?? null) : null;

                $resultados[] = [
                    'descricao' => $descricao,
                    'classificacao' => $this->classificarProduto($descricao, $categoria),
                ];
            } catch (\Exception $e) {
                $resultados[] = [
                    'descricao' => $descricao,
                    'erro' => $e->getMessage(),
                    'classificacao' => $this->getFallbackClassificacao(),
                ];
            }
        }

        return $resultados;
    }

    /**
     * Validar NCM
     */
    public function validarNCM(string $ncm): bool
    {
        // NCM deve ter 8 dígitos numéricos
        return preg_match('/^\d{8}$/', $ncm) === 1;
    }

    /**
     * Validar CFOP
     */
    public function validarCFOP(string $cfop): bool
    {
        // CFOP deve ter 4 dígitos numéricos
        return preg_match('/^\d{4}$/', $cfop) === 1;
    }

    /**
     * Buscar informações sobre um NCM específico
     */
    public function buscarInfoNCM(string $ncm): ?array
    {
        if (!$this->isAvailable() || !$this->validarNCM($ncm)) {
            return null;
        }

        $cacheKey = 'tributaai:ncm:' . $ncm;

        return Cache::remember($cacheKey, 60 * 60 * 24 * 90, function () use ($ncm) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->token,
                    ])
                    ->get($this->baseUrl . '/ncm/' . $ncm);

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                Log::warning('Erro ao buscar info NCM', ['ncm' => $ncm, 'error' => $e->getMessage()]);
            }

            return null;
        });
    }
}
