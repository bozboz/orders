<?php

return [
    'finite_state' => [
        'states' => [
            'Cart'    => ['type' => 'initial', 'properties' => []],
            'Checkout' => ['type' => 'normal',  'properties' => []],
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