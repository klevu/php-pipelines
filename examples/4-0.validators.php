<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/** @noinspection PhpRedundantOptionalArgumentInspection */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Validation;
use Klevu\Pipelines\Model\ValidationIterator;
use Klevu\Pipelines\Validator\CompositeIterate;
use Klevu\Pipelines\Validator\IsCurrencyCode;
use Klevu\Pipelines\Validator\IsEmail;
use Klevu\Pipelines\Validator\IsIpAddress;
use Klevu\Pipelines\Validator\IsNotEmpty;
use Klevu\Pipelines\Validator\IsNumeric;
use Klevu\Pipelines\Validator\IsPositiveNumber;
use Klevu\Pipelines\Validator\IsString;
use Klevu\Pipelines\Validator\IsUrl;
use Klevu\Pipelines\Validator\IsValidDate;
use Klevu\Pipelines\Validator\MatchesRegex;

# SIMPLE VALIDATORS

## IsCurrencyCode
$validator = new IsCurrencyCode();
$data = [
    null,
    'GBP',
    '£',
    ['USD'],
];
$arguments = null;
echo '# IsCurrencyCode' . PHP_EOL;
foreach ($data as $itemToValidate) {
    echo json_encode($itemToValidate) . ' : ';
    try {
        $validator->validate(
            data: $itemToValidate,
            arguments: $arguments,
            context: [],
        );
        echo 'Is Valid.';
    } catch (ValidationException $exception) {
        echo 'Is Not Valid.' . PHP_EOL;
        echo '(' . $exception::class . ') ' . $exception->getMessage() . PHP_EOL;
        echo '  * ' . implode(PHP_EOL . '  * ', $exception->getErrors());
    }
    echo PHP_EOL . PHP_EOL;
}
echo PHP_EOL . "---" . PHP_EOL;

## IsEmail
$validator = new IsEmail();
$data = [
    null,
    'contact@klevu.com',
    'test+1234@example.com',
    'foo',
    ['bar'],
];
$arguments = null;
echo '# IsEmail' . PHP_EOL;
foreach ($data as $itemToValidate) {
    echo json_encode($itemToValidate) . ' : ';
    try {
        $validator->validate(
            data: $itemToValidate,
            arguments: $arguments,
            context: [],
        );
        echo 'Is Valid.';
    } catch (ValidationException $exception) {
        echo 'Is Not Valid.' . PHP_EOL;
        echo '(' . $exception::class . ') ' . $exception->getMessage() . PHP_EOL;
        echo '  * ' . implode(PHP_EOL . '  * ', $exception->getErrors());
    }
    echo PHP_EOL . PHP_EOL;
}
echo PHP_EOL . "---" . PHP_EOL;

## IsIpAddress
$validator = new IsIpAddress();
$data = [
    null,
    '142.250.200.36',
    '127.0.0.1',
    '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
    'foo',
    ['bar'],
];
$arguments = new ArgumentIterator([
    new Argument(
        value: new ArgumentIterator([
            new Argument('IPv4'),
            new Argument('IPv6'),
        ]),
        key: IsIpAddress::ARGUMENT_INDEX_ALLOW_VERSIONS,
    ),
    new Argument(
        value: true,
        key: IsIpAddress::ARGUMENT_INDEX_ALLOW_PRIVATE_AND_RESERVED,
    ),
]);
echo '# IsIpAddress' . PHP_EOL;
foreach ($data as $itemToValidate) {
    echo json_encode($itemToValidate) . ' : ';
    try {
        $validator->validate(
            data: $itemToValidate,
            arguments: $arguments,
            context: [],
        );
        echo 'Is Valid.';
    } catch (ValidationException $exception) {
        echo 'Is Not Valid.' . PHP_EOL;
        echo '(' . $exception::class . ') ' . $exception->getMessage() . PHP_EOL;
        echo '  * ' . implode(PHP_EOL . '  * ', $exception->getErrors());
    }
    echo PHP_EOL . PHP_EOL;
}
echo PHP_EOL . "---" . PHP_EOL;

## IsNotEmpty
$validator = new IsNotEmpty();
$data = [
    null,
    0,
    0.0,
    false,
    '',
    '0',
    [],
    ' ',
    'foo',
    1,
    new \stdClass(),
];
$arguments = null;
echo '# IsNotEmpty' . PHP_EOL;
foreach ($data as $itemToValidate) {
    echo '(' . get_debug_type($itemToValidate) . ') ' . json_encode($itemToValidate) . ' : ';
    try {
        $validator->validate(
            data: $itemToValidate,
            arguments: $arguments,
            context: [],
        );
        echo 'Is Valid.';
    } catch (ValidationException $exception) {
        echo 'Is Not Valid.' . PHP_EOL;
        echo '(' . $exception::class . ') ' . $exception->getMessage() . PHP_EOL;
        echo '  * ' . implode(PHP_EOL . '  * ', $exception->getErrors());
    }
    echo PHP_EOL . PHP_EOL;
}
echo PHP_EOL . "---" . PHP_EOL;

