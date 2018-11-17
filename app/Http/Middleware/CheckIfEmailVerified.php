<?php

namespace App\Http\Middleware;

use Closure;

//判断是否通过邮箱验证的中间件
class CheckIfEmailVerified
{

    public function handle($request, Closure $next)
    {
        //如果没通过邮箱验证
        if(!$request->user()->email_verified){
            //如果是AJAX请求,则通过JSON返回
            if($request->expectsJson()){
                return response()->json(['msg'=>'请先验证邮箱'],400);
            }
            //不然的话就返回邮箱验证提醒视图
            return redirect(route('email_verify_notice'));
        }

        //如果通过了邮箱验证，就继续执行下一个中间件
        return $next($request);
    }
}
