<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('优惠券标题');
            $table->string('code')->unique()->comment('优惠码');
            $table->string('type')->comment('优惠券类型');
            $table->decimal('value')->comment('折扣值');
            $table->unsignedInteger('total')->comment('全站可兑换数量');
            $table->unsignedInteger('used')->default(0)->comment('当前已兑换数量');
            $table->decimal('min_amount',10,2)->comment('使用的最低金额');
            $table->dateTime('not_before')->nullable()->comment('这个日期前不能使用');
            $table->dateTime('not_after')->nullable()->comment('这个日期后不能使用');
            $table->boolean('enabled')->comment('优惠券是否生效');
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
        Schema::dropIfExists('coupon_codes');
    }
}
