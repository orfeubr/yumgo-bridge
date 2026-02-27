<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LocationService
{
    /**
     * Buscar todas as cidades de um estado via API IBGE
     */
    public function getCitiesByState(string $state = 'SP'): array
    {
        $cacheKey = "cities_{$state}";

        return Cache::remember($cacheKey, 60 * 24 * 30, function () use ($state) {
            try {
                $response = Http::timeout(10)->get(
                    "https://servicodados.ibge.gov.br/api/v1/localidades/estados/{$state}/municipios"
                );

                if ($response->successful()) {
                    return collect($response->json())
                        ->pluck('nome')
                        ->sort()
                        ->values()
                        ->toArray();
                }
            } catch (\Exception $e) {
                Log::error('Erro ao buscar cidades IBGE: ' . $e->getMessage());
            }

            return $this->getFallbackCities($state);
        });
    }

    /**
     * Buscar bairros de uma cidade
     * (Usa base hardcoded dos principais bairros)
     */
    public function getNeighborhoodsByCity(string $city, string $state = 'SP'): array
    {
        $cacheKey = "neighborhoods_{$state}_{$city}";

        return Cache::remember($cacheKey, 60 * 24 * 30, function () use ($city) {
            // Para MVP: usar base hardcoded dos principais bairros
            // Futuramente: integrar com API ou base de dados completa
            return $this->getHardcodedNeighborhoods($city);
        });
    }

    /**
     * Buscar endereço completo via CEP (ViaCEP)
     */
    public function getAddressByCep(string $cep): ?array
    {
        $cep = preg_replace('/\D/', '', $cep);

        if (strlen($cep) !== 8) {
            return null;
        }

        $cacheKey = "cep_{$cep}";

        return Cache::remember($cacheKey, 60 * 24 * 7, function () use ($cep) {
            try {
                $response = Http::timeout(5)->get("https://viacep.com.br/ws/{$cep}/json/");

                if ($response->successful() && !isset($response['erro'])) {
                    $data = $response->json();
                    return [
                        'cep' => $data['cep'] ?? $cep,
                        'street' => $data['logradouro'] ?? '',
                        'neighborhood' => $data['bairro'] ?? '',
                        'city' => $data['localidade'] ?? '',
                        'state' => $data['uf'] ?? '',
                        'complement' => $data['complemento'] ?? '',
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Erro ao buscar CEP: ' . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Base hardcoded de bairros principais para MVP
     * (Futuramente: substituir por API ou base completa)
     */
    private function getHardcodedNeighborhoods(string $city): array
    {
        $neighborhoods = [
            'São Paulo' => [
                'Centro', 'Vila Mariana', 'Moema', 'Jardim Paulista', 'Pinheiros',
                'Itaim Bibi', 'Brooklin', 'Vila Madalena', 'Perdizes', 'Consolação',
                'Liberdade', 'Bela Vista', 'Aclimação', 'Paraíso', 'Vila Olímpia',
                'Morumbi', 'Campo Belo', 'Saúde', 'Jabaquara', 'Santo Amaro',
                'Tatuapé', 'Mooca', 'Brás', 'Belém', 'Penha',
                'Vila Prudente', 'Ipiranga', 'Sacomã', 'Cursino', 'Vila da Saúde',
                'Lapa', 'Barra Funda', 'Pompeia', 'Butantã', 'Cidade Jardim',
                'Vila Andrade', 'Jardim Europa', 'Jardim América', 'Cerqueira César',
                'Higienópolis', 'Santa Cecília', 'República', 'Sé', 'Cambuci',
                'Santana', 'Tucuruvi', 'Vila Guilherme', 'Casa Verde', 'Limão',
            ],
            'Campinas' => [
                'Centro', 'Cambuí', 'Taquaral', 'Guanabara', 'Jardim das Paineiras',
                'Barão Geraldo', 'Nova Campinas', 'Jardim Chapadão', 'Jardim Proença',
                'Vila Industrial', 'Ponte Preta', 'Swift', 'Jardim Garcia',
                'Botafogo', 'Cambará', 'Parque Itália', 'Vila Brandina',
            ],
            'Santos' => [
                'Centro', 'Gonzaga', 'Boqueirão', 'Ponta da Praia', 'Embaré',
                'Aparecida', 'José Menino', 'Campo Grande', 'Macuco', 'Encruzilhada',
                'Vila Mathias', 'Estuário', 'Jabaquara', 'Monte Serrat',
            ],
            'Rio de Janeiro' => [
                'Centro', 'Copacabana', 'Ipanema', 'Leblon', 'Botafogo',
                'Flamengo', 'Laranjeiras', 'Tijuca', 'Vila Isabel', 'Grajaú',
                'Barra da Tijuca', 'Recreio', 'Jacarepaguá', 'Méier', 'Madureira',
                'Lapa', 'Santa Teresa', 'Catete', 'Glória', 'Urca',
                'Gávea', 'Jardim Botânico', 'Humaitá', 'Leme', 'Lagoa',
            ],
            'Belo Horizonte' => [
                'Centro', 'Savassi', 'Funcionários', 'Lourdes', 'Santo Agostinho',
                'Buritis', 'Estoril', 'Belvedere', 'Sion', 'Serra',
                'Santa Efigênia', 'Prado', 'Carlos Prates', 'Coração Eucarístico',
            ],
            'Louveira' => [
                'Centro', 'Jardim Bela Vista', 'Jardim Santo Antônio', 'Jardim São Luiz',
                'Jardim Monterrey', 'Vila Pasti', 'Parque Residencial Figueira',
                'Residencial Villa Suíça', 'Jardim Celeste', 'Jardim Novo Mundo',
                'Santo Antônio', 'Village Morro do Sol', 'Condomínio Palmeiras',
                'Jardim São Francisco', 'Vila São João', 'Recanto dos Pássaros',
                'Parque Dante Vialli', 'Jardim Primavera', 'Jardim Europa',
                'Vila Industrial', 'Parque Residencial Aquarius', 'Vila Maria',
            ],
            'Jundiaí' => [
                'Centro', 'Vila Arens', 'Anhangabaú', 'Jardim Botânico', 'Vila Virginia',
                'Ponte São João', 'Jardim Ana Estela', 'Eloy Chaves', 'Jardim do Lago',
                'Vila Hortolândia', 'Jardim Paulista', 'Jardim Alvorada', 'Jardim Caxambu',
                'Jardim das Tulipas', 'Jardim São Luiz', 'Jardim Bonfiglioli', 'Medeiros',
                'Parque da Represa', 'Vila Ruy Barbosa', 'Jardim Samambaia', 'Ponte de Campinas',
                'Jardim Tarumã', 'Jardim Santa Gertrudes', 'Vila Vianelo', 'Jardim América',
                'Jardim Novo Horizonte', 'Jardim Pitangueiras', 'Residencial Alpes de Jundiaí',
                'Vila Marlene', 'Jardim Santa Clara', 'Parque Residencial Jundiaí', 'Morada das Vinhas',
                'Jardim Bom Clima', 'Jardim Progresso', 'Vila Nambi', 'Jardim Flórida',
            ],
            'Vinhedo' => [
                'Centro', 'Capela', 'Mirante', 'Pinheiro', 'Santa Claudina',
                'Altos do Morumbi', 'Jardim Paulista', 'Loanda', 'Jardim São Luiz',
                'Vila João XXIII', 'Jardim Três Irmãos', 'Morada do Sol', 'Jardim das Palmeiras',
                'Jardim Florido', 'Nova Vinhedo', 'Jardim América', 'Village Campinas',
            ],
            'Itupeva' => [
                'Centro', 'Jardim Maracanã', 'Vila Esperança', 'Village Campinas',
                'Residencial Santa Giovana', 'Jardim Paraíso', 'Parque Residencial Itupeva',
                'Jardim Santa Rita', 'Jardim São Carlos', 'Jardim Novo Horizonte',
            ],
        ];

        return $neighborhoods[$city] ?? [];
    }

    /**
     * Cidades fallback caso API IBGE falhe
     */
    private function getFallbackCities(string $state): array
    {
        $cities = [
            'SP' => [
                'São Paulo', 'Campinas', 'Santos', 'São Bernardo do Campo',
                'Santo André', 'Osasco', 'Guarulhos', 'São José dos Campos',
                'Ribeirão Preto', 'Sorocaba', 'Piracicaba', 'Bauru',
            ],
            'RJ' => [
                'Rio de Janeiro', 'Niterói', 'Duque de Caxias', 'Nova Iguaçu',
                'São Gonçalo', 'Petrópolis', 'Volta Redonda', 'Campos dos Goytacazes',
            ],
            'MG' => [
                'Belo Horizonte', 'Contagem', 'Uberlândia', 'Juiz de Fora',
                'Betim', 'Montes Claros', 'Ribeirão das Neves', 'Uberaba',
            ],
        ];

        return $cities[$state] ?? [];
    }

    /**
     * Importar bairros de uma cidade para o banco de dados
     */
    public function importNeighborhoodsToDatabase(string $city, string $state = 'SP'): int
    {
        $neighborhoods = $this->getNeighborhoodsByCity($city, $state);
        $count = 0;

        foreach ($neighborhoods as $neighborhood) {
            \App\Models\Neighborhood::firstOrCreate([
                'city' => $city,
                'name' => $neighborhood,
            ], [
                'enabled' => false,
                'delivery_fee' => 5.00, // Valor padrão
                'delivery_time' => 30,   // Tempo padrão
            ]);
            $count++;
        }

        return $count;
    }
}
