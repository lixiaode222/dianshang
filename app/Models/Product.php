<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //可直接写入和修改的字段
    protected $fillable = [
        'title',
        'description',
        'image',
        'on_sale',
        'rating',
        'sold_count',
        'review_count',
        'price',
    ];

    //表明`on_sale`是一个布尔类型字段
    protected $casts = [
        'on_sale' => 'boolean',
    ];

    //模型关联 由商品得到它的所有sku
    public function skus(){

        return $this->hasMany(ProductSku::class);
    }
}
