<?php

namespace Bozboz\Ecommerce\Orders;

use Illuminate\Validation\Validator;

class Exception extends \Exception
{
	protected $validator;

	/**
	 * @param  Illuminate\Support\Contracts\MessageProviderInterface $validator
	 */
	public function __construct(Validator $validator)
	{
		$this->validator = $validator;
	}

	/**
	 * @return Illuminate\Support\MessageBag
	 */
	public function getErrors()
	{
		return $this->validator->getMessageBag();
	}
}
