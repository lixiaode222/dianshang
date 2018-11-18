<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class UsersController extends Controller
{
    use HasResourceActions;

    //后台用户列表页面
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->body($this->grid());
    }


    //后台列表页面展示表格形式
    protected function grid()
    {
        //根据回调函数，在页面上用表格的形式来展现用户记录
        return Admin::grid(User::class,function (Grid $grid){

            //创建一个名为ID的列，内容是用户的ID字段，并且可以在前端页面点击排序
            $grid->id('ID')->sortable();

            //创建一个名为用户名的列，内容是用户的name字段。下面的其它列也是一样的
            $grid->name('用户名');

            $grid->email('邮箱');

            $grid->email_verified('已验证邮箱')->display(function ($value){
                   return $value? '是' : '否';
            });

            $grid->created_at('注册时间');

            //不再页面显示`新建`按钮，因为我们不需要再后台新建用户
            $grid->disableCreateButton();

            $grid->actions(function ($actions){

                //不在每一行后面显示查看按钮
                $actions->disableView();

                //不在每一行后面显示删除按钮
                $actions->disableDelete();

                //不在每一行后面显示编辑按钮
                $actions->disableEdit();
            });

            $grid->tools(function ($tools){

                //禁用批量删除按钮
                $tools->batch(function ($batch){
                    $batch->disableDelete();
                });
            });
        });
    }

}
