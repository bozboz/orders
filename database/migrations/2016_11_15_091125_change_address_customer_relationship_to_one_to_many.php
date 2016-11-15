<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAddressCustomerRelationshipToOneToMany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->unsignedInteger('customer_id')->after('id')->nullable();
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('set null');
        });

        DB::statement('
            UPDATE
                addresses as a,
                address_customer as ac
            SET
                a.customer_id = ac.customer_id
            WHERE
                a.id = ac.address_id
        ');

        Schema::drop('address_customer');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('address_customer', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('address_id')->index();
            $table->unsignedInteger('customer_id')->index();
            $table->timestamps();

            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
        });

        DB::table('address_customer')->insert(
            json_decode(json_encode(
                DB::table('addresses')->selectRaw('
                    id as address_id,
                    customer_id,
                    NOW() as created_at,
                    NOW() as updated_at
                ')
                ->whereNotNull('customer_id')
                ->get()
            ), true)
        );

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign('addresses_customer_id_foreign');
            $table->dropColumn('customer_id');
        });
    }
}
