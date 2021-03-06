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

    //用户修改收货地址页面
    public function edit(UserAddress $user_address){

        $this->authorize('own', $user_address);

        return view('user_addresses.create_and_edit',['address' => $user_address]);
    }

    //用户修改收货地址逻辑
    public function update(UserAddress $user_address,UserAddressRequest $request){

        $this->authorize('own', $user_address);

        $user_address->update($request->only([
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

    //用户删除收货地址逻辑
    public function destroy(UserAddress $user_address){

          $this->authorize('own', $user_address);

          $user_address->delete();

          //因为改成了AJAX请求，不用redirect
          return [];
    }

}
