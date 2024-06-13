<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/** @noinspection PhpRedundantOptionalArgumentInspection */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Model\ValidationIteratorFactory;
use Klevu\Pipelines\ObjectManager\ValidatorManager;
use Klevu\Pipelines\Validator\ValidatorInterface;

$validationIteratorFactory = new ValidationIteratorFactory();
$validatorManager = new ValidatorManager();

## CompositeIterate

## CompositeOr

## IsCurrencyCode
$validation = $validationIteratorFactory
    ->createFromSyntaxDeclaration('IsCurrencyCode')
    ->current();
/** @var ValidatorInterface $validator */
$validator = $validatorManager->get($validation->validatorName);
$data = [
    'GBP',
    '£',
    ['USD'],
];
echo '# IsCurrencyCode' . PHP_EOL;
foreach ($data as $itemToValidate) {
    echo json_encode($itemToValidate) . ' : ';
    try {
        $validator->validate(
            data: $itemToValidate,
            arguments: $validation->arguments,
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

## IsIpAddress

## IsNotEmpty

## IsNumeric

## IsPositiveNumber

## IsString

## IsUrl

## IsValidDate

## MatchesRegex