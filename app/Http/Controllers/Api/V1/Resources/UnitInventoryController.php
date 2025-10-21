<?php

namespace App\Http\Controllers\Api\V1\Resources;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Models\Inventory;
use Illuminate\Http\Request;

class UnitInventoryController extends BaseResourceController
{
    /**
     * Display a listing of the resource.
     */
    public function index($unitId)
    {
        $unit = \App\Models\Unit::with(['products' => function ($q) {
            $q->select('products.id', 'products.name', 'products.unit_price');
        }])->findOrFail($unitId);

        return $this->successResponse($unit->products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'unit_price' => $product->unit_price,
                'quantity' => $product->pivot->quantity,
                'reorder_level' => $product->pivot->reorder_level,
            ];
        }));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $unitId, $productId)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
        ]);

        $unit = \App\Models\Unit::findOrFail($unitId);
        $product = \App\Models\Product::findOrFail($productId);

        // ensure product belongs to same franchise as unit
        abort_if($product->franchise_id !== $unit->franchise_id, 403, 'Product does not belong to this franchise');

        $unit->products()->attach($productId, $data);

        return $this->successResponse(null, 'Product added to unit inventory', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventory $inventory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $unitId, $productId)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
        ]);

        $unit = \App\Models\Unit::findOrFail($unitId);
        $product = \App\Models\Product::findOrFail($productId);

        abort_if($product->franchise_id !== $unit->franchise_id, 403, 'Product does not belong to this franchise');

        $unit->products()->updateExistingPivot($productId, $data);

        return $this->successResponse(null, 'Inventory updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($unitId, $productId)
    {
        $unit = \App\Models\Unit::findOrFail($unitId);
        $product = \App\Models\Product::findOrFail($productId);

        abort_if($product->franchise_id !== $unit->franchise_id, 403, 'Product does not belong to this franchise');

        $unit->products()->detach($productId);

        return $this->successResponse(null, 'Product removed from inventory');
    }
}
