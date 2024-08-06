<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Handler;

use Klevu\Pipelines\Exception\PropertyAccess\InvalidSubjectException;
use Klevu\Pipelines\Exception\PropertyAccess\NoSuchPropertyException;
use Klevu\Pipelines\Exception\PropertyAccessExceptionInterface;

class PropertyAccessHandler implements PropertyAccessHandlerInterface
{
    /**
     * @param mixed $subject
     *
     * @return bool
     */
    public function isValidForPropertyAccess(
        mixed $subject,
    ): bool {
        return is_array($subject) || is_object($subject);
    }

    /**
     * @param mixed $subject
     * @param string|int $propertyKey
     *
     * @return bool
     * @throws PropertyAccessExceptionInterface
     * @throws InvalidSubjectException
     */
    public function propertyExists(
        mixed $subject,
        string|int $propertyKey,
    ): bool {
        return match (true) {
            !$this->isValidForPropertyAccess($subject) => throw new InvalidSubjectException($subject),
            is_array($subject) => array_key_exists($propertyKey, $subject),
            $subject instanceof \ArrayAccess => $subject->offsetExists($propertyKey),
            is_object($subject) => property_exists($subject, (string)$propertyKey),
            default => false,
        };
    }

    /**
     * @param object|mixed[] $subject
     * @param string|int $propertyKey
     *
     * @return mixed
     * @throws PropertyAccessExceptionInterface
     * @throws InvalidSubjectException
     * @throws NoSuchPropertyException
     */
    public function getPropertyValue(
        object|array $subject,
        string|int $propertyKey,
    ): mixed {
        return match (true) {
            !$this->propertyExists($subject, $propertyKey) => throw new NoSuchPropertyException(
                subject: $subject,
                propertyKey: $propertyKey,
            ),
            is_array($subject) => $subject[$propertyKey],
            $subject instanceof \ArrayAccess => $subject->offsetGet($propertyKey),
            is_object($subject) => $subject->{$propertyKey},
            default => null,
        };
    }

    /**
     * @param object|mixed[] $subject
     * @param string|int $propertyKey
     * @param mixed $propertyValue
     *
     * @return object|mixed[]
     * @throws PropertyAccessExceptionInterface
     * @throws InvalidSubjectException
     */
    public function setPropertyValue(
        object|array $subject,
        string|int $propertyKey,
        mixed $propertyValue,
    ): object|array {
        $return = is_object($subject)
            ? clone $subject
            : $subject;

        switch (true) {
            case !$this->isValidForPropertyAccess($return):
                throw new InvalidSubjectException($return);

            case is_array($return):
                $return[$propertyKey] = $propertyValue;
                break;

            case $return instanceof \ArrayAccess:
                $return->offsetSet($propertyKey, $propertyValue);
                break;

            case is_object($return):
                $return->{$propertyKey} = $propertyValue;
                break;
        }

        return $return;
    }

    /**
     * @param object|mixed[] $subject
     * @param string|int $propertyKey
     *
     * @return object|mixed[]
     * @throws PropertyAccessExceptionInterface
     * @throws InvalidSubjectException
     */
    public function unsetProperty(
        object|array $subject,
        string|int $propertyKey,
    ): object|array {
        $return = is_object($subject)
            ? clone $subject
            : $subject;

        switch (true) {
            case !$this->isValidForPropertyAccess($return):
                throw new InvalidSubjectException($return);

            case is_array($return):
                unset($return[$propertyKey]);
                break;

            case $return instanceof \ArrayAccess:
                $return->offsetUnset($propertyKey);
                break;

            case is_object($return):
                unset($return->{$propertyKey});
                break;
        }

        return $return;
    }
}
