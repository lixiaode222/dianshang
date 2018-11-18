<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use function foo\func;

class ProductsController extends Controller
{
    use HasResourceActions;

    //后台商品列表页面
    public function index(Content $content)
    {
        return $content
            ->header('商品列表')
            ->body($this->grid());
    }

    //后台修改商品页面
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('编辑商品');
            $content->body($this->form()->edit($id));
        });
    }

    //后台修改商品逻辑
    public function update($id)
    {
        return $this->form()->update($id);
    }

    //后台添加商品页面
    public function create(Content $content)
    {
        return $content
            ->header('添加商品')
            ->body($this->form());
    }

    //后台添加商品逻辑
    public function store()
    {
        return $this->form()->store();
    }

    //后台商品列表的表格形式
    protected function grid()
    {
        return Admin::grid(Product::class,function (Grid $grid){

            //创建一个名为ID的列，内容是商品的ID字段，并且可以在前端页面点击排序
            $grid->id('ID')->sortable();

            $grid->title('商品名称');

            $grid->on_sale('已上架')->display(function($value){
                return $value? '是' : '否' ;
            });

            $grid->price('价格');

            $grid->rating('评分');

            $grid->sold_count('销量');

            $grid->review_count('评论数');

            $grid->actions(function ($actions){

                //不在每一行后面显示查看按钮
                $actions->disableView();

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

    //后台添加商品页面的表格形式
    protected function form()
    {
        return Admin::form(Product::class,function(Form $form){

            //创建一个输入框，第一个参数是字段名`title`,第二个参数是该字段描述`商品名称` 后面是验证规则
            $form->text('title', '商品名称')->rules('required');

            //创建一个选择图片的框
            $form->image('image', '商品图片')->rules('required|image');

            //创建一个富文本编辑器
            $form->editor('description','商品描述')->rules('required');

            //创建一组单选框
            $form->radio('on_sale', '上架')->options(['1'=>'是','0'=>'否'])->default('0');

            //因为有关联的sku关系 所以直接添加一对多关系的关联模型
            $form->hasMany('skus','SKU 列表',function(Form\NestedForm $form){

                //创建一个sku名称的输入框
                $form->text('title','商品SKU名称')->rules('required');

                //创建一个sku描述的输入框
                $form->text('description','商品SKU描述')->rules('required');

                //创建一个单价的输入框
                $form->text('price','单价')->rules('required|numeric|min:0.01');

                //创建一个库存的输入框
                $form->text('stock','剩余库存')->rules('required|integer|min:0');

            });

            //定义事件回调时，当模型即将保存时会触发这个回调
            //大概意思就是商品的价格是它所以sku价格中最低的那个
            $form->saving(function(Form $form){
                  $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME,0)->min('price') ?: 0;
            });
        });
    }
}
