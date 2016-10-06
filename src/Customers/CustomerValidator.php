<?php

namespace Bozboz\Ecommerce\Orders\Customers;

use Bozboz\Admin\Services\Validators\UserValidator;

class CustomerValidator extends UserValidator
{
    protected $storeRules = [
        'password' => 'required|min:8',
    ];
}