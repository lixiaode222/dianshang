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
use App\Exceptions\InvalidRequestException;

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

}
