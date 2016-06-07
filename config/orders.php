<?php

return [
    'finite_state' => [
        'states' => [
            'cart'    => ['type' => 'initial', 'properties' => []],
            'checkout' => ['type' => 'normal',  'properties' => []],
            'successful' => ['type' => 'final',   'properties' => ['user_complete' => true]],
            'failed'  => ['type' => 'final',   'properties' => ['user_complete' => true]],
            'pending'  => ['type' => 'final',   'properties' => ['user_complete' => true]],
        ],
        'transitions' => [
            'checkout' => ['from' => ['cart'],    'to' => 'checkout'],
            'cart' => ['from' => ['checkout'],    'to' => 'cart'],
            'sucess'  => ['from' => ['checkout'], 'to' => 'successful'],
            'fail'  => ['from' => ['checkout'], 'to' => 'failed'],
            'pending'  => ['from' => ['checkout'], 'to' => 'pending'],
        ]
    ],
];