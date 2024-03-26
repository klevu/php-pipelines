<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Exception\ObjectManager;

use Klevu\Pipelines\Exception\ObjectManager\ClassNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClassNotFoundException::class)]
class ClassNotFoundExceptionTest extends TestCase
{
    /**
     * @param string $identifier
     * @param string[] $namespaces
     * @param string $message
     * @return void
     */
    #[Test]
    #[TestWith(['foo', ['\\foo\\bar\\'], ''])]
    #[TestWith(['foo', ['\\foo\\bar\\'], 'Test message'])]
    public function testConstruct(
        string $identifier,
        array $namespaces,
        string $message,
    ): void {
        $previousException = new \Exception('Foo');
        $exception = new ClassNotFoundException(
            identifier: $identifier,
            namespaces: $namespaces,
            message: $message,
            code: 42,
            previous: $previousException,
        );

        $this->assertSame($identifier, $exception->getIdentifier());
        $this->assertSame($namespaces, $exception->getNamespaces());
        if ($message) {
            $this->assertSame($message, $exception->getMessage());
        } else {
            $this->assertSame(
                sprintf(
                    'Could not locate class for identifier "%s" in namespaces "%s"',
                    $identifier,
                    implode(';', $namespaces),
                ),
                $exception->getMessage(),
            );
        }
        $this->assertSame(42, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
