<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception\PropertyAccess;

use Klevu\Pipelines\Exception\PropertyAccessExceptionInterface;

class InvalidSubjectException extends \LogicException implements PropertyAccessExceptionInterface
{
    /**
     * @var mixed
     */
    private readonly mixed $subject;

    /**
     * @param mixed $subject
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        mixed $subject,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);

        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getSubject(): mixed
    {
        return $this->subject;
    }
}
