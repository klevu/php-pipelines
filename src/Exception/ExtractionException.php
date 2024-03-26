<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception;

class ExtractionException extends \LogicException implements ExtractionExceptionInterface
{
    /**
     * @var string
     */
    protected string $defaultMessage = '';
    /**
     * @var string[]
     */
    private readonly array $errors;
    /**
     * @var string|null
     */
    private readonly ?string $accessor;

    /**
     * @param string[] $errors
     * @param string|null $accessor
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        array $errors = [],
        ?string $accessor = '',
        int $code = 0,
        string $message = '',
        ?\Throwable $previous = null,
    ) {
        $this->errors = array_map('strval', $errors);
        $this->accessor = $accessor;

        parent::__construct(
            message: $message
                ?: $this->defaultMessage,
            code: $code,
            previous: $previous,
        );
    }

    /**
     * @return string|null
     */
    public function getAccessor(): ?string
    {
        return $this->accessor;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
