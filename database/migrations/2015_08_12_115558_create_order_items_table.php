<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrderItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('order_items', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';

			$table->increments('id');
			$table->integer('order_id')->unsigned()->index();
			$table->string('name');
			$table->integer('orderable_id')->unsigned();
			$table->string('orderable_type');
			$table->integer('price_pence_ex_vat');
			$table->integer('price_pence');
			$table->integer('quantity');
			$table->decimal('tax_rate', 4, 2);
			$table->integer('total_tax_pence');
			$table->integer('total_price_pence_ex_vat');
			$table->integer('total_price_pence');
			$table->integer('total_weight');
			$table->string('image');
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
		Schema::drop('order_items');
	}

}
