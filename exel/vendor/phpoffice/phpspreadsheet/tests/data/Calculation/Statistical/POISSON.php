<?php

declare(strict_types=1);

return [
    [
        0.12465201948308113,
        2, 5, true,
    ],
    [
        0.08422433748856833,
        2, 5, false,
    ],
    [
        0.05191746860849132,
        20, 25, false,
    ],
    [
        0.24241419769010333,
        35, 40, true,
    ],
    [
        '#VALUE!',
        'Nan', 40, true,
    ],
    [
        '#VALUE!',
        35, 'Nan', true,
    ],
    [
        '#VALUE!',
        35, 40, 'Nan',
    ],
    'Value < 0' => [
        '#NUM!',
        -35, 40, true,
    ],
    'Mean < 0' => [
        '#NUM!',
        35, -40, true,
    ],
];
