<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception;

class TransformationException extends \LogicException implements
    TransformationExceptionInterface
{
    /**
     * @var string
     */
    protected string $defaultMessage = '';
    /**
     * @var string
     */
    private readonly string $transformerName;
    /**
     * @var iterable<mixed>|null
     */
    private readonly ?iterable $arguments;
    /**
     * @var string[]
     */
    private readonly array $errors;
    /**
     * @var mixed
     */
    private readonly mixed $data;

    /**
     * @param string $transformerName
     * @param string[] $errors
     * @param iterable<mixed>|null $arguments
     * @param mixed|null $data
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $transformerName,
        array $errors,
        ?iterable $arguments = null,
        mixed $data = null,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->transformerName = $transformerName;
        $this->errors = array_map('strval', $errors);
        $this->arguments = $arguments;
        $this->data = $data;

        parent::__construct(
            message: $message ?: $this->defaultMessage,
            code: $code,
            previous: $previous,
        );
    }

    /**
     * @return string
     */
    public function getTransformerName(): string
    {
        return $this->transformerName;
    }

    /**
     * @return iterable<mixed>|null
     */
    public function getArguments(): ?iterable
    {
        return $this->arguments;
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
