<?php

return [
    'finite_state' => [
        'states' => [
            'cart'    => ['type' => 'initial', 'properties' => []],
            'checkout' => ['type' => 'normal',  'properties' => []],
            'completed' => ['type' => 'final',   'properties' => []],
            'failed'  => ['type' => 'final',   'properties' => []],
            'pending'  => ['type' => 'final',   'properties' => []],
        ],
        'transitions' => [
            'checkout' => ['from' => ['cart'],    'to' => 'checkout'],
            'cart' => ['from' => ['checkout'],    'to' => 'cart'],
            'complete'  => ['from' => ['checkout'], 'to' => 'complete'],
            'fail'  => ['from' => ['checkout'], 'to' => 'fail'],
            'pending'  => ['from' => ['checkout'], 'to' => 'pending'],
        ]
    ],
];