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

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
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

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Product::findOrFail($id));

        $show->id('Id');
        $show->title('Title');
        $show->description('Description');
        $show->image('Image');
        $show->on_sale('On sale');
        $show->rating('Rating');
        $show->sold_count('Sold count');
        $show->review_count('Review count');
        $show->decimal('Decimal');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Product);

        $form->text('title', 'Title');
        $form->textarea('description', 'Description');
        $form->image('image', 'Image');
        $form->switch('on_sale', 'On sale')->default(1);
        $form->decimal('rating', 'Rating')->default(5.00);
        $form->number('sold_count', 'Sold count');
        $form->number('review_count', 'Review count');
        $form->decimal('decimal', 'Decimal');

        return $form;
    }
}
