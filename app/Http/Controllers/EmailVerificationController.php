<?php

namespace App\Http\Controllers;

use App\Models\User;
use Cache;
use Illuminate\Http\Request;
use Exception;
use Mail;
use App\Notifications\EmailVerificationNotification;

class EmailVerificationController extends Controller
{
      //发送验证邮件
      public function send(Request $request){
          $user = $request->user();
          //判断用户是否已经激活了
          if($user->email_verified){
              throw new Exception('您已经通过邮箱验证了');
          }

          //调用notify()方法来发送我们定义的邮件
          $user->notify(new EmailVerificationNotification());

          //返回视图
          return view('pages.success',['msg' => '邮件发送成功']);
      }

      //邮箱验证逻辑
      public function verify(Request $request){

          //从url中获取`email`和`token`两个参数
          $email = $request->input('email');
          $token = $request->input('token');

          //如果有一个参数为空说明这不是一个合法的链接，直接抛出异常
          if(!$email || !$token){
               throw new Exception('验证链接不正确');
          }

          //用得到的`email`拼上字符串作为键名,从缓存中读取数据和获取的`token`做对比
          //如果数据不存在或者两者不一致则抛出异常
          if($token != Cache::get('email_verification_'.$email)){
               throw new Exception('验证链接不正确或已过期');
          }

          //用得到的`email`从数据库查找出对应的用户
          //通常来说这个用户肯定是存在的
          //但还是多做一层判断保证代码的健壮性
          if(!$user = User::where('email',$email)->first()){
               throw  new Exception('用户不存在');
          }

          //通过邮箱验证后，我们就把对应用户的`email_verified`字段修改为`true`
          $user->update(['email_verified' => true]);

          //然后将对应缓存清除掉
          Cache::forget('email_verification_'.$email);

          //最后告知用户邮箱验证成功 渲染视图
          return view('pages.success',['msg' => '邮箱验证成功']);
      }
}
