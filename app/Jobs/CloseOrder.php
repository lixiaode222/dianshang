<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

//代表这个类需要放到队列中执行，而不是触发时直接执行
class CloseOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected  $order;

    public function __construct(Order $order,$delay)
    {
        //将订单信息注入到这个类中
       $this->order = $order;
       //设置延迟时间，delay()方法的参数时延迟多少秒执行
        $this->delay($delay);
    }

    //定义这个任务类的具体执行逻辑
    //当队列任务从队列中取出任务时，会调用handle()方法
    public function handle()
    {
        //首先判断这个订单是不是已经关闭了
        //如果时的话直接返回就行
        if($this->order->paid_at){
            return;
        }

        //通过事务执行sql
        \DB::transaction(function (){

            //将订单中的`closed`字段标记为true
            $this->order->update(['closed' => true]);
            //循环遍历订单中的商品SKU,将订单中的数量加回到库存中区
            foreach ($this->order->items as $item){
                $item->productSku->addStock($item->amount);
            }
            //减少优惠券使用量
            if ($this->order->couponCode) {
                $this->order->couponCode->changeUsed(false);
            }
        });
    }
}
