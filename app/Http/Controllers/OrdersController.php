<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\UserAddress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Jobs\CloseOrder;
use App\Services\CartService;
use App\Services\OrderService;

class OrdersController extends Controller
{
    //用户下单逻辑
    // 利用 Laravel 的自动解析功能注入 CartService 类
    public function store(OrderRequest $request, OrderService $orderService)
    {
        $user    = $request->user();
        $address = UserAddress::find($request->input('address_id'));

        return $orderService->store($user, $address, $request->input('remark'), $request->input('items'));
    }


    //用户订单页面
    public function index(Request $request){

        $user = $request->user();
        $orders = Order::query()
                //使用with方法加载,避免N+1问题
                ->with(['items.product','items.productSku'])
                ->where('user_id',$user->id)
                ->orderBy('created_at','desc')
                ->paginate();

        return view('orders.index',compact('orders'));
    }

    //订单详情
    public function show(Order $order,Request $request){

        $this->authorize('own', $order);

        return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    //用户确认收货逻辑
    public function received(Order $order,Request $request){

        //校验权限
        $this->authorize('own', $order);

        //判断订单的发货状态是不是已经发货了
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            throw new InvalidRequestException('发货状态不正确');
        }

        // 更新发货状态为已收到
        $order->update(['ship_status' => Order::SHIP_STATUS_RECEIVED]);

        // 返回订单信息
        return $order;
    }
}