## IsNumeric
$validator = new IsNumeric();
$data = [
    null,
    42,
    3.14,
    '-123.45',
    [1],
    'foo',
];
$arguments = new ArgumentIterator([
    new Argument(
        value: true,
        key: IsNumeric::ARGUMENT_INDEX_DECIMAL_ONLY,
    ),
]);
echo '# IsNumeric' . PHP_EOL;
foreach ($data as $itemToValidate) {
    echo json_encode($itemToValidate) . ' : ';
    try {
        $validator->validate(
            data: $itemToValidate,
            arguments: $arguments,
            context: [],
        );
        echo 'Is Valid.';
    } catch (ValidationException $exception) {
        echo 'Is Not Valid.' . PHP_EOL;
        echo '(' . $exception::class . ') ' . $exception->getMessage() . PHP_EOL;
        echo '  * ' . implode(PHP_EOL . '  * ', $exception->getErrors());
    }
    echo PHP_EOL . PHP_EOL;
}
echo PHP_EOL . "---" . PHP_EOL;

## IsPositiveNumber
$validator = new IsPositiveNumber();
$data = [
    null,
    42,
    '3.14',
    0,
    -1,
    'foo',
    ['bar'],
];
$arguments = new ArgumentIterator([
    new Argument(
        value: true,
        key: IsPositiveNumber::ARGUMENT_INDEX_ALLOW_ZERO,
    ),
]);
echo '# IsPositiveNumber' . PHP_EOL;
foreach ($data as $itemToValidate) {
    echo json_encode($itemToValidate) . ' : ';
    try {
        $validator->validate(
            data: $itemToValidate,
            arguments: $arguments,
            context: [],
        );
        echo 'Is Valid.';
    } catch (ValidationException $exception) {
        echo 'Is Not Valid.' . PHP_EOL;
        echo '(' . $exception::class . ') ' . $exception->getMessage() . PHP_EOL;
        echo '  * ' . implode(PHP_EOL . '  * ', $exception->getErrors());
    }
    echo PHP_EOL . PHP_EOL;
}
echo PHP_EOL . "---" . PHP_EOL;

## IsString
$validator = new IsString();
$data = [
    null,
    'foo',
    '',
    [''],
];
$arguments = null;
echo '# IsString' . PHP_EOL;
foreach ($data as $itemToValidate) {
    echo json_encode($itemToValidate) . ' : ';
    try {
        $validator->validate(
            data: $itemToValidate,
            arguments: $arguments,
            context: [],
        );
        echo 'Is Valid.';
    } catch (ValidationException $exception) {
        echo 'Is Not Valid.' . PHP_EOL;
        echo '(' . $exception::class . ') ' . $exception->getMessage() . PHP_EOL;
        echo '  * ' . implode(PHP_EOL . '  * ', $exception->getErrors());
    }
    echo PHP_EOL . PHP_EOL;
}
echo PHP_EOL . "---" . PHP_EOL;

## IsUrl
$validator = new IsUrl();
$data = [
    null,
    'www.klevu.com',
    'https://www.klevu.com',
    '127.0.0.1',
    'foo',
    ['bar'],
];
$arguments = new ArgumentIterator([
    new Argument(
        value: false,
        key: IsUrl::ARGUMENT_INDEX_REQUIRE_PROTOCOL,
    ),
]);
echo '# IsUrl' . PHP_EOL;
foreach ($data as $itemToValidate) {
    echo json_encode($itemToValidate) . ' : ';
    try {
        $validator->validate(
            data: $itemToValidate,
            arguments: $arguments,
            context: [],
        );
        echo 'Is Valid.';
    } catch (ValidationException $exception) {
        echo 'Is Not Valid.' . PHP_EOL;
        echo '(' . $exception::class . ') ' . $exception->getMessage() . PHP_EOL;
        echo '  * ' . implode(PHP_EOL . '  * ', $exception->getErrors());
    }
    echo PHP_EOL . PHP_EOL;
}
echo PHP_EOL . "---" . PHP_EOL;

## IsValidDate
$validator = new IsValidDate();
$data = [
    null,
    '2020-01-01T00:00:00',
    '01-01-2020 00:00:00',
    'foo',
    ['2020-01-01T00:00:00'],
];
$arguments = new ArgumentIterator([
    new Argument(
        value: new ArgumentIterator([
            new Argument('Y-m-d\TH:i:s'),
        ]),
        key: IsUrl::ARGUMENT_INDEX_REQUIRE_PROTOCOL,
    ),
]);
echo '# IsValidDate' . PHP_EOL;
foreach ($data as $itemToValidate) {
    echo json_encode($itemToValidate) . ' : ';
    try {
        $validator->validate(
            data: $itemToValidate,
            arguments: $arguments,
            context: [],
        );
        echo 'Is Valid.';
    } catch (ValidationException $exception) {
        echo 'Is Not Valid.' . PHP_EOL;
        echo '(' . $exception::class . ') ' . $exception->getMessage() . PHP_EOL;
        echo '  * ' . implode(PHP_EOL . '  * ', $exception->getErrors());
    }
    echo PHP_EOL . PHP_EOL;
}
echo PHP_EOL . "---" . PHP_EOL;

