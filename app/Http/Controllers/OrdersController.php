<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\UserAddress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Jobs\CloseOrder;
use App\Services\CartService;
use App\Services\OrderService;
use App\Events\OrderReviewed;
use App\Http\Requests\ApplyRefundRequest;
use App\Http\Requests\SendReviewRequest;

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

    //用户评价页面
    public function review(Order $order)
    {
        // 校验权限
        $this->authorize('own', $order);
        // 判断是否已经支付
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }
        // 使用 load 方法加载关联数据，避免 N + 1 性能问题
        return view('orders.review', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    //用户评价逻辑
    public function sendReview(Order $order, SendReviewRequest $request)
    {
        // 校验权限
        $this->authorize('own', $order);
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }
        // 判断是否已经评价
        if ($order->reviewed) {
            throw new InvalidRequestException('该订单已评价，不可重复提交');
        }
        $reviews = $request->input('reviews');
        // 开启事务
        \DB::transaction(function () use ($reviews, $order) {
            // 遍历用户提交的数据
            foreach ($reviews as $review) {
                $orderItem = $order->items()->find($review['id']);
                // 保存评分和评价
                $orderItem->update([
                    'rating'      => $review['rating'],
                    'review'      => $review['review'],
                    'reviewed_at' => Carbon::now(),
                ]);
            }
            // 将订单标记为已评价
            $order->update(['reviewed' => true]);

            //触发跟新评价信息
            event(new OrderReviewed($order));
        });

        return redirect()->back();
    }

    //用户申请退款
    public function applyRefund(Order $order,ApplyRefundRequest $request){

        //校验权限
        $this->authorize('own',$order);

        //判断订单是不是已经支付了
        if(!$order->paid_at){
            throw new InvalidRequestException('该订单未支付，不可退款');
        }

        //判断订单退款状态
        if($order->refund_status !== Order::REFUND_STATUS_PENDING){
            throw new InvalidRequestException('该订单已经申请过退款，请勿重复申请');
        }

        //将用户输入的退款理由放到订单的extra字段中
        $extra = $order->extra ?:[];
        $extra['refund_reason'] = $request->input('reason');

        //将订单退款状态改为已申请退款
        $order->update([
            'refund_status' => Order::REFUND_STATUS_APPLIED,
            'extra'         => $extra,
        ]);

        return $order;
    }

}
