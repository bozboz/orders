<?php

namespace Bozboz\Ecommerce\Orders\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Actions\Permissions\IsValid;
use Bozboz\Admin\Reports\Actions\Presenters\Link;
use Bozboz\Admin\Reports\Actions\Presenters\Urls\Url;
use Bozboz\Ecommerce\Orders\Customers\AddressAdminDecorator;
use Bozboz\Ecommerce\Orders\Customers\CustomerAdminDecorator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use DB;

class AddressController extends ModelAdminController
{
    private $customerController;
    private $customerDecorator;

    protected $useActions = true;

    public function __construct(
        AddressAdminDecorator $decorator,
        CustomerController $customerController,
        CustomerAdminDecorator $customerDecorator
    )
    {
        parent::__construct($decorator);
        $this->customerController = $customerController;
        $this->customerDecorator = $customerDecorator;
    }

    public function createForCustomer($customer)
    {
        $address = $this->decorator->newModelInstance();

        if ( ! $this->canCreate($address)) App::abort(403);

        $customer = $this->customerDecorator->findInstance($customer);
        $address->customer()->associate($customer);

        return $this->renderFormFor($address, $this->createView, 'POST', 'store');
    }

    protected function save($modelInstance, $input)
    {
        $modelInstance->fill($input);

        if ( ! $this->canCreate($modelInstance)) App::abort(403);

        $modelInstance->save();
        $this->decorator->updateRelations($modelInstance, request()->input());
    }

    public function updateForCustomer(Request $request, $address)
    {
        $address = $this->decorator->findInstance($address);

        if ( ! $this->canEdit($address)) App::abort(403);

        if ($address->orders->count()) {
            $customer = $address->customer;
            $address->customer()->dissociate()->save();
            $address = $customer->addresses()->create($request->except('after_save'));
        } else {
            $this->saveInTransaction($address, $request->except('after_save'));
        }

        return $this->getSuccessResponse($address);
    }

    public function destroyForCustomer(Request $request, $address)
    {
        $address = $this->decorator->findInstance($address);
        $addressForResponse = $address->replicate();

        if ( ! $this->canDestroy($address)) App::abort(403);

        if ($address->orders->count()) {
            $address->customer()->dissociate()->save();
        } else {
            $address->delete();
        }

        return $this->getSuccessResponse($addressForResponse);
    }

    protected function getSuccessResponse($instance)
    {
        return Redirect::action('\\' . get_class($this->customerController) . '@edit', $instance->customer_id);
    }

    protected function getFormActions($instance)
    {
        return [
            $this->actions->submit('Save', 'fa fa-save', [
                'name' => 'after_save',
                'value' => 'exit',
            ]),
            $this->actions->custom(
                new Link(new Url(action('\\' . get_class($this->customerController) . '@edit', $instance->customer_id)), 'Back to listing', 'fa fa-list-alt', [
                    'class' => 'btn-default pull-right space-left',
                ]),
                new IsValid([$this, 'canView'])
            ),
        ];
    }

    public function viewPermissions($stack)
    {
        $stack->add('ecommerce');
    }

    public function createPermissions($stack, $instance)
    {
        $stack->add('ecommerce', $instance);
    }

    public function editPermissions($stack, $instance)
    {
        $stack->add('ecommerce', $instance);
    }

    public function deletePermissions($stack, $instance)
    {
        $stack->add('ecommerce', $instance);
    }
}
