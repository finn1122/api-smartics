<?php

namespace App\Http\Controllers\Api\V1\Copomex;

use App\Http\Controllers\Controller;
use App\Models\PostalCode;
use App\Services\CopomexService;
use Illuminate\Support\Facades\Log;

class CopomexController extends Controller
{
    protected $service;

    public function __construct(CopomexService $service)
    {
        $this->service = $service;
    }

    public function syncState($stateName)
    {
        Log::info('syncState');
        try {
            $result = $this->service->syncStateData($stateName);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => "Datos de {$stateName} sincronizados correctamente",
                    'data' => $result
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al sincronizar datos'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPostalCodeInfo($postalCode)
    {
        $postalCode = PostalCode::with(['municipality.state', 'neighborhoods'])
            ->where('code', $postalCode)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $postalCode
        ]);
    }
}
