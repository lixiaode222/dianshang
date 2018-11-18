<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id')->comment('商品ID');
            $table->string('title')->comment('商品名称');
            $table->text('description')->comment('商品详情');
            $table->string('image')->comment('商品图片路径');
            $table->tinyInteger('on_sale')->default(true)->comment('商品是否在售卖');
            $table->float('rating')->default(5)->comment('商品平均评分');
            $table->unsignedInteger('sold_count')->default(0)->comment('商品销量');
            $table->unsignedInteger('review_count')->default(0)->comment('商品评价数量');
            $table->decimal('decimal',10,2)->comment('商品sku的最低价格');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
