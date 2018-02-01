<?php

namespace Bozboz\Ecommerce\Orders;

use Bozboz\Ecommerce\Checkout\Checkoutable;

class CheckoutableOrder extends Order implements Checkoutable
{
    /**
     * Set the current screen on the checkoutable instance
     *
     * @param $screenAlias string
     */
    public function markScreenAsComplete($screenAlias)
    {
        $this->checkout_progress = $screenAlias;
        $this->save();
    }

    /**
     * Get the current screen the checkoutable instance is up to
     *
     * @return string
     */
    public function getCompletedScreen()
    {
        return $this->checkout_progress;
    }

    public function isComplete()
    {
        return $this->getStateMachine()->getCurrentState()->isFinal();
    }
}
