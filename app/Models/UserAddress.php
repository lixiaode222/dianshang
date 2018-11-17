<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    //可直接写入和修改的字段
    protected $fillable = [
        'province',
        'city',
        'district',
        'address',
        'zip',
        'contact_name',
        'contact_phone',
        'last_used_at',
    ];

    //表明`last_used_at`字段时日期时间类型
    //以后会直接返回一个Carbon对象(日期时间对象)
    protected $dates = ['last_used_at'];

    //模型关联 由地址得到对应的用户
    public function user(){
        return $this->belongsTo(User::class);
    }

    //通过有关地址的四个字段，拼接成完整的具体地址
    public function getFullAddressAttribute(){
        return "{$this->province}{$this->city}{$this->district}{$this->address}";
    }
}
