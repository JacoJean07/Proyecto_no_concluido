<?php

declare(strict_types=1);

return [
    [
        3.5,
        [1, 2, 4, 7, 8, 9, 10, 12],
        1,
    ],
    [
        5.4,
        [10.5, 7.2, 200, 5.4, 8.1],
        0,
    ],
    [
        7.2,
        [10.5, 7.2, 200, 5.4, 8.1],
        1,
    ],
    [
        8.1,
        [10.5, 7.2, 200, 5.4, 8.1],
        2,
    ],
    [
        10.5,
        [10.5, 7.2, 200, 5.4, 8.1],
        3,
    ],
    [
        200,
        [10.5, 7.2, 200, 5.4, 8.1],
        4,
    ],
    [
        7.75,
        [7, 8, 9, 10],
        1,
    ],
    [
        8.5,
        [7, 8, 9, 10],
        2,
    ],
    [
        9.25,
        [7, 8, 9, 10],
        3,
    ],
    [
        '#NUM!',
        [7, 8, 9, 10],
        -1,
    ],
    [
        '#NUM!',
        [7, 8, 9, 10],
        5,
    ],
    [
        '#VALUE!',
        [7, 8, 9, 10],
        'X',
    ],
];
