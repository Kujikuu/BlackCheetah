<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Inventory extends Pivot
{
    protected $table = 'unit_product_inventories';

    protected $fillable = [
        'unit_id',
        'product_id',
        'quantity',
        'reorder_level',
    ];
}
