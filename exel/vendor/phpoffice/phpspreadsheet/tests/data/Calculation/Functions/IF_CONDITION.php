<?php

declare(strict_types=1);

return [
    [
        '<"A"',
        '<A',
    ],
    [
        '>"A"',
        '>A',
    ],
    [
        '<="A"',
        '<=A',
    ],
    [
        '>"A"',
        '>A',
    ],
    [
        '>="A"',
        '>=A',
    ],
    [
        '<>"A"',
        '<>A',
    ],
    [
        '<"<A"',
        '<<A',
    ],
    [
        '="A"',
        '=A',
    ],
    [
        '="""A"""',
        '="A"',
    ],
    [
        '="""A""B"""',
        '="A"B"',
    ],
    [
        '<>"< PLEASE SELECT >"',
        '<>< Please Select >',
    ],
    [
        '<>""',
        '<>',
    ],
    [
        '=""',
        '""',
    ],
];