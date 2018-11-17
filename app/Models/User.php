<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    //使用通知类
    use Notifiable;

    //可直接写入和修改的字段
    protected $fillable = [
        'name', 'email', 'password','email_verified'
    ];

    //表明'email_verified'字段是布尔型
    protected $casts = [
        'email_verified' => 'boolean',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    //模型关联 由用户得到它的所有收货地址
    public function addresses(){
        return $this->hasMany(UserAddress::class);
    }
}