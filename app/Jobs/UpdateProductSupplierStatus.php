<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateProductSupplierStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        Log::info('Iniciando actualización masiva de has_best_supplier');

        $totalUpdated = 0;

        Product::chunkById(200, function ($products) use (&$totalUpdated) {
            foreach ($products as $product) {
                // Usamos el método del modelo en lugar de duplicar lógica
                if ($product->updateSupplierStatus()) {
                    $totalUpdated++;
                }
            }

            Log::info("Procesado lote. Actualizados: {$totalUpdated} hasta ahora");
        });

        Log::info("Actualización completada. Total productos actualizados: {$totalUpdated}");
    }
}
