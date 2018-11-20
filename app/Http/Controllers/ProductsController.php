<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    //商品列表页面
    public function index(Request $request){

        //创建一个查询构造器 先找出所有上架的商品
        $builder = Product::query()->where('on_sale',true);

        //判断页面url中是否带有search参数，如果有就赋值给$search，用模糊查询查询结果
        if($search = $request->input('search','')){
             $like = '%'.$search.'%';
             //模糊搜索商品标题、商品描述、SKU标题、SKU描述
            $builder->where(function ($query) use ($like){
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }

        //判断页面url中是否带有order参数，如果有就赋值给$order，对结果做相应的排序
        if($order = $request->input('order','')){
             //$oreder是否是以_asc或者_desc结尾的 如果不是的话就是错误的不用排序
            if(preg_match('/^(.+)_(asc|desc)$/',$order,$m)){
                // 如果字符串的开头是这 3 个字符串之一，说明是一个合法的排序值
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 根据传入的排序值来构造排序参数
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }

        //分页
        $products = $builder->paginate(16);

        //把搜索内容和排序方式也返回到页面中
        return view('products.index', [
            'products' => $products,
            'filters'  => [
                'search' => $search,
                'order'  => $order,
            ],
        ]);
    }

    //商品详情页面
    public function show(Product $product,Request $request){

        //判断商品有没有上架，如果没有就直接抛出异常
        if(!$product->on_sale){
             throw new InvalidRequestException('商品还没有上架');
        }

        $favored = false;
        // 用户未登录时返回的是 null，已登录时返回的是对应的用户对象
        if($user = $request->user()) {
            // 从当前用户已收藏的商品中搜索 id 为当前商品 id 的商品
            // boolval() 函数用于把值转为布尔值
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }

        $reviews = OrderItem::query()
            ->with(['order.user', 'productSku']) // 预先加载关联关系
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at') // 筛选出已评价的
            ->orderBy('reviewed_at', 'desc') // 按评价时间倒序
            ->limit(10) // 取出 10 条
            ->get();

        // 最后别忘了注入到模板中
        return view('products.show', [
            'product' => $product,
            'favored' => $favored,
            'reviews' => $reviews
        ]);
    }

    //用户收藏商品逻辑
    public function favor(Product $product,Request $request){

        $user = $request->user();
        //先判断这件商品是不是已经收藏了，如果是直接返回
        if($user->favoriteProducts()->find($product->id)){
              return [];
        }

        //新增关联
        $user->favoriteProducts()->attach($product);

        return [];
    }

    //用户取消收藏逻辑
    public function disfavor(Product $product,Request $request){

        $user = $request->user();

        //先判断这件商品是不是已经收藏了，如果不是直接返回
        if(!$user->favoriteProducts()->find($product->id)){
            return [];
        }

        //取消关联
        $user->favoriteProducts()->detach($product);

        return [];
    }

    //用户收藏商品列表
    public function favorites(Request $request){

        $products = $request->user()->favoriteProducts()->paginate(16);

        return view('products.favorites',compact('products'));
    }
}
