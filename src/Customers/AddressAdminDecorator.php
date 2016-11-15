<?php

namespace Bozboz\Ecommerce\Orders\Customers;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Ecommerce\Orders\Customers\Addresses\Address;
use DB;

class AddressAdminDecorator extends ModelAdminDecorator
{
    public function __construct(Address $model)
    {
        parent::__construct($model);
    }

    public function getLabel($plural = false)
    {
        return 'test';
    }

    public function getFields($inst)
    {
        return [
            new HiddenField('customer_id'),
            new TextField('name'),
            new TextField('company'),
            new TextField('address_1'),
            new TextField('address_2'),
            new TextField('city'),
            new TextField('county'),
            new SelectField('country', [
                'options' => DB::table('countries')->pluck('country', 'code'),
                'value' => 'GB',
            ]),
            new TextField('postcode'),
        ];
    }

    public function getSyncRelations()
    {
        return ['customers'];
    }
}
