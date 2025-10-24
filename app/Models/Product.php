<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'franchise_id',
        'name',
        'description',
        'category',
        'unit_price',
        'stock',
        'status',
        'sku',
        'image',
        'cost_price',
        'weight',
        'dimensions',
        'minimum_stock',
        'attributes',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'cost_price' => 'float',
        'weight' => 'float',
        'attributes' => 'array',
    ];

    /**
     * Units that stock this product.
     */
    public function units(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'unit_product_inventories')
            ->withPivot(['quantity', 'reorder_level'])
            ->using(Inventory::class)
            ->withTimestamps();
    }

    /**
     * Get the franchise that owns this product
     */
    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    /**
     * Check if product is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if product is low in stock
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->minimum_stock;
    }

    /**
     * Scope to filter active products
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
