<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeyConstraints extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::transaction(function($connection)
		{

			/**
			 * Deleting a state belonging to orders is restricted
			 * Deleting a user belonging to orders nullifies the user_id
			 * Deleting an address belonging to orders (shipping or billing) is restricted
			 * Deleting an order which is the parent of an order nullifies the parent_order_id
			 */
			Schema::table('orders', function(Blueprint $table)
			{
				$table->foreign('state_id')->references('id')->on('order_states')->onDelete('restrict');
				$table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
				$table->foreign('shipping_address_id')->references('id')->on('addresses')->onDelete('restrict');
				$table->foreign('billing_address_id')->references('id')->on('addresses')->onDelete('restrict');
				$table->foreign('parent_order_id')->references('id')->on('orders')->onDelete('set null');
			});

			/**
			 * Deleting an order belong to order_items is restricted
			 */
			Schema::table('order_items', function(Blueprint $table)
			{
				$table->foreign('order_id')->references('id')->on('orders')->onDelete('restrict');
			});

			/**
			 * Deleting an address or customer removes the link
			 */
			Schema::table('address_customer', function(Blueprint $table)
			{
				$table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
				$table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
			});
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('orders', function(Blueprint $table)
		{
			$table->dropForeign('orders_state_id_foreign');
			$table->dropForeign('orders_user_id_foreign');
			$table->dropForeign('orders_shipping_address_id_foreign');
			$table->dropForeign('orders_billing_address_id_foreign');
			$table->dropForeign('orders_parent_order_id_foreign');
		});

		Schema::table('order_items', function(Blueprint $table)
		{
			$table->dropForeign('order_items_order_id_foreign');
		});

		Schema::table('address_customer', function(Blueprint $table)
		{
			$table->dropForeign('address_customer_address_id_foreign');
			$table->dropForeign('address_customer_customer_id_foreign');
		});
	}

}
