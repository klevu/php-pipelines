<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\ObjectManager;

use Klevu\Pipelines\Exception\ObjectManagerException;

interface ObjectManagerInterface extends ContainerInterface
{
    public const PRIORITY_NAMESPACE_SORT_ORDER = 50;
    public const DEFAULT_NAMESPACE_SORT_ORDER = 100;

    /**
     * @param string $id
     * @return object
     * @throws ObjectManagerException
     */
    public function get(string $id): object;

    /**
     * @param string $identifier
     * @param object|null $instance
     * @return void
     * @throws ObjectManagerException
     */
    public function addSharedInstance(string $identifier, ?object $instance): void;

    /**
     * @param string $namespace
     * @param int $sortOrder
     * @return void
     */
    public function registerNamespace(string $namespace, int $sortOrder = self::DEFAULT_NAMESPACE_SORT_ORDER): void;
}
