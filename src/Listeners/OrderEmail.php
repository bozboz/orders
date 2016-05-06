<?php

namespace Bozboz\Ecommerce\Orders\Listeners;

use Illuminate\Mail\Mailer;
use Illuminate\Config\Repository as Config;

class OrderEmail
{
    private $mailer, $config;

    public function __construct(Mailer $mailer, Config $config)
    {
        $this->mailer = $mailer;
        $this->config = $config;
    }

    public function subscribe($events)
    {
        $events->listen('order.completed', __CLASS__ . '@onOrderCompleted');
    }

    public function onOrderCompleted(Order\Order $order)
    {
        $data = $order->toArray();
        $data['lineItems'] = $order->items;
        $data['orderTotal'] = $order->totalPrice();
        $data['orderTax'] = $order->totalTax();

        $this->mailer->send('emails.orders.confirmation', $data, function($message) use ($order)
        {
            $message->to($order->customer_email);
            if ($this->config->get('app.order_cc_email_address')) {
                $message->bcc($this->config->get('app.order_cc_email_address'));
            }
            $message->subject(sprintf('%s - Your Order', $this->config->get('app.client_name')));
        });
    }
}
