<?php

namespace Bozboz\Ecommerce\Orders;

use Bozboz\Admin\Base\BulkAdminDecorator;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Admin\Reports\Downloadable;
use Bozboz\Admin\Reports\Filters\ArrayListingFilter;
use Bozboz\Admin\Reports\Filters\RelationFilter;
use Bozboz\Admin\Reports\Filters\SearchListingFilter;
use Bozboz\Ecommerce\Orders\ListingFilters\DateFilter;
use Illuminate\Database\Eloquent\Builder;

class OrderDecorator extends BulkAdminDecorator implements Downloadable
{
	private $orderStates;

	const TODAY = 1;
	const THIS_WEEK = 2;
	const THIS_MONTH = 3;
	const PAST_WEEK = 4;
	const PAST_MONTH = 5;
	const PAST_QUARTER = 6;

	public function __construct(Order $model)
	{
		parent::__construct($model);
	}

	public function getColumns($order)
	{
		return array(
			'Order Number' => sprintf('<strong class="id">%s</strong>', $order->transaction_id),
			'Customer' => $order->customer_first_name . ' ' . $order->customer_last_name,
			'Country' => $order->billingAddress ? $order->billingAddress->country : '-',
			'Date' => $order->created_at,
			'Total' => format_money($order->totalPrice())
		);
	}

	public function getStateTransitions()
	{
		$stateMachine = $this->model->getStateMachine();
		return collect($stateMachine->getTransitions());
	}

	public function getColumnsForCSV($order)
	{
		return [
			'id' => $order->id,
			'customer' => $order->customer_first_name . ' ' . $order->customer_last_name,
			'country' => $order->billingAddress ? $order->billingAddress->country : '-',
			'date' => $order->created_at,
			'total' => format_money($order->totalPrice())
		];
	}

	public function modifyListingQuery(Builder $query)
	{
		$query
			->with('billingAddress', 'items')
			->latest();
	}

	public function getListingFilters()
	{
		$hiddenStates = $this->model->getStateMachine()->findStateWithProperty('show_in_default_filter', false);
		return [
			new DateFilter('created_at'),
			new ArrayListingFilter('state', $this->getStateOptions($hiddenStates), function($query, $value) use ($hiddenStates) {
				if ($value == 'all') {
					$query->whereNotIn('state', $hiddenStates);
				} else {
					$query->where('state', $value);
				}
			}, $this->getDefaultStatusFilter()),
			new SearchListingFilter('customer', function($q, $value) {
				foreach(explode(' ', $value) as $part) {
					$q->where(function($q) use ($part) {
						foreach(['transaction_id', 'customer_first_name', 'customer_last_name', 'customer_email'] as $attr) {
							$q->orWhere($attr, 'LIKE', "%$part%");
						}
					});
				}
			})
		];
	}

	protected function getDefaultStatusFilter()
	{
		return 'all';
	}

	protected function getStateOptions($hiddenStates = [])
	{
		$states = $this->model->getStateMachine()->getStates();

		return ['all' => 'All' . (count($hiddenStates) ? ' (except ' . implode(', ', $hiddenStates) . ')' : null)] + array_combine($states, $states);
	}

	protected function getAvailableStateOptions($order)
	{
		$transitions = $this->getStateTransitions()->toArray();
		return collect(array_combine($transitions, $transitions))->map(function($item) use ($order) {
			if ($order->getStateMachine()->can($item)) {
				return ucwords($item);
			}
		})->filter();
	}

	public function getLabel($model)
	{
		return sprintf('Order by %s %s on %s',
			$model->customer_first_name,
			$model->customer_last_name,
			$model->created_at->format('Y-m-d')
		);
	}

	public function getFields($instance)
	{
		return [
			new TextField(array('name' => 'transaction_id', 'disabled' => true)),
			new SelectField(array('name' => 'state_transition', 'label' => 'Order State', 'options' => $this->getAvailableStateOptions($instance)->prepend($instance->state))),
			new TextField(array('name' => 'customer_first_name', 'disabled' => true)),
			new TextField(array('name' => 'customer_last_name', 'disabled' => true)),
			new TextField(array('name' => 'customer_email', 'disabled' => true))
		];
	}

	/**
	 * Return the fields displayed on a bulk create/edit screen
	 *
	 * @param  $instances
	 * @return array
	 */
	public function getBulkFields($instances)
	{
		$options = ['' => '- Please Select -'] + call_user_func_array('array_intersect', $instances->map(function($order) {
			return $this->getAvailableStateOptions($order)->toArray();
		})->toArray());
		return [
			new SelectField(['name' => 'state_transition', 'label' => 'Order State', 'options' => $options]),
		];
	}
}
