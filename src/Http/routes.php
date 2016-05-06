<?php

/**
 * Admin routes
 */
Route::group(['middleware' => 'web', 'prefix' => 'admin', 'namespace' => 'Bozboz\Ecommerce\Orders\Http\Controllers\Admin'], function()
{
	/* Orders */
	Route::resource('orders', 'OrderController', ['except' => ['show']]);
	Route::post('orders/{id}/invoice', [
		'uses' => 'OrderController@downloadInvoice',
		'as' => 'admin.orders.invoice'
	]);
	Route::post('orders/{id}/refund', [
		'uses' => 'OrderController@refund',
		'as' => 'admin.orders.refund',
		'before' => 'csrf'
	]);
	Route::get('orders/csv', [
		'uses' => 'OrderController@downloadCsv'
	]);

	/* Customers */
	Route::resource('customers', 'CustomerController', ['except' => 'show']);
	Route::put('customers/{customer}/address/{address}', [
		'uses' => 'CustomerController@updateAddress',
		'as' => 'admin.customer.address.update'
	]);
});

/**
 * Cart
 */
Route::group(['prefix' => 'cart', 'namespace' => 'Bozboz\Ecommerce\Http\Controllers'], function()
{
	Route::get('/', [
		'as' => 'cart',
		'uses' => 'CartController@index'
	]);

	Route::post('/', [
		'uses' => 'CartController@update',
		'as' => 'cart.update'
	]);

	Route::post('items', [
		'as' => 'cart.add',
		'uses' => 'CartController@add'
	]);

	Route::post('voucher', [
		'as' => 'cart.add-voucher',
		'uses' => 'CartController@addVoucher'
	]);

	Route::delete('/', [
		'as' => 'cart.clear',
		'uses' => 'CartController@destroy'
	]);

	Route::delete('items/{id}', [
		'as' => 'cart.remove-item',
		'uses' => 'CartController@remove'
	]);

	Route::get('items/remove/{id}/{sessionId}', [
		'as' => 'cart.remove-item',
		'uses' => 'CartController@remove',
		'before' => 'sessionProtect'
	]);
});
