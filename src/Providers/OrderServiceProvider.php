<?php

namespace Bozboz\Ecommerce\Orders\Providers;

use Bozboz\Ecommerce\Orders\Actions\FiniteState;
use Bozboz\Ecommerce\Orders\Cart\Cart;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    protected $listen = [
        'Bozboz\Ecommerce\Orders\Events\OrderComplete' => [
            'Bozboz\Ecommerce\Orders\Customers\Addresses\LinkAddressToCustomer',
        ],
    ];

    protected $subscribe = [
        'Bozboz\Ecommerce\Orders\Listeners\OrderEmail',
    ];

    public function register()
    {
    }

    public function boot()
    {
        $packageRoot = __DIR__ . '/../..';

        $this->publishes([
            "$packageRoot/database/migrations/" => database_path('migrations'),
            "$packageRoot/resources/views/" => base_path('resources/views/vendor/orders'),
            "$packageRoot/config/orders.php" => config_path('orders.php'),
        ]);

        require("$packageRoot/helpers.php");

        $this->mergeConfigFrom(
            "$packageRoot/config/default.php", 'orders'
        );

        $this->loadViewsFrom("$packageRoot/resources/views/", 'orders');

        $this->app['view']->composer(
            'orders::cart.summary', 'Bozboz\Ecommerce\Orders\Cart\CartComposer'
        );

        $this->registerActions();
    }

    protected function registerActions()
    {
        $actions = $this->app['admin.actions'];

        $actions->register('finite_state', function($items) {
            return new FiniteState($items);
        });
    }
}
