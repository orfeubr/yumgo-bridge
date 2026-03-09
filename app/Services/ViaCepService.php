<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ViaCepService
{
    protected string $baseUrl = 'https://viacep.com.br/ws';

    /**
     * Busca endereço por CEP
     *
     * @param string $cep
     * @return array|null
     */
    public function buscarCep(string $cep): ?array
    {
        // Remove formatação do CEP
        $cep = preg_replace('/[^0-9]/', '', $cep);

        // Valida tamanho
        if (strlen($cep) !== 8) {
            return null;
        }

        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/{$cep}/json/");

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            // ViaCEP retorna {"erro": true} quando CEP não existe
            if (isset($data['erro']) && $data['erro'] === true) {
                return null;
            }

            // Retorna dados formatados
            return [
                'cep' => $data['cep'] ?? null,
                'logradouro' => $data['logradouro'] ?? null,
                'complemento' => $data['complemento'] ?? null,
                'bairro' => $data['bairro'] ?? null,
                'localidade' => $data['localidade'] ?? null,
                'uf' => $data['uf'] ?? null,
                'ibge' => $data['ibge'] ?? null,
                'gia' => $data['gia'] ?? null,
                'ddd' => $data['ddd'] ?? null,
                'siafi' => $data['siafi'] ?? null,
            ];
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar CEP', [
                'cep' => $cep,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Formata CEP para exibição (00000-000)
     *
     * @param string $cep
     * @return string
     */
    public static function formatarCep(string $cep): string
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);

        if (strlen($cep) === 8) {
            return substr($cep, 0, 5) . '-' . substr($cep, 5);
        }

        return $cep;
    }

    /**
     * Remove formatação do CEP
     *
     * @param string $cep
     * @return string
     */
    public static function limparCep(string $cep): string
    {
        return preg_replace('/[^0-9]/', '', $cep);
    }
}
