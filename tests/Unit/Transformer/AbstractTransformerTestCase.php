<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Transformer;

use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Transformer\TransformerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @todo (Valid and Invalid) Arguments in InvalidType tests
 * @todo Context (extractions) in InvalidType tests
 * @todo Invalid arguments (arguments provider)
 */
abstract class AbstractTransformerTestCase extends TestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = '';

    /**
     * @return mixed[][]
     */
    abstract public static function dataProvider_testTransform_Valid(): array;

    /**
     * @return mixed[][]
     */
    abstract public static function dataProvider_testTransform_InvalidInputData(): array;

    /**
     * @return mixed[][]
     */
    abstract public static function dataProvider_testTransform_InvalidArguments(): array;

    #[Test]
    public function testImplementsInterface(): void
    {
        $validator = $this->initialiseTestObject();
        $this->assertInstanceOf(TransformerInterface::class, $validator);
    }

    /**
     * @param mixed $data
     * @param mixed $expectedResult
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<int|string, mixed>|null $context
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_Valid')]
    public function testTransform_Valid(
        mixed $data,
        mixed $expectedResult,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): void {
        /** @var TransformerInterface $transformer */
        $transformer = $this->initialiseTestObject();

        $result = null;
        $errors = [];
        try {
            $result = $transformer->transform(
                data: $data,
                arguments: $arguments,
                context: $context,
            );
        } catch (TransformationException | ValidationException $exception) {
            $errors = [
                'data' => $data,
                'exception' => $exception->getMessage(),
                'errors' => $exception->getErrors(),
                'arguments' => array_map(
                    fn (Argument $argument): string => $this->getArgumentValueForReport($argument),
                    $arguments?->toArray() ?: [],
                ),
            ];
        }

        $message = json_encode([
            'data' => $data,
            'expectedResult' => $expectedResult,
            'arguments' => array_map(
                fn (Argument $argument): string => $this->getArgumentValueForReport($argument),
                $arguments?->toArray() ?: [],
            ),
        ]) ?: '';
        $this->assertSame(
            expected: [],
            actual: $errors,
            message: $message,
        );
        $this->assertSame(
            expected: $expectedResult,
            actual: $result,
            message: $message,
        );
    }

    #[Test]
    #[DataProvider('dataProvider_testTransform_InvalidInputData')]
    public function testTransform_InvalidInputData(
        mixed $data,
    ): void {
        /** @var TransformerInterface $transformer */
        $transformer = $this->initialiseTestObject();

        $this->expectException(InvalidInputDataException::class);
        $transformer->transform(
            data: $data,
        );
    }

    /**
     * @param mixed $data
     * @param mixed|null $expectedResult
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_InvalidArguments')]
    public function testTransform_InvalidArguments(
        mixed $data,
        mixed $expectedResult, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context = null,
    ): void {
        /** @var TransformerInterface $transformer */
        $transformer = $this->initialiseTestObject();

        $this->expectException(InvalidTransformationArgumentsException::class);
        $transformer->transform(
            data: $data,
            arguments: $arguments,
            context: $context,
        );
    }

    /**
     * @return object
     * @throws \LogicException
     */
    protected function initialiseTestObject(): object
    {
        if (!$this->transformerFqcn) {
            throw new \LogicException('transformerFqcn must be defined');
        }

        return new $this->transformerFqcn();
    }

    /**
     * @param Argument|null $argument
     *
     * @return string
     */
    protected function getArgumentValueForReport(?Argument $argument): string
    {
        $return = match (true) {
            null === $argument => 'null',
            is_scalar($argument->getValue()),
            is_array($argument->getValue()) => json_encode([
                $argument->getKey() => $argument->getValue(),
            ]),
            $argument->getValue() instanceof ArgumentIterator => json_encode(
                array_map(
                    fn (Argument $childArgument): string => stripslashes(
                        $this->getArgumentValueForReport($childArgument),
                    ),
                    $argument->getValue()->toArray(),
                ),
            ),
            $argument->getValue() instanceof Argument => json_encode([
                $argument->getKey() => $this->getArgumentValueForReport($argument->getValue()),
            ]),
            default => json_encode([
                $argument->getKey() => stripslashes(
                    json_encode($argument->getValue()) ?: '',
                ),
            ]),
        };

        return $return ?: '';
    }
}
