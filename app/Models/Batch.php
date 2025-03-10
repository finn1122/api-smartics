<?php

namespace App\Models;

use App\Services\DocumentUrlService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'supplier_id',
        'quantity',
        'purchase_price',
        'sale_price',
        'purchase_date',
        'purchase_document_url',
    ];

    // Relación con el producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relación con el proveedor
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    /**
     * Accesor para purchase_document_url.
     * Usa el servicio para generar la URL completa.
     */
    public function getPurchaseDocumentUrlAttribute($value)
    {
        $documentUrlService = app(DocumentUrlService::class);
        return $documentUrlService->getFullUrl($value);
    }
}
