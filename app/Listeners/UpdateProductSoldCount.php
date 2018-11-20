<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\OrderItem;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

//  implements ShouldQueue 代表此监听器是异步执行的
class UpdateProductSoldCount implements ShouldQueue
{

    public function handle(OrderPaid $event)
    {
        //从事件中取出对应的订单信息
        $order = $event->getOrder();
        //预加载商品数据
        $order->load('items.product');
        //循环遍历订单商品
        foreach ($order->items as $item){
            //得到商品信息
            $product = $item->product;
            //计算对应商品的销量
            $soldCount = OrderItem::query()
                       ->where('product_id',$product->id)
                       ->whereHas('order',function ($query){
                           $query->whereNotNull('paid_at');  //关联的订单是已经支付的
                       })->sum('amount');  //再加上这次订单的销量

            //更新商品销量
            $product->update(['solu_count' => $soldCount]);
        }
    }
}
