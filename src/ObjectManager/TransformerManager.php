<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/** @noinspection PhpRedundantOptionalArgumentInspection */

declare(strict_types=1);

namespace Klevu\Pipelines\ObjectManager;

use Klevu\Pipelines\Transformer\TransformerInterface;

class TransformerManager implements ObjectManagerInterface
{
    use ObjectManagerTrait;

    /**
     * @param array<string, TransformerInterface>|null $sharedInstances identifier => instance
     * @param array<string, int>|null $namespaces namespace => sort_order
     */
    public function __construct(
        ?array $sharedInstances = null,
        ?array $namespaces = null,
    ) {
        foreach ($sharedInstances ?? [] as $identifier => $instance) {
            $this->addSharedInstance(
                identifier: $identifier,
                instance: $instance,
            );
        }

        $this->registerNamespace(
            namespace: '\\Klevu\\Pipelines\\Transformer\\',
            sortOrder: static::DEFAULT_NAMESPACE_SORT_ORDER,
        );
        foreach ($namespaces ?? [] as $namespace => $sortOrder) {
            $this->registerNamespace(
                namespace: $namespace,
                sortOrder: is_numeric($sortOrder) ? (int)$sortOrder : static::DEFAULT_NAMESPACE_SORT_ORDER,
            );
        }
    }

    /**
     * {@inheritDoc}
     *
     * @param object $instance
     * @return bool
     */
    private function isValidInstance(object $instance): bool
    {
        return $instance instanceof TransformerInterface;
    }
}
