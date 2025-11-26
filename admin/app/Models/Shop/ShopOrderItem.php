<?php

// app/Models/Shop/ShopOrderItem.php
namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopOrderItem extends Model
{
    use SoftDeletes;

    protected $table = 'shop_order_items';

    protected $fillable = [
        'status','order_id','product_id','product_title','sku',
        'qty','unit_price','currency','is_digital','download_url',
        'chosen_options',
    ];

    protected $casts = [
        'is_digital'     => 'boolean',
        'chosen_options' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(ShopOrder::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(ShopProduct::class, 'product_id');
    }
}
