<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\HandleRefundRequest;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\InternalException;

class OrdersController extends Controller
{
    use HasResourceActions;


    //后台订单列表页面
    public function index(Content $content)
    {
        return $content
            ->header('订单列表')
            ->body($this->grid());
    }

    //订单详情页面
    public function show(Order $order)
    {
        return Admin::content(function (Content $content) use ($order) {
            $content->header('查看订单');
            // body 方法可以接受 Laravel 的视图作为参数
            $content->body(view('admin.orders.show', ['order' => $order]));
        });
    }

    //后台订单列表页面的表格形式
    protected function grid()
    {
        return Admin::grid(Order::class,function (Grid $grid){

            //创建一个名为ID的列，内容是已支付的订单ID字段，并且可以在前端页面点击排序
            $grid->model()->whereNotNull('paid_at')->orderBy('paid_at','desc');

            $grid->no('订单流水号');

             //展示关联关系的字段时，使用column方法
            $grid->column('user.name','买家');

            $grid->total_amount('总金额')->sortable();

            $grid->paid_at('支付时间')->sortable();

            $grid->ship_status('物流')->display(function($value) {
                return Order::$shipStatusMap[$value];
            });

            $grid->refund_status('退款状态')->display(function($value) {
                return Order::$refundStatusMap[$value];
            });

            //后台禁用创建按钮，后台不需要创建订单
            $grid->disableCreateButton();


            $grid->actions(function ($actions){

                //不在每一行后面显示编辑按钮
                $actions->disableEdit();

                //不在每一行后面显示删除按钮
                $actions->disableDelete();
            });

            $grid->tools(function ($tools){

                //禁用批量删除按钮
                $tools->batch(function ($batch){
                    $batch->disableDelete();
                });
            });
        });
    }

    //后台点击发货逻辑
    public function ship(Order $order,Request $request){

        //判断订单是不是已经支付了
        if(!$order->paid_at){
             throw  new InvalidRequestException('该订单未支付');
        }

        //判断订单是不是已经支付了
        if($order->ship_status !== Order::SHIP_STATUS_PENDING){
             throw new InvalidRequestException('该订单已经发货');
        }

        //校验传进来的参数
        $data = $this->validate($request, [
            'express_company' => ['required'],
            'express_no'      => ['required'],
        ], [], [
            'express_company' => '物流公司',
            'express_no'      => '物流单号',
        ]);

        //将订单发货状态改为已发货,并且存入物流信息
        $order->update([
            'ship_status' => Order::SHIP_STATUS_DELIVERED,
            // 我们在 Order 模型的 $casts 属性里指明了 ship_data 是一个数组
            // 因此这里可以直接把数组传过去
            'ship_data'   => $data,
        ]);

        //返回上一页
        return redirect()->back();
    }


    //后台处理退款逻辑
    public function handleRefund(Order $order,HandleRefundRequest $request){

        //判断订单的退款状态是不是申请退款
        if($order->refund_status !== Order::REFUND_STATUS_APPLIED){
            throw new InvalidRequestException('订单状态不正确');
        }

        //分两者情况处理一种是同意一种是不同意
        if($request->input('agree')){
             //同意退款
            // 清空拒绝退款理
            $extra = $order->extra ?: [];
            unset($extra['refund_disagree_reason']);
            $order->update([
                'extra' => $extra,
            ]);
            // 调用退款逻辑
            $this->_refundOrder($order);
        }else{
            //不同意退款
            //将退款理由放到订单中的`extra`字段中
            $extra = $order->extra ? :[];
            $extra['refund_disagree_reason'] = $request->input('reason');

            //将订单的退款状态改为未退款状态
            $order->update([
                'refund_status' => Order::REFUND_STATUS_PENDING,
                'extra'         => $extra,
            ]);
        }
        return $order;
    }

    //同意退款逻辑
    protected function _refundOrder(Order $order)
    {
        // 判断该订单的支付方式
        switch ($order->payment_method) {
            case 'alipay':
                // 用我们刚刚写的方法来生成一个退款订单号
                $refundNo = Order::getAvailableRefundNo();
                // 调用支付宝支付实例的 refund 方法
                $ret = app('alipay')->refund([
                    'out_trade_no' => $order->no, // 之前的订单流水号
                    'refund_amount' => $order->total_amount, // 退款金额，单位元
                    'out_request_no' => $refundNo, // 退款订单号
                ]);
                // 根据支付宝的文档，如果返回值里有 sub_code 字段说明退款失败
                if ($ret->sub_code) {
                    // 将退款失败的保存存入 extra 字段
                    $extra = $order->extra;
                    $extra['refund_failed_code'] = $ret->sub_code;
                    // 将订单的退款状态标记为退款失败
                    $order->update([
                        'refund_no' => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_FAILED,
                        'extra' => $extra,
                    ]);
                } else {
                    // 将订单的退款状态标记为退款成功并保存退款订单号
                    $order->update([
                        'refund_no' => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_SUCCESS,
                    ]);
                }
                break;
            default:
                // 原则上不可能出现，这个只是为了代码健壮性
                throw new InternalException('未知订单支付方式：'.$order->payment_method);
                break;
        }
    }
}
