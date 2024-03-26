<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\ObjectManager;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * @param string $id
     * @param mixed[] $constructorArgs
     * @return object
     */
    public function create(string $id, array $constructorArgs): object;
}
