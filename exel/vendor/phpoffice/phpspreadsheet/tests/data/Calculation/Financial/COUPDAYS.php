<?php

declare(strict_types=1);

// Settlement, Maturity, Frequency, Basis, Result

return [
    [
        181,
        '25-Jan-2007',
        '15-Nov-2008',
        2,
        1,
    ],
    [
        90,
        '2011-01-01',
        '2012-10-25',
        4,
    ],
    [
        90,
        '2011-01-01',
        '2012-10-25',
        4,
        null,
    ],
    [
        182.5,
        '25-Jan-2007',
        '15-Nov-2008',
        2,
        3,
    ],
    [
        365,
        '25-Jan-2007',
        '15-Nov-2008',
        1,
        1,
    ],
    [
        365,
        '25-Jan-2010',
        '15-Nov-2011',
        1,
        1,
    ],
    [
        '#VALUE!',
        'Invalid Date',
        '15-Nov-2008',
        2,
        1,
    ],
    [
        '#VALUE!',
        '25-Jan-2007',
        'Invalid Date',
        2,
        1,
    ],
    'Invalid Frequency' => [
        '#NUM!',
        '25-Jan-2007',
        '15-Nov-2008',
        3,
        1,
    ],
    'Non-Numeric Frequency' => [
        '#VALUE!',
        '25-Jan-2007',
        '15-Nov-2008',
        'NaN',
        1,
    ],
    'Invalid Basis' => [
        '#NUM!',
        '25-Jan-2007',
        '15-Nov-2008',
        4,
        -1,
    ],
    'Non-Numeric Basis' => [
        '#VALUE!',
        '25-Jan-2007',
        '15-Nov-2008',
        4,
        'NaN',
    ],
    'Same Date' => [
        '#NUM!',
        '24-Dec-2000',
        '24-Dec-2000',
        4,
        0,
    ],
    [
        360,
        '31-Jan-2021',
        '20-Mar-2021',
        1,
        0,
    ],
    [
        365,
        '31-Jan-2021',
        '20-Mar-2021',
        1,
        1,
    ],
    [
        366,
        '31-Jan-2020',
        '20-Mar-2021',
        1,
        1,
    ],
    [
        360,
        '31-Jan-2021',
        '20-Mar-2021',
        1,
        2,
    ],
    [
        365,
        '31-Jan-2021',
        '20-Mar-2021',
        1,
        3,
    ],
    [
        360,
        '31-Jan-2021',
        '20-Mar-2021',
        1,
        4,
    ],
    [
        180,
        '31-Jan-2021',
        '20-Mar-2021',
        2,
        0,
    ],
    [
        181,
        '31-Jan-2021',
        '20-Mar-2021',
        2,
        1,
    ],
    [
        182,
        '31-Jan-2020',
        '20-Mar-2021',
        2,
        1,
    ],
    [
        180,
        '31-Jan-2021',
        '20-Mar-2021',
        2,
        2,
    ],
    [
        182.5,
        '31-Jan-2021',
        '20-Mar-2021',
        2,
        3,
    ],
    [
        180,
        '31-Jan-2021',
        '20-Mar-2021',
        2,
        4,
    ],
    [
        90,
        '31-Jan-2021',
        '20-Mar-2021',
        4,
        0,
    ],
    [
        90,
        '31-Jan-2021',
        '20-Mar-2021',
        4,
        1,
    ],
    [
        91,
        '31-Jan-2020',
        '20-Mar-2021',
        4,
        1,
    ],
    [
        90,
        '31-Jan-2021',
        '20-Mar-2021',
        4,
        2,
    ],
    [
        91.25,
        '31-Jan-2021',
        '20-Mar-2021',
        4,
        3,
    ],
    [
        90,
        '31-Jan-2021',
        '20-Mar-2021',
        4,
        4,
    ],
    [
        180,
        '05-Apr-2019',
        '30-Sep-2021',
        2,
        0,
    ],
    [
        180,
        '05-Oct-2019',
        '31-Mar-2022',
        2,
        0,
    ],
];