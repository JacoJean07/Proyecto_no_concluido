<?php

declare(strict_types=1);

return [
    [
        90.0,
        ['01-Apr-2017', '31-Mar-2021', 0.025, 100],
    ],
    [
        97.625,
        ['2008-02-15', '2008-11-30', 0.03, 100, null],
    ],
    [
        97.6311475409836,
        ['2008-02-15', '2008-11-30', 0.03, 100, 1],
    ],
    [
        '#VALUE!',
        ['Invalid Date', '2008-11-30', 0.03, 100, 1],
    ],
    [
        '#VALUE!',
        ['2008-02-15', 'Invalid Date', 0.03, 100, 1],
    ],
    [
        '#VALUE!',
        ['2008-02-15', '2008-11-30', 'NaN', 100, 1],
    ],
    [
        '#NUM!',
        ['2008-02-15', '2008-11-30', -0.03, 100, 1],
    ],
    [
        '#VALUE!',
        ['2008-02-15', '2008-11-30', 0.03, 'NaN', 1],
    ],
    [
        '#NUM!',
        ['2008-02-15', '2008-11-30', 0.03, -100, 1],
    ],
    [
        '#VALUE!',
        ['2008-02-15', '2008-11-30', 0.03, 100, 'NaN'],
    ],
    [
        '#NUM!',
        ['2008-02-15', '2008-11-30', 0.03, 100, -1],
    ],
];