<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception\ObjectManager;

use Klevu\Pipelines\Exception\ObjectManagerException;
use Psr\Container\ContainerExceptionInterface;

class ObjectInstantiationException extends ObjectManagerException implements ContainerExceptionInterface
{
}
