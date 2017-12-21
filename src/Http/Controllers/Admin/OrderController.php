<?php

namespace Bozboz\Ecommerce\Orders\Http\Controllers\Admin;

use Bozboz\Admin\Reports\Report;
use Bozboz\Admin\Reports\CSVReport;
use Bozboz\Ecommerce\Orders\Refund;
use Illuminate\Support\Facades\App;
use Bozboz\Permissions\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Bozboz\Admin\Reports\Actions\Action;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Bozboz\Ecommerce\Orders\OrderDecorator;
use Illuminate\Contracts\Events\Dispatcher;
use Bozboz\Admin\Reports\Actions\Presenters\Form;
use Bozboz\Admin\Reports\Actions\Presenters\Link;
use Bozboz\Admin\Reports\Actions\Permissions\IsValid;
use Bozboz\Admin\Http\Controllers\BulkAdminController;
use Bozboz\Ecommerce\Orders\Events\OrderStateTransition;
use Bozboz\Ecommerce\Payment\Exception as PaymentException;
use Bozboz\Ecommerce\Orders\Actions\Permissions\CanTransition;

class OrderController extends BulkAdminController
{
	protected $useActions = true;

	protected $editView = 'orders::admin.edit';
	private $refund;

	public function __construct(OrderDecorator $decorator, Refund $refund)
	{
		$this->refund = $refund;

		parent::__construct($decorator);
	}

	protected function getReportActions()
	{
		return [
			$this->actions->custom(
				new Form(
					$this->getActionName('bulkEdit'),
					'Bulk Edit',
					'fa fa-pencil',
					['class' => 'btn-info btn-sm'],
					['class' => 'pull-right space-left js-bulk-update']
				),
				new IsValid([$this, 'canView'])
			),
			$this->actions->custom(
				new Link(
					$this->getActionName('downloadCsv'),
					'Download CSV',
					'fa fa-download',
					['class' => 'btn-primary pull-right']
				),
				new IsValid([$this, 'canView'])
			),
		];
	}

	protected function renderFormFor($instance, $view, $method, $action)
	{
		return parent::renderFormFor($instance, $view, $method, $action)
			->with(['canRefund' => $this->canRefund($instance)]);
	}

	public function transitionState(Dispatcher $event, $id, $transition)
	{
		$instance = $this->decorator->findInstance($id);
		$instance->transitionState($transition);

		return redirect()->back()->with('model.updated', sprintf(
			'Successfully updated "%s"',
			$this->decorator->getLabel($instance)
		));
	}

	public function getRowActions()
	{
		$items = collect($this->decorator->getStateTransitions());
		return array_merge([
			$this->actions->finite_state(
				$items->map(function($item) {
					return $this->actions->custom(
						new Form([$this->getActionName('transitionState'), ['transition' => $item]], ucwords($item)),
						new CanTransition([$this, 'canTransition'], $item)
					);
				})
			),
		], parent::getRowActions());
	}

	public function refund($orderId)
	{
		$order = $this->decorator->findInstance($orderId);
		$errors = [];

		if ( ! $this->canRefund($order)) return App::abort(403);

		try {
			$this->refund->process($order, Input::get('items'), Input::has('dummy-refund'));
		} catch (PaymentException $e) {
			$errors['refund'] = $e->getMessage();
		}

		return Redirect::back()->withErrors($errors);
	}

	public function downloadCsv()
	{
		$report = new CSVReport($this->decorator);
		return $report->render();
	}

	public function canTransition($instance)
	{
        return ! $instance->getStateMachine()->getCurrentState()->has('disallow_manual_transition') && $this->canEdit($instance);
	}

	public function canRefund($instance)
	{
		return $this->canEdit($instance) && $instance->relatedOrders->isEmpty() && $instance->state !== 'Refunded';
	}

    protected function viewPermissions($stack)
    {
        $stack->add('ecommerce');
    }

    protected function createPermissions($stack, $instance)
    {
        $stack->add('ecommerce', $instance);
    }

    protected function editPermissions($stack, $instance)
    {
        $stack->add('ecommerce', $instance);
    }

    protected function deletePermissions($stack, $instance)
    {
        $stack->add('ecommerce', $instance);
    }
}
