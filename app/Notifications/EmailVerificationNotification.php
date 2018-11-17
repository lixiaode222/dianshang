<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Cache;
use Illuminate\Support\Str;
use Illuminate\Notifications\Messages\MailMessage;

//接口ShouldQueue没有任何方法需要实现，但加上后Laravel会用将发邮件的操作放进队列里来实现异步发送
class EmailVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;


    //通知方式，我们只需要邮件就行了
    public function via($notifiable)
    {
        return ['mail'];
    }

    //发送邮件时会调用这个方法来构建邮件内容  $notifiable其实是用户实例
    public function toMail($notifiable)
    {
        //使用Laravel内置的Str类来生成随机字符串,参数是你需要的长度
        $token = Str::random(16);

        //然后将这个令牌存入缓存里面，键名为`email_verification_`拼上邮箱，有效时间为30分钟
        Cache::set('email_verification_'.$notifiable->email,$token,30);

        //把邮箱和令牌存入验证路由的url中
        $url = route('email_verification.verify', ['email' => $notifiable->email, 'token' => $token]);
        return (new MailMessage)
                    ->greeting($notifiable->name.'您好')   //问候
                    ->subject('注册成功，请验证您的邮箱')    //标题
                    ->line('请点击下方链接验证您的邮箱')
                    ->action('验证', $url);
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
