<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Exception;

//用户异常
class InvalidRequestException extends Exception
{
      //默认错误信息为空，错误码为400
      public function __construct(string $message = "",int $code = 400){
            parent::__construct($message,$code);
      }

      //抛出异常的的处理
      public function render(Request $request){

          //如果是AJAX请求就返回JSON格式的信息
          if($request->expectsJson()){
               return response()->json(['msg' => $this->message],$this->code);
          }
          //不是的话就返回错误视图
          return view('pages.error',['msg' => $this->message]);
      }
}
