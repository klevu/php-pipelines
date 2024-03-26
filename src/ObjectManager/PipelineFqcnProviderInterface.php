<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\ObjectManager;

interface PipelineFqcnProviderInterface
{
    /**
     * @param string $alias
     * @return string|null
     */
    public function getFqcn(string $alias): ?string;
}
