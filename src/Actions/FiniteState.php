<?php

namespace Bozboz\Ecommerce\Orders\Actions;

use Bozboz\Admin\Reports\Actions\DropdownAction;
use Bozboz\Ecommerce\Orders\Actions\Presenters\StateDropdown;

class FiniteState extends DropdownAction
{
    public function __construct($items)
    {
        parent::__construct($items, null);
    }

    public function output()
    {
        $attributes = $this->attributes;

        $stateMachine = $this->instance->getStateMachine();

        $label = $stateMachine->getCurrentState();
        $icon = '';

        $presenter = new StateDropdown($this->validItems, $label, $icon, $attributes);

        return $presenter->render();
    }

    protected function check()
    {
        parent::check();
        return true;
    }
}