## MatchesRegex
$validator = new MatchesRegex();
$data = [
    null,
    'foo::bar/baz',
    ' foo :: bar/baz ',
    ['foo::bar/baz'],
];
$arguments = new ArgumentIterator([
    new Argument(
        value: '#^[a-z]{3}::bar/[^\d]+$#',
        key: MatchesRegex::ARGUMENT_INDEX_REGULAR_EXPRESSION,
    ),
]);
echo '# MatchesRegex' . PHP_EOL;
foreach ($data as $itemToValidate) {
    echo json_encode($itemToValidate) . ' : ';
    try {
        $validator->validate(
            data: $itemToValidate,
            arguments: $arguments,
            context: [],
        );
        echo 'Is Valid.';
    } catch (ValidationException $exception) {
        echo 'Is Not Valid.' . PHP_EOL;
        echo '(' . $exception::class . ') ' . $exception->getMessage() . PHP_EOL;
        echo '  * ' . implode(PHP_EOL . '  * ', $exception->getErrors());
    }
    echo PHP_EOL . PHP_EOL;
}
echo PHP_EOL . "---" . PHP_EOL;

# COMPOSITE VALIDATORS

## CompositeIterate
$validator = new CompositeIterate();
$data = [
    null,
    [null],
    ['www.klevu.com', 'https://www.klevu.com'],
    ['www.klevu.com', 'foo'],
    [['www.klevu.com']],
];
$arguments = new ArgumentIterator([
    new Argument(
        value: new ValidationIterator([
            new Validation(
                validatorName: 'IsUrl',
                arguments: new ArgumentIterator([
                    new Argument(
                        value: false,
                        key: IsUrl::ARGUMENT_INDEX_REQUIRE_PROTOCOL,
                    ),
                ]),
            ),
        ]),
        key: CompositeIterate::ARGUMENT_INDEX_VALIDATION
    )
]);
echo '# CompositeIterate(IsUrl)' . PHP_EOL;
foreach ($data as $itemToValidate) {
    echo json_encode($itemToValidate) . ' : ';
    try {
        $validator->validate(
            data: $itemToValidate,
            arguments: $arguments,
            context: [],
        );
        echo 'Is Valid.';
    } catch (ValidationException $exception) {
        echo 'Is Not Valid.' . PHP_EOL;
        echo '(' . $exception::class . ') ' . $exception->getMessage() . PHP_EOL;
        echo '  * ' . implode(PHP_EOL . '  * ', $exception->getErrors());
    }
    echo PHP_EOL . PHP_EOL;
}
echo PHP_EOL . "---" . PHP_EOL;

## CompositeOr
$validator = new \Klevu\Pipelines\Validator\CompositeOr();
$data = [
    null,
    'www.klevu.com',
    'https://www.klevu.com',
    '127.0.0.1',
    'foo',
    ['bar'],
];
$arguments = new ArgumentIterator([
    new Argument(
        new ValidationIterator([
            new Validation(
                validatorName: 'IsUrl',
                arguments: new ArgumentIterator([
                    new Argument(
                        value: false,
                        key: IsUrl::ARGUMENT_INDEX_REQUIRE_PROTOCOL,
                    ),
                ]),
            ),
        ]),
    ),
    new Argument(
        new ValidationIterator([
            new Validation(
                validatorName: 'IsIpAddress',
                arguments: null,
            ),
            new Validation(
                validatorName: 'IsNotEmpty',
                arguments: null,
            ),
        ]),
    ),
]);
echo '# CompositeOr(IsUrl(...), IsIpAddress|IsNotEmpty)' . PHP_EOL;
foreach ($data as $itemToValidate) {
    echo json_encode($itemToValidate) . ' : ';
    try {
        $validator->validate(
            data: $itemToValidate,
            arguments: $arguments,
            context: [],
        );
        echo 'Is Valid.';
    } catch (ValidationException $exception) {
        echo 'Is Not Valid.' . PHP_EOL;
        echo '(' . $exception::class . ') ' . $exception->getMessage() . PHP_EOL;
        echo '  * ' . implode(PHP_EOL . '  * ', $exception->getErrors());
    }
    echo PHP_EOL . PHP_EOL;
}
echo PHP_EOL . "---" . PHP_EOL;