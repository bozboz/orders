<?php

return [
    'finite_state' => [
        'states' => [
            'Cart'    => ['type' => 'initial', 'properties' => ['show_in_default_filter' => false, 'disallow_manual_transition' => true]],
            'Checkout' => ['type' => 'normal',  'properties' => ['show_in_default_filter' => false, 'disallow_manual_transition' => true]],
            'Successful' => ['type' => 'final',   'properties' => ['user_complete' => true]],
            'Failed'  => ['type' => 'final',   'properties' => ['user_complete' => true]],
            'Pending'  => ['type' => 'final',   'properties' => ['user_complete' => true]],
            'Refunded'   => ['type' => 'final',   'properties' => ['user_complete' => true, 'disallow_manual_transition' => true]],
        ],
        'transitions' => [
            'checkout' => ['from' => ['Cart', 'Checkout'], 'to' => 'Checkout'],
            'cart'     => ['from' => ['Checkout'],         'to' => 'Cart'],
            'success'  => ['from' => ['Checkout'],         'to' => 'Successful'],
            'fail'     => ['from' => ['Checkout'],         'to' => 'Failed'],
            'pending'  => ['from' => ['Checkout'],         'to' => 'Pending'],
            'refund'   => ['from' => ['Cart'],             'to' => 'Refunded'],
        ]
    ],
];
