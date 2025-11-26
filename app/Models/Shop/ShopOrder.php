<?php
// app/Models/Shop/ShopOrder.php
namespace App\Models\Shop;

use App\Enums\ShopOrderState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopOrder extends Model
{
    use SoftDeletes;

    protected $table = 'shop_orders';

    protected $fillable = [
        'status','user_id','code','state','currency',
        'subtotal','discount','shipping','total',
        'paid_at','payment_id',
        'shipping_name','shipping_phone','shipping_addr',
        'shipping_ref','shipping_city','shipping_country','shipping_notes',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'state'   => ShopOrderState::class,
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function items()
    {
        return $this->hasMany(ShopOrderItem::class, 'order_id');
    }
}
