<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/** @noinspection PhpRedundantOptionalArgumentInspection */

declare(strict_types=1);

namespace Klevu\Pipelines\ObjectManager;

class PipelineFqcnProvider implements PipelineFqcnProviderInterface
{
    /**
     * @var string[]
     */
    private array $aliasToFqcn = [];
    /**
     * @var array<string, int>
     */
    private array $registeredNamespaces = [];

    /**
     * @param string[] $aliasToFqcn
     * @param array<string, int> $namespaces
     */
    public function __construct(
        array $aliasToFqcn = [],
        array $namespaces = [],
    ) {
        array_walk($aliasToFqcn, [$this, 'addAlias']);

        $this->registerNamespace(
            namespace: '\\Klevu\\Pipelines\\Pipeline\\',
            sortOrder: ObjectManagerInterface::DEFAULT_NAMESPACE_SORT_ORDER,
        );
        foreach ($namespaces as $namespace => $sortOrder) {
            $this->registerNamespace(
                namespace: $namespace,
                sortOrder: $sortOrder,
            );
        }
    }

    /**
     * @param string $fqcn
     * @param string $alias
     * @return void
     */
    public function addAlias(string $fqcn, string $alias): void
    {
        // @todo Validation
        $this->aliasToFqcn[$alias] = $fqcn;
    }

    /**
     * @param string $namespace
     * @param int $sortOrder
     * @return void
     */
    public function registerNamespace(
        string $namespace,
        int $sortOrder = ObjectManagerInterface::DEFAULT_NAMESPACE_SORT_ORDER,
    ): void {
        $namespace = '\\' . trim($namespace, '\\') . '\\';

        // Unsetting here means that if we change the priority of an existing namespace to the same
        //  as another key, it is moved to the end of the priority group
        unset($this->registeredNamespaces[$namespace]);
        $this->registeredNamespaces[$namespace] = $sortOrder;
        asort($this->registeredNamespaces, SORT_NUMERIC);
    }

    /**
     * @param string $alias
     * @return string|null
     */
    public function getFqcn(string $alias): ?string
    {
        if (!isset($this->aliasToFqcn[$alias])) {
            switch (true) {
                case class_exists($alias):
                    $this->aliasToFqcn[$alias] = $alias;
                    break;

                default:
                    $namespaces = array_merge(
                        array_keys($this->registeredNamespaces),
                        ['\\'],
                    );
                    $fqcn = $this->determineFqcn($alias, $namespaces);
                    if ($fqcn) {
                        $this->aliasToFqcn[$alias] = $fqcn;
                    }
                    break;
            }
        }

        return $this->aliasToFqcn[$alias] ?? null;
    }

    /**
     * @param string $id
     * @param string[] $namespaces
     * @return class-string|null
     */
    private function determineFqcn(
        string $id,
        array $namespaces,
    ): ?string {
        $fqcn = null;
        switch (true) {
            case class_exists($id):
                $fqcn = $id;
                break;

            case !str_starts_with($id, '\\'):
                foreach ($namespaces as $namespace) {
                    if (class_exists($namespace . $id)) {
                        $fqcn = $namespace . $id;
                        break;
                    }
                }
                break;
        }

        return $fqcn;
    }
}
