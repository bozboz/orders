<?php

namespace Bozboz\Ecommerce\Orders;

use Bozboz\Admin\Base\ModelAdminDecorator;


class StateDecorator extends ModelAdminDecorator
{
    public function __construct(State $model)
    {
        parent::__construct($model);
    }

    public function getColumns($order)
    {
        return [];
    }

    public function getLabel($model)
    {
        return $model->name;
    }

    public function getFields($instance)
    {
        return [];
    }
}
