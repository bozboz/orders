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
			$table->string('transaction_id')->nullable();
			$table->integer('state_id')->unsigned();
			$table->string('checkout_progress')->nullable();
			$table->string('customer_email');
			$table->string('customer_first_name');
			$table->string('customer_last_name');
			$table->string('customer_phone')->nullable();
			$table->string('company')->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->integer('shipping_address_id')->unsigned();
			$table->integer('billing_address_id')->unsigned();
			$table->string('payment_method');
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
