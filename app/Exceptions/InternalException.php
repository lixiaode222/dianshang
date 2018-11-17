<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class InternalException extends Exception
{
    protected $msgForUser;

    //默认传给用户的错误信息就是'系统内部错误' 错误码为500
    public function __construct(string $message, string $msgForUser = '系统内部错误', int $code = 500)
    {
        parent::__construct($message, $code);
        $this->msgForUser = $msgForUser;
    }

    public function render(Request $request){
        //如果是AJAX请求就返回JSON格式的信息
        if($request->expectsJson()){
            return response()->json(['msg' => $this->msgForUser],$this->code);
        }
        //不是的话就返回错误视图
        return view('pages.error',['msg' => $this->msgForUser]);
    }

}
