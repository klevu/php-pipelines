<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Exception;

use Klevu\Pipelines\Exception\ObjectManagerException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectManagerException::class)]
class ObjectManagerExceptionTest extends TestCase
{
    #[Test]
    #[TestWith(['foo', ''])]
    #[TestWith(['foo', 'Test message'])]
    public function testConstruct(
        string $identifier,
        string $message,
    ): void {
        $previousException = new \Exception('Foo');
        $exception = new ObjectManagerException(
            identifier: $identifier,
            message: $message,
            code: 42,
            previous: $previousException,
        );

        $this->assertSame($identifier, $exception->getIdentifier());
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(42, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
