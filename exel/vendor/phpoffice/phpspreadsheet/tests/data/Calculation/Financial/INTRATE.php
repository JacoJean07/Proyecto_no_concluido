<?php

declare(strict_types=1);

// Settlement, Maturity, Investment, Redemption, Basis, Result

return [
    [
        0.05768,
        '2008-02-15',
        '2008-05-15',
        1000000,
        1014420,
        2,
    ],
    [
        0.225,
        '2005-04-01',
        '2010-03-31',
        1000,
        2125,
    ],
    [
        0.225,
        '2005-04-01',
        '2010-03-31',
        1000,
        2125,
        null,
    ],
    [
        '#VALUE!',
        '2008-02-15',
        '2008-05-15',
        1000000,
        1014420,
        'ABC',
    ],
    [
        '#NUM!',
        '2008-02-15',
        '2008-05-15',
        1000000,
        -1014420,
        2,
    ],
    [
        '#VALUE!',
        'Invalid Date',
        '2008-05-15',
        1000000,
        1014420,
        2,
    ],
];