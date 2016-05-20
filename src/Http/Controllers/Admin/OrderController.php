<?php

namespace Bozboz\Ecommerce\Orders\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Actions\Action;
use Bozboz\Admin\Reports\CSVReport;
use Bozboz\Admin\Reports\Report;
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

	// public function index()
	// {
	// 	$report = new Report($this->decorator, 'orders::admin.overview');
	// 	return $report->render(array(
	// 		'controller' => get_class($this),
	// 		'canCreate' => false
	// 	));
	// }

	protected function getReportActions()
	{
		return [
			// '@downloadCsv'
		];
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
