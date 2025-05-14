<?php

namespace App\Services;

use App\Repositories\CopomexRepository;
use App\Models\State;
use App\Models\Municipality;
use App\Models\PostalCode;
use App\Models\Neighborhood;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CopomexService
{
    protected $repository;

    public function __construct(CopomexRepository $repository)
    {
        $this->repository = $repository;
    }

    public function syncStateData(string $stateName)
    {
        Log::info('syncStateData');
        return DB::transaction(function () use ($stateName) {
            // 1. Obtener y guardar el estado si no existe
            $state = $this->findOrCreateState($stateName);

            Log::debug($state);

            // 2. Obtener y guardar municipios
            $municipalities = $this->syncMunicipalities($state);

            // 3. Para cada municipio, obtener códigos postales
            foreach ($municipalities as $municipality) {
                $this->syncPostalCodes($municipality);
            }

            return $state;
        });
    }

    protected function findOrCreateState(string $stateName)
    {
        $state = State::where('name', $stateName)->first();

        if (!$state) {
            $apiData = $this->repository->getStates();
            $stateData = collect($apiData['estados'] ?? [])
                ->firstWhere('nombre', $stateName);

            $state = State::create([
                'name' => $stateName,
                'short_name' => $stateData['abreviatura'] ?? substr($stateName, 0, 3),
                'c_estado' => $stateData['clave'] ?? ''
            ]);
        }

        return $state;
    }

    protected function syncMunicipalities(State $state)
    {
        $apiData = $this->repository->getMunicipalitiesByState($state->name);
        $municipalities = [];

        // Verificar si la respuesta tiene la estructura esperada
        if (!isset($apiData['response']['municipios'])) {
            Log::error('Estructura de respuesta inesperada', ['response' => $apiData]);
            return [];
        }

        foreach ($apiData['response']['municipios'] as $municipioName) {
            try {
                // Generar una clave única basada en el nombre si no existe
                $clave = substr(str_slug($municipioName), 0, 10);

                $municipalities[] = Municipality::firstOrCreate([
                    'state_id' => $state->id,
                    'name' => $municipioName
                ], [
                    'c_municipio' => $clave
                ]);
            } catch (\Exception $e) {
                Log::error('Error al crear municipio', [
                    'error' => $e->getMessage(),
                    'municipio' => $municipioName
                ]);
                continue;
            }
        }

        return $municipalities;
    }

    protected function syncPostalCodes(Municipality $municipality)
    {
        $apiData = $this->repository->getPostalCodesByMunicipality($municipality->name);

        foreach ($apiData['cp'] ?? [] as $cp) {
            $postalCode = PostalCode::firstOrCreate([
                'code' => $cp,
                'municipality_id' => $municipality->id
            ]);

            $this->syncNeighborhoods($postalCode);
        }
    }

    protected function syncNeighborhoods(PostalCode $postalCode)
    {
        $apiData = $this->repository->getPostalCodeInfo($postalCode->code);

        if (!isset($apiData['colonias'])) return;

        foreach ($apiData['colonias'] as $colonia) {
            Neighborhood::firstOrCreate([
                'postal_code_id' => $postalCode->id,
                'name' => $colonia['nombre'],
                'c_settlement_type' => $colonia['clave_asentamiento']
            ], [
                'settlement_type' => $colonia['tipo_asentamiento'],
                'zone_type' => $apiData['tipo_zona'] ?? null
            ]);
        }
    }
}
