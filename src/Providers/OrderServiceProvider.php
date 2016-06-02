<?php

namespace Bozboz\Ecommerce\Orders\Providers;

use Bozboz\Ecommerce\Orders\Cart\Cart;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    protected $listen = [
        'order.completed' => [
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
            "{$packageRoot}/database/migrations/" => database_path('migrations'),
            "$packageRoot/resources/views/" => base_path('resources/views/vendor/orders')
        ]);

        require("$packageRoot/helpers.php");

        $this->loadViewsFrom("{$packageRoot}/resources/views/", 'orders');

        $this->loadTranslationsFrom("{$packageRoot}", 'products');

        $this->app['view']->composer(
            'orders::cart.summary', 'Bozboz\Ecommerce\Orders\Cart\CartComposer'
        );
    }
}
