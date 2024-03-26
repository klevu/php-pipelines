<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception;

interface TransformationExceptionInterface extends
    HasErrorsExceptionInterface
{
    /**
     * @return string
     */
    public function getTransformerName(): string;

    /**
     * @return iterable<mixed>|null
     */
    public function getArguments(): ?iterable;

    /**
     * @return mixed
     */
    public function getData(): mixed;
}
