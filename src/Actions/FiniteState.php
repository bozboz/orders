<?php

namespace Bozboz\Ecommerce\Orders\Actions;

use Bozboz\Admin\Reports\Actions\DropdownAction;
use Bozboz\Admin\Reports\Actions\Presenters\Dropdown;

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

        $presenter = new Dropdown($this->validItems, $label, $icon, $attributes);

        return $presenter->render();
    }
}
