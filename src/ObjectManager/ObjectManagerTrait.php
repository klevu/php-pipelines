<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\ObjectManager;

use Klevu\Pipelines\Exception\ObjectManager\ClassNotFoundException;
use Klevu\Pipelines\Exception\ObjectManager\DependencyResolutionException;
use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\ObjectManager\ObjectInstantiationException;
use Klevu\Pipelines\Exception\ObjectManagerException;

trait ObjectManagerTrait
{
    /**
     * @var array<string, int>
     */
    private array $registeredNamespaces = [];
    /**
     * @var object[]
     */
    private array $sharedInstances = [];

    /**
     * @param object $instance
     * @return bool
     */
    abstract private function isValidInstance(object $instance): bool;

    /**
     * @param string $id
     * @return object
     * @throws ObjectManagerException
     * @throws ObjectInstantiationException
     * @throws ClassNotFoundException
     * @throws InvalidClassException
     */
    public function get(string $id): object
    {
        $instance = $this->sharedInstances[$id] ?? null;
        if ($instance) {
            return $instance;
        }

        $instance = $this->create($id);

        $this->addSharedInstance(
            identifier: $id,
            instance: $instance,
        );

        return $instance;
    }

    /**
     * @param string $id
     * @param mixed[] $constructorArgs
     * @return object
     */
    public function create(string $id, array $constructorArgs = []): object
    {
        $namespaces = array_merge(
            array_keys($this->registeredNamespaces),
            ['\\'],
        );
        $instance = $this->resolve($id, $namespaces, $constructorArgs);

        if (null === $instance) {
            throw new ClassNotFoundException(
                identifier: $id,
                namespaces: $namespaces,
            );
        }

        return $instance;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->sharedInstances);
    }

    /**
     * @param string $identifier
     * @param object|null $instance
     * @return void
     * @throws ObjectManagerException
     * @throws InvalidClassException
     */
    public function addSharedInstance(string $identifier, ?object $instance): void
    {
        if (null === $instance) {
            unset($this->sharedInstances[$identifier]);
            return;
        }

        if (!$this->isValidInstance($instance)) {
            throw new InvalidClassException(
                identifier: $identifier,
                instance: $instance,
            );
        }

        $this->sharedInstances[$identifier] = $instance;
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
     * @param string $id
     * @param string[] $namespaces
     * @param mixed[] $constructorArgs
     * @return object|null
     * @throws ObjectInstantiationException
     * @throws DependencyResolutionException
     */
    private function resolve(string $id, array $namespaces, array $constructorArgs = []): ?object
    {
        $fqcn = $this->determineFqcn(
            id: $id,
            namespaces: $namespaces,
        );

        if (null === $fqcn) {
            return null;
        }

        try {
            $reflector = new \ReflectionClass($fqcn);
            if (!$reflector->isInstantiable()) {
                throw new ObjectInstantiationException(
                    identifier: $id,
                    message: sprintf(
                        'Encountered error instantiating object: %s is not instantiable',
                        $fqcn,
                    ),
                );
            }

            $constructor = $reflector->getConstructor();
            if (null === $constructor) {
                $instance = $reflector->newInstance();
            } else {
                $instance = $reflector->newInstanceArgs(
                    $this->getDependencies(
                        parameters: $constructor->getParameters(),
                        constructorArgs: $constructorArgs,
                    ),
                );
            }

            $this->addSharedInstance($id, $instance);
        } catch (DependencyResolutionException $exception) {
            throw new DependencyResolutionException(
                dependency: $exception->getDependency(),
                identifier: $id,
                message: $exception->getMessage(),
                previous: $exception,
            );
        } catch (\ReflectionException $exception) {
            throw new ObjectInstantiationException(
                identifier: $id,
                message: sprintf(
                    'Encountered error instantiating %s: %s',
                    $fqcn,
                    $exception->getMessage(),
                ),
                previous: $exception,
            );
        }

        return $instance;
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

    /**
     * @param \ReflectionParameter[] $parameters
     * @param mixed[] $constructorArgs
     * @return mixed[]
     * @throws DependencyResolutionException
     */
    private function getDependencies(
        array $parameters,
        array $constructorArgs = [],
    ): array {
        $dependencies = [];
        foreach ($parameters as $parameter) {
            if (
                $parameter instanceof \ReflectionParameter
                && array_key_exists($parameter->getName(), $constructorArgs)
            ) {
                $dependencies[] = $constructorArgs[$parameter->getName()];
                continue;
            }

            $type = $parameter->getType();
            if (!($type instanceof \ReflectionNamedType) || $type->isBuiltin()) {
                if (!$parameter->isDefaultValueAvailable()) {
                    throw new DependencyResolutionException(
                        dependency: $parameter->getName(),
                        identifier: '',
                        message: 'Cannot resolve builtin dependency with no default value available',
                    );
                }

                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            $container = Container::getInstance();
            $dependencies[] = match (true) {
                $parameter->isDefaultValueAvailable() => $parameter->getDefaultValue(),
                $parameter->allowsNull() => null,
                default => $container->get($type->getName()),
            };
        }

        return $dependencies;
    }
}
