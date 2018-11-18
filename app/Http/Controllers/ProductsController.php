<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
}
