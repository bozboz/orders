<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orders', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';

			$table->increments('id');
			$table->string('transaction_id')->nullable()->index();
			$table->string('state')->index();
			$table->string('checkout_progress')->nullable();
			$table->string('customer_email')->nullable();
			$table->string('customer_first_name')->nullable();
			$table->string('customer_last_name')->nullable();
			$table->string('customer_phone')->nullable();
			$table->string('company')->nullable();
			$table->integer('user_id')->unsigned()->nullable()->nullable();
			$table->integer('shipping_address_id')->unsigned()->nullable();
			$table->integer('billing_address_id')->unsigned()->nullable();
			$table->string('payment_method')->nullable();
			$table->string('payment_ref')->nullable();
			$table->string('card_identifier', 4)->nullable();
			$table->text('payment_data')->nullable();
			$table->integer('parent_order_id')->unsigned()->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('orders');
	}

}
