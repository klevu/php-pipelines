<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Handler;

use Klevu\Pipelines\Exception\PropertyAccessExceptionInterface;

interface PropertyAccessHandlerInterface
{
    /**
     * @param mixed $subject
     *
     * @return bool
     */
    public function isValidForPropertyAccess(
        mixed $subject,
    ): bool;

    /**
     * @param object|mixed[] $subject
     * @param string|int $propertyKey
     *
     * @return bool
     * @throws PropertyAccessExceptionInterface
     */
    public function propertyExists(
        object|array $subject,
        string|int $propertyKey,
    ): bool;

    /**
     * @param object|mixed[] $subject
     * @param string|int $propertyKey
     *
     * @return mixed
     * @throws PropertyAccessExceptionInterface
     */
    public function getPropertyValue(
        object|array $subject,
        string|int $propertyKey,
    ): mixed;

    /**
     * @param object|mixed[] $subject
     * @param string|int $propertyKey
     * @param mixed $propertyValue
     *
     * @return object|mixed[] Subject after modification
     * @throws PropertyAccessExceptionInterface
     */
    public function setPropertyValue(
        object|array $subject,
        string|int $propertyKey,
        mixed $propertyValue,
    ): object|array;

    /**
     * @param object|mixed[] $subject
     * @param string|int $propertyKey
     *
     * @return object|mixed[] Subject after modification
     * @throws PropertyAccessExceptionInterface
     */
    public function unsetProperty(
        object|array $subject,
        string|int $propertyKey,
    ): object|array;
}
