<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Exception\Validation;

use Klevu\Pipelines\Exception\Validation\InvalidTypeValidationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidTypeValidationException::class)]
class InvalidTypeValidationExceptionTest extends TestCase
{
    /**
     * @param string[] $errors
     * @param mixed $data
     * @param string $message
     *
     * @return void
     */
    #[Test]
    #[TestWith([['Generic Error Passing'], 'integer', 'Lets Pass this message to the Exception'])]
    #[TestWith([['Bar'], 'Mixed', 'Random Array'])]
    #[TestWith([['foo'], null, 'Random Array'])]
    #[TestWith([['foo'], 'integer', 'Data must be null|string; Received array'])]
    #[TestWith([['foo' => 'bar'], 'integer', 'Data must be null|string; Received array'])]
    #[TestWith([['Data must be null|string; Received array'], 'integer', ''])]
    public function testConstruct(
        array $errors,
        mixed $data,
        string $message,
    ): void {
        $previousException = new \Exception('Foo');
        $exception = new InvalidTypeValidationException(
            validatorName: 'Validator',
            errors: $errors,
            data: $data,
            message: $message,
            code: 418,
            previous: $previousException,
        );
        $this->assertSame(
            expected: 'Validator',
            actual: $exception->getValidatorName(),
        );
        $this->assertSame(
            expected: $errors,
            actual: $exception->getErrors(),
        );
        $this->assertSame($data, $exception->getData());
        $this->assertSame(
            $message ?: 'Invalid data type received',
            $exception->getMessage(),
        );
        $this->assertSame(418, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
