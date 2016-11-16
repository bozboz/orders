<?php

return [
    'finite_state' => [
        'states' => [
            'Cart'    => ['type' => 'initial', 'properties' => ['show_in_default_filter' => false, 'allow_manual_transition' => false]],
            'Checkout' => ['type' => 'normal',  'properties' => ['show_in_default_filter' => false, 'allow_manual_transition' => false]],
            'Successful' => ['type' => 'final',   'properties' => ['user_complete' => true]],
            'Failed'  => ['type' => 'final',   'properties' => ['user_complete' => true]],
            'Pending'  => ['type' => 'final',   'properties' => ['user_complete' => true]],
        ],
        'transitions' => [
            'checkout' => ['from' => ['Cart', 'Checkout'],    'to' => 'Checkout'],
            'cart' => ['from' => ['Checkout'],    'to' => 'Cart'],
            'sucess'  => ['from' => ['Checkout'], 'to' => 'Successful'],
            'fail'  => ['from' => ['Checkout'], 'to' => 'Failed'],
            'pending'  => ['from' => ['Checkout'], 'to' => 'Pending'],
        ]
    ],
];