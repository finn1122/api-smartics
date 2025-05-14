<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CopomexRepository
{
    protected $baseUrl = 'https://api.copomex.com/query/';
    protected $apiKey;
    protected $useTestMode;

    public function __construct()
    {
        $this->apiKey = config('services.copomex.key');
        $this->useTestMode = config('services.copomex.use_test_mode');
    }

    public function getStates()
    {
        return $this->makeRequest('get_estados');
    }

    public function getMunicipalitiesByState(string $state)
    {
        $response = $this->makeRequest("get_municipio_por_estado/".urlencode($state));

        // Si la respuesta es un array de strings (formato actual)
        if (isset($response['response']['municipios'])) {
            return $response;
        }

        // Si la respuesta tiene otro formato
        return ['response' => ['municipios' => []]];
    }

    public function getPostalCodesByMunicipality(string $municipality)
    {
        return $this->makeRequest("get_cp_por_municipio/" . urlencode($municipality));
    }

    public function getPostalCodeInfo(string $postalCode)
    {
        return $this->makeRequest("info_cp/" . $postalCode);
    }

    protected function makeRequest(string $endpoint)
    {
        try {
            $url = $this->baseUrl . $endpoint;
            $requestData = [
                'mode' => $this->useTestMode ? 'TEST' : 'PRODUCTION',
                'base_url' => $this->baseUrl,
                'original_endpoint' => $endpoint,
                'final_url' => $url,
                'headers' => ['Accept' => 'application/json']
            ];

            // Agregar parámetro token=pruebas si está en modo test
            if ($this->useTestMode) {
                $url .= (str_contains($url, '?') ? '&' : '?') . 'token=pruebas';
                $requestData['final_url'] = $url;
                $requestData['test_mode'] = 'ACTIVE (token=pruebas)';
            } else {
                $requestData['test_mode'] = 'INACTIVE (using API key)';
            }

            $request = Http::withHeaders([
                'Accept' => 'application/json'
            ]);

            // Solo agregar Authorization si no está en modo test
            if (!$this->useTestMode) {
                $request = $request->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey
                ]);
                $requestData['headers']['Authorization'] = 'Bearer ***'; // Masked for logs
            }

            // Log completo de la solicitud
            Log::debug('Copomex API Request:', $requestData);

            $response = $request->get($url);

            // Log de la respuesta (sin el cuerpo completo para no saturar)
            Log::debug('Copomex API Response:', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'headers' => $response->headers()
            ]);

            if ($response->successful()) {
                return $response->json()['response'] ?? null;
            }

            Log::error('Copomex API Error:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Copomex Connection Error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
