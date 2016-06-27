<?php

namespace Bozboz\Ecommerce\Orders\Listeners;

use Illuminate\Mail\Mailer;
use Illuminate\Config\Repository as Config;
use Bozboz\Ecommerce\Orders\Events\OrderStateTransition;

class Notify
{
    private $mailer, $config;

    public function __construct(Mailer $mailer, Config $config)
    {
        $this->mailer = $mailer;
        $this->config = $config;
    }

    public function handle(OrderStateTransition $event)
    {
        if (view()->exists($this->getView($event->transition))) {
            $order = $event->order;

            $data = $order->toArray();
            $data['lineItems'] = $order->items;
            $data['orderTotal'] = $order->totalPrice();
            $data['orderTax'] = $order->totalTax();

            $this->mailer->send($this->getView($event->transition), $data, function($message) use ($order)
            {
                $message->from('noreply@finecut.co.uk');
                $message->to($order->customer_email);
                if ($this->config->get('app.order_cc_email_address')) {
                    $message->bcc($this->config->get('app.order_cc_email_address'));
                }
                $message->subject(sprintf('%s - Your Order', $this->config->get('app.client_name')));
            });
        }
    }

    protected function getView($transition)
    {
        return 'ecommerce::emails.notifications.' . $transition;
    }
}
