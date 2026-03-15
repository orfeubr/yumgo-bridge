<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LocationService;
use App\Models\Neighborhood;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Listar cidades de um estado
     * GET /api/location/cities/{state}
     */
    public function getCities(string $state = 'SP')
    {
        $cities = $this->locationService->getCitiesByState($state);

        return response()->json([
            'success' => true,
            'state' => $state,
            'total' => count($cities),
            'cities' => $cities,
        ]);
    }

    /**
     * Listar apenas cidades que têm bairros HABILITADOS no banco
     * GET /api/v1/location/enabled-cities
     */
    public function getEnabledCities()
    {
        $cities = Neighborhood::where('is_active', true)
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return response()->json([
            'success' => true,
            'total' => $cities->count(),
            'data' => $cities->values(),
        ]);
    }

    /**
     * Listar bairros de uma cidade (via API)
     * GET /api/location/neighborhoods/{city}
     */
    public function getNeighborhoods(string $city)
    {
        $neighborhoods = $this->locationService->getNeighborhoodsByCity($city);

        return response()->json([
            'success' => true,
            'city' => $city,
            'total' => count($neighborhoods),
            'neighborhoods' => $neighborhoods,
        ]);
    }

    /**
     * Listar bairros HABILITADOS de uma cidade (do banco)
     * GET /api/location/enabled-neighborhoods/{city}
     */
    public function getEnabledNeighborhoods(string $city)
    {
        $neighborhoods = Neighborhood::enabledByCity($city)->get();

        return response()->json([
            'success' => true,
            'city' => $city,
            'total' => $neighborhoods->count(),
            'data' => $neighborhoods->map(function ($n) {
                return [
                    'id' => $n->id,
                    'name' => $n->name,
                    'delivery_fee' => (float) $n->delivery_fee,
                    'delivery_time' => $n->delivery_time,
                    'minimum_order' => $n->minimum_order ? (float) $n->minimum_order : null,
                ];
            }),
        ]);
    }

    /**
     * Buscar endereço via CEP
     * GET /api/location/cep/{cep}
     */
    public function searchByCep(string $cep)
    {
        $address = $this->locationService->getAddressByCep($cep);

        if (!$address) {
            return response()->json([
                'success' => false,
                'error' => 'CEP não encontrado',
            ], 404);
        }

        // Se temos o bairro, buscar se está habilitado e a taxa
        $neighborhood = null;
        if ($address['neighborhood'] && $address['city']) {
            $neighborhoodData = Neighborhood::where('city', $address['city'])
                ->where('name', $address['neighborhood'])
                ->where('is_active', true)
                ->first();

            if ($neighborhoodData) {
                $neighborhood = [
                    'name' => $neighborhoodData->name,
                    'fee' => (float) $neighborhoodData->delivery_fee,
                    'time' => $neighborhoodData->delivery_time,
                    'available' => true,
                ];
            } else {
                $neighborhood = [
                    'name' => $address['neighborhood'],
                    'available' => false,
                    'message' => 'Não entregamos neste bairro',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'address' => $address,
            'neighborhood_info' => $neighborhood,
        ]);
    }

    /**
     * Importar bairros de uma cidade
     * POST /api/location/import-neighborhoods
     */
    public function importNeighborhoods(Request $request)
    {
        $request->validate([
            'city' => 'required|string',
            'state' => 'sometimes|string|max:2',
        ]);

        $city = $request->input('city');
        $state = $request->input('state', 'SP');

        $count = $this->locationService->importNeighborhoodsToDatabase($city, $state);

        return response()->json([
            'success' => true,
            'message' => "Bairros importados com sucesso!",
            'city' => $city,
            'total_imported' => $count,
        ]);
    }
}
