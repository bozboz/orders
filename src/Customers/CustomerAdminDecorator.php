<?php

namespace Bozboz\Ecommerce\Orders\Customers;

use Bozboz\Admin\Fields\EmailField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Admin\Reports\Filters\SearchListingFilter;
use Bozboz\Admin\Users\RoleAdminDecorator;
use Bozboz\Admin\Users\UserAdminDecorator;
use Illuminate\Database\Eloquent\Builder;

class CustomerAdminDecorator extends UserAdminDecorator
{
	public function __construct(Customer $customer, RoleAdminDecorator $roles)
	{
		parent::__construct($customer, $roles);
	}

	public function modifyListingQuery(Builder $query)
	{
		$query->has('orders')->with('orders')->latest();
	}

	public function getColumns($inst)
	{
		return [
			'Name' => $inst->first_name . ' ' . $inst->last_name,
			'Email' => \HTML::link('mailto:' . $inst->email, $inst->email),
			'Customer Since' => $inst->created_at,
			'Last Order' => $this->getLastOrder($inst)
		];
	}

	protected function getLastOrder($customer)
	{
		$order = $customer->orders()->complete()->latest()->first();

		return $order ? $order->created_at->diffForHumans() : '-';
	}

	public function getListingFilters()
	{
		return [
			new SearchListingFilter('search', ['first_name', 'last_name', 'email'], function($q, $value) {
				foreach(explode(' ', $value) as $part) {
					$q->where(function($q) use ($part) {
						foreach(['first_name', 'last_name', 'email'] as $attr) {
							$q->orWhere($attr, 'LIKE', "%$part%");
						}
					});
				}
			})
		];
	}

	public function getFields($inst)
	{
		return [
			new TextField('first_name'),
			new TextField('last_name'),
			new EmailField('email'),
			$this->getPasswordFieldForUser($inst),
		];
	}
}
