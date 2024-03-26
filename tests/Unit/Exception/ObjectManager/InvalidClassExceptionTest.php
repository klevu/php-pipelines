<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Exception\ObjectManager;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Test\Fixture\TestObject;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidClassException::class)]
class InvalidClassExceptionTest extends TestCase
{
    #[Test]
    #[TestWith(['foo', new TestObject(publicProperty: 'bar'), ''])]
    #[TestWith(['foo', new TestObject(publicProperty: 'bar'), 'Test message'])]
    public function testConstruct(
        string $identifier,
        object $instance,
        string $message,
    ): void {
        $previousException = new \Exception('Foo');
        $exception = new InvalidClassException(
            identifier: $identifier,
            instance: $instance,
            message: $message,
            code: 42,
            previous: $previousException,
        );

        $this->assertSame($identifier, $exception->getIdentifier());
        $this->assertSame($instance, $exception->getInstance());
        if ($message) {
            $this->assertSame($message, $exception->getMessage());
        } else {
            $this->assertSame(
                sprintf(
                    'Class of type "%s" identified by "%s" is not valid',
                    $instance::class,
                    $identifier,
                ),
                $exception->getMessage(),
            );
        }
        $this->assertSame(42, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
