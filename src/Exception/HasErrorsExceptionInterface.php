<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception;

interface HasErrorsExceptionInterface extends \Throwable
{
    /**
     * @return string[]
     */
    public function getErrors(): array;
}
