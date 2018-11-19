<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\UserAddress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Jobs\CloseOrder;

class OrdersController extends Controller
{
    //用户下单逻辑
    public function store(OrderRequest $request){

        $user = $request->user();

        //开启一个数据事务
        $order = \DB::transaction(function () use ($user,$request){

            //传进来的只是一个地址ID 我们要根据这个ID得到具体地址
            $address = UserAddress::find($request->input('address_id'));
            //更新这个地址的使用时间
            $address->update(['last_used_at' => Carbon::now()]);
            //创建一个订单
            $order = new Order([
                'address' => [
                    //将地址信息放入订单中
                    'address'       => $address->full_address,
                    'zip'           => $address->zip,
                    'contact_name'  => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark' => $request->input('remark'),
                'total_amount' => 0,
            ]);

            //订单关联用户
            $order->user()->associate($user);
            //写入数据库
            $order->save();

            $totalAmount = 0;
            $items = $request->input('items');
            //遍历用户提交的SKU
            foreach ($items as $data){
                $sku = ProductSku::find($data['sku_id']);
                //创建一个orderItem 并直接与当前订单关联
                $item = $order->items()->make([
                     'amount' => $data['amount'],
                     'price'  => $sku->price,
                ]);
                //将这个订单项与商品关联
                $item->product()->associate($sku->product_id);
                //将这个订单与商品sku关联
                $item->productSku()->associate($sku);
                //写入数据库
                $item->save();
                //每次循环都加上这个订单项的金额 最后得到订单的总金额
                $totalAmount += $sku->price * $data['amount'];
                //减去每个商品sku的库存
                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }

            //更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            //将下单的商品从购物车中移除
            //得到购物车中所有的sku ID
            $skuIds = collect($request->input('items'))->pluck('sku_id');
            //然后根据ID删除
            $user->cartItems()->where('product_sku_id',$skuIds)->delete();

            return $order;
        });

        //触发延时任务，如果用户超过时间不支付订单，订单自动关闭
        //app.order_ttl 是我们自己定义在config/app.php 中的  关闭时间
        $this->dispatch(new CloseOrder($order, config('app.order_ttl')));

        return $order;
    }
}
