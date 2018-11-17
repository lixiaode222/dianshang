<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAddressRequest;
use App\Models\UserAddress;
use Illuminate\Http\Request;

class UserAddressesController extends Controller
{
    //用户收货地址列表
    public function index(Request $request){

         $addresses = $request->user()->addresses;

         return view('user_addresses.index',compact('addresses'));
    }

    //用户添加收货地址列表页面
    public function create(){

        return view('user_addresses.create_and_edit',['address' => new UserAddress()]);
    }

    //用户添加收货地址逻辑
    public function store(UserAddressRequest $request){

        $request->user()->addresses()->create($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
        ]));

        return redirect()->route('user_addresses.index');
    }

}
