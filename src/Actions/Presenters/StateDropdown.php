<?php

namespace Bozboz\Ecommerce\Orders\Actions\Presenters;

use Bozboz\Admin\Reports\Actions\Presenters\Dropdown;

class StateDropdown extends Dropdown
{
	public function getView()
	{
		return 'orders::admin.report-actions.state-dropdown';
	}
}
