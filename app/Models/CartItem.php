<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    //可直接写入和修改的字段
    protected $fillable = [
        'amount',
    ];

    //表明这个表没有时间戳字段
    public $timestamps = false;

    //模型关联 由这个购物车项得到对应的用户
    public function user(){

        return $this->belongsTo(User::class);
    }

    //模型关联 由这个购物车项得到对应的商品SKU
    public function productSku(){

        return $this->belongsTo(ProductSku::class);
    }
}
