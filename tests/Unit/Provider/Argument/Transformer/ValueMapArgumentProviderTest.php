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
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\Transformation\ValueMapItem;
use Klevu\Pipelines\Model\Transformation\ValueMapItemIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\ObjectManager\ObjectManagerInterface;
use Klevu\Pipelines\Provider\Argument\Transformer\ValueMap\ItemArgumentProvider;
use Klevu\Pipelines\Provider\Argument\Transformer\ValueMapArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Transformer\ValueMap as ValueMapTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValueMapArgumentProvider::class)]
class ValueMapArgumentProviderTest extends TestCase
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
    public function testConstruct_InvalidContainerObject_ArgumentProvider(): void
    {
        $this->container?->addSharedInstance(
            identifier: ArgumentProvider::class,
            instance: new \stdClass(),
        );

        $this->expectException(InvalidClassException::class);
        try {
            $valueMapArgumentProvider = new ValueMapArgumentProvider( // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable, Generic.Files.LineLength.TooLong
                argumentIteratorFactory: $this->createMock(ArgumentIteratorFactory::class),
            );
        } catch (InvalidClassException $exception) {
            $this->assertSame(
                expected: ArgumentProvider::class,
                actual: $exception->getIdentifier(),
            );
            throw $exception;
        }
    }

    #[Test]
    #[RunInSeparateProcess]
    public function testConstruct_InvalidContainerObject_ArgumentIteratorFactory(): void
    {
        $this->container?->addSharedInstance(
            identifier: ArgumentIteratorFactory::class,
            instance: new \stdClass(),
        );

        $this->expectException(InvalidClassException::class);
        try {
            $valueMapArgumentProvider = new ValueMapArgumentProvider( // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable, Generic.Files.LineLength.TooLong
                argumentProvider: $this->createMock(ArgumentProviderInterface::class),
            );
        } catch (InvalidClassException $exception) {
            $this->assertSame(
                expected: ArgumentIteratorFactory::class,
                actual: $exception->getIdentifier(),
            );
            throw $exception;
        }
    }

    #[Test]
    #[RunInSeparateProcess]
    public function testConstruct_InvalidContainerObject_Overwritten(): void
    {
        $this->container?->addSharedInstance(
            identifier: ArgumentProvider::class,
            instance: new \stdClass(),
        );
        $this->container?->addSharedInstance(
            identifier: ItemArgumentProvider::class,
            instance: new \stdClass(),
        );
        $this->container?->addSharedInstance(
            identifier: ArgumentIteratorFactory::class,
            instance: new \stdClass(),
        );

        try {
            $this->expectNotToPerformAssertions();
            $valueMapArgumentProvider = new ValueMapArgumentProvider( // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable, Generic.Files.LineLength.TooLong
                argumentProvider: $this->createMock(ArgumentProviderInterface::class),
                itemArgumentProvider: $this->createMock(ItemArgumentProvider::class),
                argumentIteratorFactory: $this->createMock(ArgumentIteratorFactory::class),
            );
        } catch (\Exception $exception) {
            $this->fail($exception::class . ': ' . $exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }
    }

    #[Test]
    #[TestWith([null, true])]
    #[TestWith([true, true])]
    #[TestWith([false, false])]
    public function testGetStrictArgumentValue_WithoutArguments(
        ?bool $defaultStrict,
        bool $expectedResult,
    ): void {
        if (null === $defaultStrict) {
            $valueMapArgumentProvider = new ValueMapArgumentProvider();
        } else {
            $valueMapArgumentProvider = new ValueMapArgumentProvider(
                defaultStrict: $defaultStrict,
            );
        }
        $arguments = $this->argumentIteratorFactory?->create([
            ValueMapArgumentProvider::ARGUMENT_INDEX_VALUE_MAP => [],
            ValueMapArgumentProvider::ARGUMENT_INDEX_CASE_SENSITIVE => true,
        ]);

        $actualResult = $valueMapArgumentProvider->getStrictArgumentValue(
            arguments: $arguments,
        );

        $this->assertSame(
            expected: $expectedResult,
            actual: $actualResult,
        );
    }

    #[Test]
    #[TestWith([null, true, true])]
    #[TestWith([true, true, true])]
    #[TestWith([false, true, true])]
    #[TestWith([null, false, false])]
    #[TestWith([true, false, false])]
    #[TestWith([false, false, false])]
    public function testGetStrictArgumentValue_Valid(
        ?bool $defaultStrict,
        mixed $strictArgumentValue,
        bool $expectedResult,
    ): void {
        if (null === $defaultStrict) {
            $valueMapArgumentProvider = new ValueMapArgumentProvider();
        } else {
            $valueMapArgumentProvider = new ValueMapArgumentProvider(
                defaultStrict: $defaultStrict,
            );
        }
        $arguments = $this->argumentIteratorFactory?->create([
            ValueMapArgumentProvider::ARGUMENT_INDEX_VALUE_MAP => [],
            ValueMapArgumentProvider::ARGUMENT_INDEX_STRICT => $strictArgumentValue,
            ValueMapArgumentProvider::ARGUMENT_INDEX_CASE_SENSITIVE => true,
        ]);

        $actualResult = $valueMapArgumentProvider->getStrictArgumentValue(
            arguments: $arguments,
        );

        $this->assertSame(
            expected: $expectedResult,
            actual: $actualResult,
        );
    }

    #[Test]
    public function testGetStrictArgumentValue_Extraction(): void
    {
        $valueMapArgumentProvider = new ValueMapArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            ValueMapArgumentProvider::ARGUMENT_INDEX_VALUE_MAP => [],
            ValueMapArgumentProvider::ARGUMENT_INDEX_STRICT => new Extraction(
                accessor: 'strict',
            ),
            ValueMapArgumentProvider::ARGUMENT_INDEX_CASE_SENSITIVE => true,
        ]);

        $actualResult = $valueMapArgumentProvider->getStrictArgumentValue(
            arguments: $arguments,
            extractionPayload: [
                'strict' => false,
            ],
        );

        $this->assertSame(
            expected: false,
            actual: $actualResult,
        );
    }

    #[Test]
    #[TestWith([42])]
    #[TestWith([3.14])]
    #[TestWith(['true'])]
    #[TestWith([[]])]
    #[TestWith([new \stdClass()])]
    public function testGetStrictArgumentValue_InvalidType(
        mixed $strictArgumentValue,
    ): void {
        $valueMapArgumentProvider = new ValueMapArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            ValueMapArgumentProvider::ARGUMENT_INDEX_VALUE_MAP => [],
            ValueMapArgumentProvider::ARGUMENT_INDEX_STRICT => $strictArgumentValue,
            ValueMapArgumentProvider::ARGUMENT_INDEX_CASE_SENSITIVE => true,
        ]);

        $this->expectException(InvalidTransformationArgumentsException::class);
        try {
            $valueMapArgumentProvider->getStrictArgumentValue(
                arguments: $arguments,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Strict argument \(\d\) must be boolean; Received.*/',
                string: $errors[0] ?? '',
            );
            $this->assertSame(
                expected: ValueMapTransformer::class,
                actual: $exception->getTransformerName(),
            );
            throw $exception;
        }
    }

    #[Test]
    #[TestWith([null, true])]
    #[TestWith([true, true])]
    #[TestWith([false, false])]
    public function testGetCaseSensitiveArgumentValue_WithoutArguments(
        ?bool $defaultCaseSensitive,
        bool $expectedResult,
    ): void {
        if (null === $defaultCaseSensitive) {
            $valueMapArgumentProvider = new ValueMapArgumentProvider();
        } else {
            $valueMapArgumentProvider = new ValueMapArgumentProvider(
                defaultCaseSensitive: $defaultCaseSensitive,
            );
        }
        $arguments = $this->argumentIteratorFactory?->create([
            ValueMapArgumentProvider::ARGUMENT_INDEX_VALUE_MAP => [],
            ValueMapArgumentProvider::ARGUMENT_INDEX_STRICT => true,
        ]);

        $actualResult = $valueMapArgumentProvider->getCaseSensitiveArgumentValue(
            arguments: $arguments,
        );

        $this->assertSame(
            expected: $expectedResult,
            actual: $actualResult,
        );
    }

    #[Test]
    #[TestWith([null, true, true])]
    #[TestWith([true, true, true])]
    #[TestWith([false, true, true])]
    #[TestWith([null, false, false])]
    #[TestWith([true, false, false])]
    #[TestWith([false, false, false])]
    public function testGetCaseSensitiveArgumentValue_Valid(
        ?bool $defaultCaseSensitive,
        mixed $caseSensitiveArgumentValue,
        bool $expectedResult,
    ): void {
        if (null === $defaultCaseSensitive) {
            $valueMapArgumentProvider = new ValueMapArgumentProvider();
        } else {
            $valueMapArgumentProvider = new ValueMapArgumentProvider(
                defaultCaseSensitive: $defaultCaseSensitive,
            );
        }
        $arguments = $this->argumentIteratorFactory?->create([
            ValueMapArgumentProvider::ARGUMENT_INDEX_VALUE_MAP => [],
            ValueMapArgumentProvider::ARGUMENT_INDEX_STRICT => true,
            ValueMapArgumentProvider::ARGUMENT_INDEX_CASE_SENSITIVE => $caseSensitiveArgumentValue,
        ]);

        $actualResult = $valueMapArgumentProvider->getCaseSensitiveArgumentValue(
            arguments: $arguments,
        );

        $this->assertSame(
            expected: $expectedResult,
            actual: $actualResult,
        );
    }

    #[Test]
    public function testGetCaseSensitiveArgumentValue_Extraction(): void
    {
        $valueMapArgumentProvider = new ValueMapArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            ValueMapArgumentProvider::ARGUMENT_INDEX_VALUE_MAP => [],
            ValueMapArgumentProvider::ARGUMENT_INDEX_STRICT => true,
            ValueMapArgumentProvider::ARGUMENT_INDEX_CASE_SENSITIVE => new Extraction(
                accessor: 'case_sensitive',
            ),
        ]);

        $actualResult = $valueMapArgumentProvider->getCaseSensitiveArgumentValue(
            arguments: $arguments,
            extractionPayload: [
                'case_sensitive' => false,
            ],
        );

        $this->assertSame(
            expected: false,
            actual: $actualResult,
        );
    }

    #[Test]
    #[TestWith([42])]
    #[TestWith([3.14])]
    #[TestWith(['true'])]
    #[TestWith([[]])]
    #[TestWith([new \stdClass()])]
    public function testGetCaseSensitiveArgumentValue_InvalidType(
        mixed $caseSensitiveArgumentValue,
    ): void {
        $valueMapArgumentProvider = new ValueMapArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            ValueMapArgumentProvider::ARGUMENT_INDEX_VALUE_MAP => [],
            ValueMapArgumentProvider::ARGUMENT_INDEX_STRICT => true,
            ValueMapArgumentProvider::ARGUMENT_INDEX_CASE_SENSITIVE => $caseSensitiveArgumentValue,
        ]);

        $this->expectException(InvalidTransformationArgumentsException::class);
        try {
            $valueMapArgumentProvider->getCaseSensitiveArgumentValue(
                arguments: $arguments,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Case Sensitive argument \(\d\) must be boolean; Received .*/',
                string: $errors[0] ?? '',
            );
            $this->assertSame(
                expected: ValueMapTransformer::class,
                actual: $exception->getTransformerName(),
            );
            throw $exception;
        }
    }

    #[Test]
    public function testGetValueMap_WithoutArguments(): void
    {
        $valueMapArgumentProvider = new ValueMapArgumentProvider();

        $this->expectException(InvalidTransformationArgumentsException::class);
        try {
            $valueMapArgumentProvider->getValueMap(
                arguments: null,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Value Map argument \(\d\) must be .*; Received null/',
                string: $errors[0] ?? '',
            );
            $this->assertSame(
                expected: ValueMapTransformer::class,
                actual: $exception->getTransformerName(),
            );
            throw $exception;
        }
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testGetValueMap_Valid(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                $argumentIteratorFactory->create([
                    ValueMapArgumentProvider::ARGUMENT_INDEX_VALUE_MAP => [
                        'foo' => 'bar',
                    ],
                ]),
                new ValueMapItemIterator([
                    new ValueMapItem(
                        sourceValue: 'foo',
                        convertedValue: 'bar',
                    ),
                ]),
            ],
            [
                new ArgumentIterator([
                    new Argument(
                        value: [
                            'foo' => 'bar',
                        ],
                        key: ValueMapArgumentProvider::ARGUMENT_INDEX_VALUE_MAP,
                    ),
                ]),
                new ValueMapItemIterator([
                    new ValueMapItem(
                        sourceValue: 'foo',
                        convertedValue: 'bar',
                    ),
                ]),
            ],
            [
                $argumentIteratorFactory->create([
                    ValueMapArgumentProvider::ARGUMENT_INDEX_VALUE_MAP => [
                        [
                            'sourceValue' => 'foo',
                            'convertedValue' => 'bar',
                        ],
                    ],
                ]),
                new ValueMapItemIterator([
                    new ValueMapItem(
                        sourceValue: 'foo',
                        convertedValue: 'bar',
                    ),
                ]),
            ],
            [
                $argumentIteratorFactory->create([
                    ValueMapArgumentProvider::ARGUMENT_INDEX_VALUE_MAP => [
                        [
                            'caseSensitive' => false,
                            'strict' => false,
                            'convertedValue' => 'bar',
                            'sourceValue' => 'foo',
                        ],
                    ],
                ]),
                new ValueMapItemIterator([
                    new ValueMapItem(
                        sourceValue: 'foo',
                        convertedValue: 'bar',
                        strict: false,
                        caseSensitive: false,
                    ),
                ]),
            ],
            [
                new ArgumentIterator([
                    new Argument(
                        value: [
                            [
                                'caseSensitive' => false,
                                'strict' => false,
                                'convertedValue' => 'bar',
                                'sourceValue' => 'foo',
                            ],
                        ],
                        key: ValueMapArgumentProvider::ARGUMENT_INDEX_VALUE_MAP,
                    ),
                ]),
                new ValueMapItemIterator([
                    new ValueMapItem(
                        sourceValue: 'foo',
                        convertedValue: 'bar',
                        strict: false,
                        caseSensitive: false,
                    ),
                ]),
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testGetValueMap_Valid')]
    public function testGetValueMap_Valid(
        ArgumentIterator $arguments,
        ValueMapItemIterator $expectedResult,
    ): void {
        $valueMapArgumentProvider = new ValueMapArgumentProvider();

        $actualResult = $valueMapArgumentProvider->getValueMap(
            arguments: $arguments,
        );

        $this->assertEquals(
            expected: $expectedResult,
            actual: $actualResult,
        );
    }

    #[Test]
    #[TestWith([[]])]
    #[TestWith([new ValueMapItemIterator([])])]
    public function testGetValueMap_Empty(
        mixed $valueMapArgumentValue,
    ): void {
        $valueMapArgumentProvider = new ValueMapArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            ValueMapArgumentProvider::ARGUMENT_INDEX_VALUE_MAP => $valueMapArgumentValue,
        ]);

        $actualResult = $valueMapArgumentProvider->getValueMap(
            arguments: $arguments,
        );

        $this->assertEquals(
            expected: new ValueMapItemIterator([]),
            actual: $actualResult,
        );
    }

    #[Test]
    #[TestWith([null])]
    #[TestWith([42])]
    #[TestWith([3.14])]
    #[TestWith([true])]
    #[TestWith([new \stdClass()])]
    public function testGetValueMap_InvalidType(
        mixed $valueMapArgumentValue,
    ): void {
        $valueMapArgumentProvider = new ValueMapArgumentProvider();
        $arguments = $this->argumentIteratorFactory?->create([
            ValueMapArgumentProvider::ARGUMENT_INDEX_VALUE_MAP => $valueMapArgumentValue,
        ]);

        $this->expectException(InvalidTransformationArgumentsException::class);
        try {
            $valueMapArgumentProvider->getValueMap(
                arguments: $arguments,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Value Map argument \(\d\) must be .*; Received .*/',
                string: $errors[0] ?? '',
            );
            $this->assertSame(
                expected: ValueMapTransformer::class,
                actual: $exception->getTransformerName(),
            );
            throw $exception;
        }
    }
}
