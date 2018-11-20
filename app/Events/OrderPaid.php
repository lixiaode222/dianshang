<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

//支付成功后触发的事件
class OrderPaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected  $order;

    //在类里面注入订单信息
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    //得到订单信息   事件本身不需要有逻辑，只需要包含相关的信息即可，
    public function getOrder(){
        return $this->order;
    }
}
