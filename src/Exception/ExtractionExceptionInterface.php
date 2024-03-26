<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception;

interface ExtractionExceptionInterface extends HasErrorsExceptionInterface
{
    /**
     * @return string|null
     */
    public function getAccessor(): ?string;
}
