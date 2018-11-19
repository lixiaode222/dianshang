<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use Illuminate\Http\Request;

class CartController extends Controller
{
    //用户购物车列表页面
    public function index(Request $request){

        $cartItems = $request->user()->cartItems()->with(['productSku.product'])->get();

        return view('cart.index',compact('cartItems'));
    }

    //把商品添加到购物车逻辑
    public function add(AddCartRequest $request){

        $user = $request->user();
        $skuId = $request->input('sku_id');
        $amount = $request->input('amount');

        //先判断购物车里面是不是已经有这件商品了
        if($cart = $user->cartItems()->where('product_sku_id',$skuId)->first()){

            //如果有这件商品了 直接叠加商品数量就行
            $cart->update([
                'amount' => $cart->amount + $amount,
            ]);
        }else{

            //如果没有 就新建这样一个购物车项
            $cart = new CartItem(['amount' => $amount]);
            $cart->user()->associate($user);
            $cart->productSku()->associate($skuId);
            $cart->save();
        }

        return [];
    }

    //用户移除购物车项逻辑
    public function remove(ProductSku $sku,Request $request){

        $request->user()->cartItems()->where('product_sku_id',$sku->id)->delete();

        return [];
    }


}
