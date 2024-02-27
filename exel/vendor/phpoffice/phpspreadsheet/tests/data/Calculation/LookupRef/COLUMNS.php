<?php

declare(strict_types=1);

return [
    [
        1,
        null,
    ],
    [
        1,
        '',
    ],
    [
        '#VALUE!',
        'foo',
    ],
    [
        0,
        [],
    ],
    [
        1,
        [1],
    ],
    [
        1,
        [1, 1],
    ],
    [
        2,
        [[1, 1]],
    ],
    [
        1,
        ['a' => [1, 1]],
    ],
];
