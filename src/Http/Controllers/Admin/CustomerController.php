<?php

namespace Bozboz\Ecommerce\Orders\Http\Controllers\Admin;

use Input;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Ecommerce\Orders\Customers\CustomerAdminDecorator;

class CustomerController extends ModelAdminController
{
	protected $editView = 'orders::customers.admin.record';

	public function __construct(CustomerAdminDecorator $decorator)
	{
		parent::__construct($decorator);
	}

	public function edit($id)
	{
		$view = parent::edit($id);

		$view->with('addresses', $view->model->addresses);

		$view->with(
			'orderHistory',
			$view->model->orders()->complete()->with('items.orderable')->latest()->get()
		);

		return $view;
	}

	public function updateAddress($customer, $address)
	{
		$customer = $this->decorator->findInstance($customer);

		$address = $customer->addresses()->where('address_id', $address)->firstOrFail();

		$customer->addresses()->detach($address);

		$customer->addresses()->create(Input::except('after_save'));

		return $this->reEdit($customer);
	}

    public function viewPermissions($stack)
    {
        $stack->add('ecommerce');
    }

    public function createPermissions($stack, $instance)
    {
        $stack->add('ecommerce', $instance);
    }

    public function editPermissions($stack, $instance)
    {
        $stack->add('ecommerce', $instance);
    }

    public function deletePermissions($stack, $instance)
    {
        $stack->add('ecommerce', $instance);
    }
}
