<?php

namespace App\Http\Controllers\Api\V1\Slider;

use App\Http\Controllers\Controller;
use App\Http\Resources\SliderResource;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SliderController extends Controller
{
    /**
     * Display a listing of active sliders.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllActiveSliders()
    {
        try {
            $sliders = Slider::with('type')
                ->where('active', true)
                ->orderBy('order')
                ->get();

            return response()->json([
                'success' => true,
                'data' => SliderResource::collection($sliders)
            ]);
        }catch (\Exception $e){
            Log::debug($e->getMessage());
        }

    }
}
