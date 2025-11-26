<?php
// app/Models/Shop/ShopProduct.php
namespace App\Models\Shop;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopProduct extends Model
{
    use SoftDeletes;

    protected $table = 'shop_products';

    protected $fillable = [
        'status','category_id','sku','slug','title','description',
        'product_type','is_digital','price','currency','stock_qty',
        'attributes_json','cover_url','images','file_url',
    ];

    protected $casts = [
        'attributes_json' => 'array',
        'is_digital'      => 'boolean',
        'product_type'    => ProductType::class,
    ];

    public function category()
    {
        // si tu utilises main_categories
        return $this->belongsTo(\App\Models\MainCategory::class, 'category_id');
    }

    public function orderItems()
    {
        return $this->hasMany(ShopOrderItem::class, 'product_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
