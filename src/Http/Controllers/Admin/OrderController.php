<?php

namespace Bozboz\Ecommerce\Orders\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\BulkAdminController;
use Bozboz\Admin\Reports\Actions\Action;
use Bozboz\Admin\Reports\Actions\Permissions\IsValid;
use Bozboz\Admin\Reports\Actions\Presenters\Form;
use Bozboz\Admin\Reports\Actions\Presenters\Link;
use Bozboz\Admin\Reports\CSVReport;
use Bozboz\Admin\Reports\Report;
use Bozboz\Ecommerce\Orders\Actions\Permissions\CanTransition;
use Bozboz\Ecommerce\Orders\Events\OrderStateTransition;
use Bozboz\Ecommerce\Orders\OrderDecorator;
use Bozboz\Ecommerce\Orders\Refund;
use Bozboz\Ecommerce\Payment\Exception as PaymentException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

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

	public function transitionState(Dispatcher $event, $id, $transition)
	{
		$instance = $this->decorator->findInstance($id);
		$instance->transitionState($transition);

		return $this->getUpdateResponse($instance)->with('model.updated', sprintf(
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
						new CanTransition([$this, 'canEdit'], $item)
					);
				})
			),
		], parent::getRowActions());
	}

	public function refund($orderId)
	{
		$order = $this->decorator->findInstance($orderId);
		$errors = [];

		try {
			$this->refund->process($order, Input::get('items'));
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
