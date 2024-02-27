<?php

declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;

return [
    [
        '3.76344325733562+0.621384040306436j',
        '12.34+5.67j',
    ],
    [
        ExcelError::NAN(),
        'Invalid Complex Number',
    ],
    [
        '32.6586381298614+2.26618007108803i',
        '-12.34E-5+6.78E9i',
    ],
    [
        '2.10472668297646+0.894830857610216i',
        '3.5+2.5i',
    ],
    [
        '1.86396022742506+0.401501537958665i',
        '3.5+i',
    ],
    [
        '1.80735492219671',
        '3.5',
    ],
    [
        '1.86396022742506-0.401501537958665i',
        '3.5-i',
    ],
    [
        '2.10472668297646-0.894830857610216i',
        '3.5-2.5i',
    ],
    [
        '1.42899049767377+1.71722540775913i',
        '1+2.5i',
    ],
    [
        '0.500000000038482+1.13309003554401i',
        '1+i',
    ],
    [
        '0',
        '1',
    ],
    [
        '0.500000000038482-1.13309003554401i',
        '1-i',
    ],
    [
        '1.42899049767377-1.71722540775913i',
        '1-2.5i',
    ],
    [
        '1.3219280949891+2.26618007108801i',
        '2.5i',
    ],
    [
        '2.26618007108801i',
        'i',
    ],
    [
        ExcelError::NAN(),
        '0',
    ],
    [
        '-2.26618007108801i',
        '-i',
    ],
    [
        '1.3219280949891-2.26618007108801i',
        '-2.5i',
    ],
    [
        '1.42899049767377+2.81513473441689i',
        '-1+2.5i',
    ],
    [
        '0.500000000038482+3.39927010663201i',
        '-1+i',
    ],
    [
        '4.53236014217602i',
        '-1',
    ],
    [
        '0.500000000038482-3.39927010663201i',
        '-1-i',
    ],
    [
        '1.42899049767377-2.81513473441689i',
        '-1-2.5i',
    ],
    [
        '2.10472668297646+3.63752928456581i',
        '-3.5+2.5i',
    ],
    [
        '1.86396022742506+4.13085860421736i',
        '-3.5+i',
    ],
    [
        '1.80735492219671+4.53236014217602i',
        '-3.5',
    ],
    [
        '1.86396022742506-4.13085860421736i',
        '-3.5-i',
    ],
    [
        '2.10472668297646-3.63752928456581i',
        '-3.5-2.5i',
    ],
];
