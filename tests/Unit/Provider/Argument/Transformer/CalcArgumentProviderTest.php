<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 * phpcs:disable SlevomatCodingStandard.Classes.ClassStructure.IncorrectGroupOrder
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Provider\Argument\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\Transformation\Calc\Operations;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\ObjectManager\ObjectManagerInterface;
use Klevu\Pipelines\Provider\Argument\Transformer\CalcArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Transformer\Calc as CalcTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(CalcArgumentProvider::class)]
class CalcArgumentProviderTest extends TestCase
{
    /**
     * @var ArgumentIteratorFactory|null
     */
    private ?ArgumentIteratorFactory $argumentIteratorFactory = null;
    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $container = null;
    
    /**
     * @return void
     * @throws \TypeError
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->argumentIteratorFactory = new ArgumentIteratorFactory();
        $this->container = Container::getInstance(); // @phpstan-ignore-line (\TypeError will cause test failures)
    }

    #[Test]
    #[RunInSeparateProcess]
    public function testConstruct_InvalidContainerObject(): void
    {
        $this->container?->addSharedInstance(
            identifier: ArgumentProvider::class,
            instance: new \stdClass(),
        );

        $this->expectException(InvalidClassException::class);
        $calcArgumentProvider = new CalcArgumentProvider(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable, Generic.Files.LineLength.TooLong
    }

    #[Test]
    #[RunInSeparateProcess]
    public function testConstruct_InvalidContainerObject_Overwritten(): void
    {
        $this->container?->addSharedInstance(
            identifier: ArgumentProvider::class,
            instance: new \stdClass(),
        );

        try {
            $this->expectNotToPerformAssertions();
            $calcArgumentProvider = new CalcArgumentProvider( // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable, Generic.Files.LineLength.TooLong
                argumentProvider: $this->createMock(ArgumentProviderInterface::class),
            );
        } catch (\Exception $exception) {
            $this->fail($exception::class . ': ' . $exception->getMessage());
        }
    }

    #[Test]
    public function testGetOperationArgumentValue_WithoutArgument(): void
    {
        $calcArgumentProvider = new CalcArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            CalcArgumentProvider::ARGUMENT_INDEX_VALUE => 'foo',
        ]);

        $this->expectException(InvalidTransformationArgumentsException::class);
        try {
            $calcArgumentProvider->getOperationArgumentValue(
                arguments: $arguments,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertSame(
                expected: sprintf(
                    'Operation argument (%s) is required',
                    CalcArgumentProvider::ARGUMENT_INDEX_OPERATION,
                ),
                actual: $errors[0] ?? null,
            );
            $this->assertSame(
                expected: CalcTransformer::class,
                actual: $exception->getTransformerName(),
            );
            throw $exception;
        }
    }

    #[Test]
    #[TestWith([Operations::ADD, Operations::ADD])]
    #[TestWith([Operations::DIVIDE, Operations::DIVIDE])]
    #[TestWith([Operations::MULTIPLY, Operations::MULTIPLY])]
    #[TestWith([Operations::POW, Operations::POW])]
    #[TestWith([Operations::SUBTRACT, Operations::SUBTRACT])]
    #[TestWith(['+', Operations::ADD])]
    #[TestWith(['/', Operations::DIVIDE])]
    #[TestWith(['*', Operations::MULTIPLY])]
    #[TestWith(['^', Operations::POW])]
    #[TestWith(['-', Operations::SUBTRACT])]
    public function testGetOperationArgumentValue_Valid(
        mixed $operationArgumentValue,
        Operations $expectedResult,
    ): void {
        $calcArgumentProvider = new CalcArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            CalcArgumentProvider::ARGUMENT_INDEX_OPERATION => $operationArgumentValue,
            CalcArgumentProvider::ARGUMENT_INDEX_VALUE => 'foo',
        ]);

        $actualResult = $calcArgumentProvider->getOperationArgumentValue(
            arguments: $arguments,
        );

        $this->assertSame(
            expected: $expectedResult,
            actual: $actualResult,
        );
    }

    #[Test]
    public function testGetOperationArgumentValue_Extraction(): void
    {
        $calcArgumentProvider = new CalcArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            CalcArgumentProvider::ARGUMENT_INDEX_OPERATION => new Extraction(
                accessor: 'operation',
            ),
            CalcArgumentProvider::ARGUMENT_INDEX_VALUE => 'foo',
        ]);

        $actualResult = $calcArgumentProvider->getOperationArgumentValue(
            arguments: $arguments,
            extractionPayload: [
                'operation' => '+',
            ],
        );

        $this->assertSame(
            expected: Operations::ADD,
            actual: $actualResult,
        );
    }

    #[Test]
    #[TestWith([null])]
    #[TestWith([''])]
    public function testGetOperationArgumentValue_Empty(
        mixed $operationArgumentValue,
    ): void {
        $calcArgumentProvider = new CalcArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            CalcArgumentProvider::ARGUMENT_INDEX_OPERATION => $operationArgumentValue,
            CalcArgumentProvider::ARGUMENT_INDEX_VALUE => 'foo',
        ]);

        $this->expectException(InvalidTransformationArgumentsException::class);
        try {
            $calcArgumentProvider->getOperationArgumentValue(
                arguments: $arguments,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertSame(
                expected: sprintf(
                    'Operation argument (%s) is required',
                    CalcArgumentProvider::ARGUMENT_INDEX_OPERATION,
                ),
                actual: $errors[0] ?? null,
            );
            $this->assertSame(
                expected: CalcTransformer::class,
                actual: $exception->getTransformerName(),
            );
            throw $exception;
        }
    }

    #[Test]
    #[TestWith([42])]
    #[TestWith([3.14])]
    #[TestWith([true])]
    #[TestWith([false])]
    #[TestWith([[]])]
    #[TestWith([new \stdClass()])]
    public function testGetOperationArgumentValue_InvalidType(
        mixed $operationArgumentValue,
    ): void {
        $calcArgumentProvider = new CalcArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            CalcArgumentProvider::ARGUMENT_INDEX_OPERATION => $operationArgumentValue,
            CalcArgumentProvider::ARGUMENT_INDEX_VALUE => 'foo',
        ]);

        $this->expectException(InvalidTransformationArgumentsException::class);
        try {
            $calcArgumentProvider->getOperationArgumentValue(
                arguments: $arguments,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Invalid Operation argument \(\d\): .*/',
                string: $errors[0] ?? '',
            );
            $this->assertSame(
                expected: CalcTransformer::class,
                actual: $exception->getTransformerName(),
            );
            throw $exception;
        }
    }

    #[Test]
    #[TestWith([' '])]
    #[TestWith(['foo'])]
    #[TestWith(['add'])]
    public function testGetOperationArgumentValue_InvalidValue(
        mixed $operationArgumentValue,
    ): void {
        $calcArgumentProvider = new CalcArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            CalcArgumentProvider::ARGUMENT_INDEX_OPERATION => $operationArgumentValue,
            CalcArgumentProvider::ARGUMENT_INDEX_VALUE => 'foo',
        ]);

        $this->expectException(InvalidTransformationArgumentsException::class);
        try {
            $calcArgumentProvider->getOperationArgumentValue(
                arguments: $arguments,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/^Unrecognised Operation argument \(\d\) value: .*/',
                string: $errors[0] ?? '',
            );
            $this->assertSame(
                expected: CalcTransformer::class,
                actual: $exception->getTransformerName(),
            );
            throw $exception;
        }
    }

    #[Test]
    public function testGetValueArgumentValue_WithoutArguments(): void
    {
        $calcArgumentProvider = new CalcArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            CalcArgumentProvider::ARGUMENT_INDEX_OPERATION => Operations::ADD,
        ]);

        $this->expectException(InvalidTransformationArgumentsException::class);
        try {
            $calcArgumentProvider->getValueArgumentValue(
                arguments: $arguments,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertSame(
                expected: sprintf(
                    'Value argument (%s) is required',
                    CalcArgumentProvider::ARGUMENT_INDEX_VALUE,
                ),
                actual: $errors[0] ?? null,
            );
            $this->assertSame(
                expected: CalcTransformer::class,
                actual: $exception->getTransformerName(),
            );
            throw $exception;
        }
    }

    #[Test]
    #[TestWith([0, 0])]
    #[TestWith([0.0, 0.0])]
    #[TestWith(['0.0', '0.0'])]
    #[TestWith([1, 1])]
    #[TestWith([1.0, 1.0])]
    #[TestWith(['1', '1'])]
    #[TestWith([' 42 ', '42'])]
    #[TestWith([0123, 83])]
    #[TestWith([0o123, 83])]
    #[TestWith([0x1A, 26])]
    #[TestWith([0b11111111, 255])]
    #[TestWith([1_234_567, 1234567])]
    public function testGetValueArgumentValue_Valid(
        mixed $valueArgumentValue,
        float|int|string $expectedResult,
    ): void {
        $calcArgumentProvider = new CalcArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            CalcArgumentProvider::ARGUMENT_INDEX_OPERATION => Operations::ADD,
            CalcArgumentProvider::ARGUMENT_INDEX_VALUE => $valueArgumentValue,
        ]);

        $actualResult = $calcArgumentProvider->getValueArgumentValue(
            arguments: $arguments,
        );

        $this->assertSame(
            expected: $expectedResult,
            actual: $actualResult,
        );
    }

    #[Test]
    public function testGetValueArgumentValue_Extraction(): void
    {
        $calcArgumentProvider = new CalcArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            CalcArgumentProvider::ARGUMENT_INDEX_OPERATION => Operations::ADD,
            CalcArgumentProvider::ARGUMENT_INDEX_VALUE => new Extraction(
                accessor: 'value',
            ),
        ]);

        $actualResult = $calcArgumentProvider->getValueArgumentValue(
            arguments: $arguments,
            extractionPayload: [
                'value' => 42,
            ],
        );

        $this->assertSame(
            expected: 42,
            actual: $actualResult,
        );
    }

    #[Test]
    #[TestWith([true])]
    #[TestWith([[]])]
    #[TestWith([new \stdClass()])]
    public function testGetValueArgumentValue_InvalidType(
        mixed $valueArgumentValue,
    ): void {
        $calcArgumentProvider = new CalcArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            CalcArgumentProvider::ARGUMENT_INDEX_OPERATION => Operations::ADD,
            CalcArgumentProvider::ARGUMENT_INDEX_VALUE => $valueArgumentValue,
        ]);

        $this->expectException(InvalidTransformationArgumentsException::class);
        try {
            $calcArgumentProvider->getValueArgumentValue(
                arguments: $arguments,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/^Invalid Value argument \(\d\): .*/',
                string: $errors[0],
            );
            $this->assertSame(
                expected: CalcTransformer::class,
                actual: $exception->getTransformerName(),
            );
            throw $exception;
        }
    }

    #[Test]
    #[TestWith(['1_234_567'])]
    #[TestWith(['100b'])]
    public function testGetValueArgumentValue_InvalidValue(
        mixed $valueArgumentValue,
    ): void {
        $calcArgumentProvider = new CalcArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            CalcArgumentProvider::ARGUMENT_INDEX_OPERATION => Operations::ADD,
            CalcArgumentProvider::ARGUMENT_INDEX_VALUE => $valueArgumentValue,
        ]);

        $this->expectException(InvalidTransformationArgumentsException::class);
        try {
            $result = $calcArgumentProvider->getValueArgumentValue( // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable, Generic.Files.LineLength.TooLong
                arguments: $arguments,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Value argument \(\d\) must be numeric; received.*/',
                string: $errors[0] ?? '',
            );
            $this->assertSame(
                expected: CalcTransformer::class,
                actual: $exception->getTransformerName(),
            );
            throw $exception;
        }
    }
}
