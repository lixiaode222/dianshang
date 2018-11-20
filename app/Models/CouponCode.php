<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Self_;

class CouponCode extends Model
{
    //定义两个优惠券的类型常量
    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENT = 'percent';

    //把这两个常量和它们的中文描述对应起来
    public static $typeMap = [
        self::TYPE_FIXED => '固定金额',
        self::TYPE_PERCENT => '比例',
    ];

    //可直接写入和修改的字段
    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'total',
        'used',
        'min_amount',
        'not_before',
        'not_after',
        'enabled',
    ];

    //表明`emabled`字段是布尔型
    protected $casts = [
        'enabled' => 'boolean',
    ];

    //表明两个时间日期类型
    protected $dates = [
        'not_before',
        'not_after',
    ];

    //折扣的友好描述
    protected $appends = ['description'];

    //得到折扣的友好描述
    public function getDescriptionAttribute(){
        $str = '';

        if($this->min_amount>0){
            $str = '满'.str_replace('.00', '', $this->min_amount);
        }

        //如果是比例类型
        if($this->type == self::TYPE_PERCENT){
            return $str.'优惠'.str_replace('.00', '', $this->value).'%';
        }

        return $str.'减'.str_replace('.00', '', $this->value);
    }

    //生成优惠码逻辑
    public static function findAvailableCode($length = 16){

        do{
            //生成一个指定长度的随机字符串，并且转为大写
            $code = strtoupper(Str::random($length));
        }while(self::query()->where('code',$code)->exists());

        return $code;
    }

}
