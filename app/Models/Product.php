<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cva_key',
        'sku',
        'warranty',
        'brand_id',
        'product_type',
        'active',
    ];

    protected $casts = ['has_best_supplier' => 'boolean'];

    /**
     * Obtener la marca asociada al producto.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Obtener el grupo asociado al producto.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Obtener los proveedores asociados a este producto.
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'inventories', 'product_id', 'supplier_id')
            ->withPivot('quantity', 'purchase_date');
    }
    // En Product.php
    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    // Relación con los datos de proveedores externos
    public function externalProductData()
    {
        return $this->hasMany(ExternalProductData::class);
    }

    public function gallery()
    {
        return $this->hasMany(Gallery::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tags');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    // Obtener la ruta completa del producto
    public function getProductPath()
    {
        // Obtener la categoría más específica del producto
        $category = $this->categories()->orderBy('parent_id', 'desc')->first(); // Tomar la categoría con el parent_id más alto
        return $category ? $category->getFullPath() . '/' . $this->name : $this->name;
    }

    public function scopeWithBestSupplier($query)
    {
        return $query->whereHas('externalProductData', function($q) {
            $q->where('price', '>', 0)
                ->where('sale_price', '>', 0)
                ->where('new_sale_price', '>', 0)
                ->orderByRaw('quantity > 0 DESC, price ASC')
                ->limit(1);
        });
    }

    public function updateSupplierStatus(): bool
    {
        $newStatus = $this->calculateBestSupplier();

        // Solo actualiza si cambió el estado
        if ($this->has_best_supplier !== $newStatus) {
            $this->has_best_supplier = $newStatus;
            $this->save();
            return true;
        }

        return false;
    }

    private function calculateBestSupplier()
    {
        $bestSupplier = $this->externalProductData()
            ->where('price', '>', 0)
            ->where('sale_price', '>', 0)
            ->where('new_sale_price', '>', 0)
            ->orderByRaw('CASE WHEN quantity > 0 THEN 0 ELSE 1 END')
            ->orderBy('price')
            ->first();

        return !is_null($bestSupplier);
    }

    public function getMainCategory()
    {
        // Puedes ajustar esta lógica según cómo determines la categoría principal
        return $this->categories->sortByDesc(function($category) {
            return $category->depth; // Asumiendo que usas NodeTrait que proporciona depth
        })->first();
    }
}
