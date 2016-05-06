<?php

namespace Bozboz\Ecommerce\Orders\ListingFilters;

use Bozboz\Admin\Reports\Filters\ArrayListingFilter;
use DateTime;

class DateFilter extends ArrayListingFilter
{
	const TODAY = 1;
	const THIS_WEEK = 2;
	const THIS_MONTH = 3;
	const PAST_WEEK = 4;
	const PAST_MONTH = 5;

	public function __construct($default = self::PAST_WEEK)
	{
		parent::__construct('date', $this->getDateOptions(), [$this, 'callback'], $default);
	}

	public function callback($builder, $value)
	{
		$mapping = [
			self::TODAY => 'today',
			self::THIS_WEEK => 'monday this week',
			self::THIS_MONTH => 'first day of this month',
			self::PAST_WEEK => '-1 week',
			self::PAST_MONTH => '-1 month'
		];

		if (array_key_exists($value, $mapping)) {
			$builder->where('created_at', '>', new DateTime($mapping[$value]));
		}
	}

	protected function getDateOptions()
	{
		return [
			null => 'All',
			self::TODAY => 'Today',
			self::THIS_WEEK => 'This week',
			self::THIS_MONTH => 'This month',
			self::PAST_WEEK => 'Past week',
			self::PAST_MONTH => 'Past month',
		];
	}
}
