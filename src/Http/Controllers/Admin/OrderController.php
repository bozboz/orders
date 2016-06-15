<?php

namespace Bozboz\Ecommerce\Orders\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Actions\Action;
use Bozboz\Admin\Reports\Actions\Presenters\Form;
use Bozboz\Admin\Reports\CSVReport;
use Bozboz\Admin\Reports\Report;
use Bozboz\Ecommerce\Orders\Actions\Permissions\CanTransition;
use Bozboz\Ecommerce\Orders\OrderDecorator;
use Bozboz\Ecommerce\Orders\Refund;
use Bozboz\Ecommerce\Payment\Exception as PaymentException;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class OrderController extends ModelAdminController
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
			// '@downloadCsv'
		];
	}

	public function transitionState($id, $transition)
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
}
