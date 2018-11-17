<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserAddressesController extends Controller
{
    //用户收货地址列表
    public function index(Request $request){

         $addresses = $request->user()->addresses;

         return view('user_addresses.index',compact('addresses'));
    }
}
