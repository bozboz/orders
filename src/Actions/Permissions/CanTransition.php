<?php

namespace Bozboz\Ecommerce\Orders\Actions\Permissions;

class CanTransition
{
	private $permission;
    private $transition;

	public function __construct(callable $permission, $transition)
	{
		$this->permission = $permission;
        $this->transition = $transition;
	}

	public function check($instance)
	{
		return call_user_func($this->permission, $instance) && $instance->getStateMachine()->can($this->transition);
	}
}
