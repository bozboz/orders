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
        $this->app->bind('cart', Bozboz\Ecommerce\Orders\Cart\Cart::class);

        $this->app->bind(
            Bozboz\Ecommerce\Orders\Cart\Contracts\CartStorage::class,
            Bozboz\Ecommerce\Orders\Cart\SessionStorage::class
        );
    }

    public function boot()
    {
        $packageRoot = __DIR__ . '/../..';

        if (! $this->app->routesAreCached()) {
            require "{$packageRoot}/src/Http/routes.php";
        }

        $this->publishes([
            "{$packageRoot}/database/migrations/" => database_path('migrations')
        ], 'migrations');

        $this->loadViewsFrom("{$packageRoot}/resources/views/", 'orders');

        $this->loadTranslationsFrom("{$packageRoot}", 'products');

        $this->buildAdminMenu();
    }

    private function buildAdminMenu()
    {
        $event = $this->app['events'];

        $event->listen('admin.renderMenu', function($menu)
        {
            $url = $this->app['url'];
            $lang = $this->app['translator'];

            $menu[$lang->get('ecommerce::ecommerce.menu_name')] = [
                'Orders' => $url->route('admin.orders.index'),
                'Customers' => $url->route('admin.customers.index'),
            ];
        });
    }
}
