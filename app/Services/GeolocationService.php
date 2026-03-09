<?php

namespace App\Services;

class GeolocationService
{
    /**
     * Calcula distância entre dois pontos usando fórmula Haversine
     *
     * @param float $lat1 Latitude do ponto 1
     * @param float $lon1 Longitude do ponto 1
     * @param float $lat2 Latitude do ponto 2
     * @param float $lon2 Longitude do ponto 2
     * @return float Distância em quilômetros
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        // Raio da Terra em km
        $earthRadius = 6371;

        // Converter graus para radianos
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        // Diferenças
        $deltaLat = $lat2 - $lat1;
        $deltaLon = $lon2 - $lon1;

        // Fórmula Haversine
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return round($distance, 1); // Retorna com 1 casa decimal
    }

    /**
     * Busca taxa de entrega baseada na distância/zona
     *
     * @param \App\Models\Tenant $tenant
     * @param float $distance Distância em km
     * @return array ['fee' => float|null, 'is_free' => bool, 'zone_name' => string, 'delivers' => bool]
     */
    public static function getDeliveryFee($tenant, $distance): array
    {
        // ⚠️ VALIDAÇÃO: Se distância > 20km, não entrega (provavelmente outra cidade)
        if ($distance > 20) {
            return [
                'fee' => null,
                'is_free' => false,
                'zone_name' => 'Fora da área de entrega',
                'delivers' => false, // ← NOVO
            ];
        }

        // Inicializar tenancy
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }

        // Buscar zonas de entrega ordenadas por raio
        $zones = \DB::table('delivery_zones')
            ->where('is_active', true)
            ->orderBy('max_distance', 'asc')
            ->get();

        // Se não tem zonas configuradas, usar zona padrão (até 10km com taxa R$ 5)
        if ($zones->isEmpty()) {
            // ⚠️ REGRA: Se não configurou zonas, só entrega até 10km
            if ($distance > 10) {
                return [
                    'fee' => null,
                    'is_free' => false,
                    'zone_name' => 'Fora da área de cobertura',
                    'delivers' => false,
                ];
            }

            // Dentro de 10km, cobra R$ 5,00 padrão
            return [
                'fee' => 5.00,
                'is_free' => false,
                'zone_name' => 'Zona Padrão (até 10km)',
                'delivers' => true,
            ];
        }

        // Encontrar zona correspondente
        foreach ($zones as $zone) {
            if ($distance <= $zone->max_distance) {
                return [
                    'fee' => (float) $zone->delivery_fee,
                    'is_free' => $zone->delivery_fee == 0,
                    'zone_name' => $zone->name,
                    'delivers' => true,
                ];
            }
        }

        // Se passou de todas as zonas, não entrega
        return [
            'fee' => null,
            'is_free' => false,
            'zone_name' => 'Fora da área de entrega',
            'delivers' => false,
        ];
    }

    /**
     * Formata distância para exibição
     *
     * @param float $distance
     * @return string
     */
    public static function formatDistance($distance): string
    {
        if ($distance < 1) {
            return round($distance * 1000) . ' m';
        }

        return number_format($distance, 1, ',', '.') . ' km';
    }

    /**
     * Formata taxa de entrega para exibição
     *
     * @param array $deliveryInfo
     * @return string
     */
    public static function formatDeliveryFee($deliveryInfo): string
    {
        if ($deliveryInfo['fee'] === null) {
            return 'Não entrega';
        }

        if ($deliveryInfo['is_free']) {
            return 'Grátis';
        }

        return 'R$ ' . number_format($deliveryInfo['fee'], 2, ',', '.');
    }
}
