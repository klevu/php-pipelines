<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\ObjectManager;

/**
 * @method object get(string $id)
 */
class Container implements ObjectManagerInterface
{
    use ObjectManagerTrait;

    /**
     * @var ContainerInterface|null
     */
    private static ?ContainerInterface $instance = null;

    /**
     * @return void
     */
    public function __construct(
        ?ContainerInterface $instance = null,
    ) {
        $this->init($instance);
    }

    /**
     * @param ContainerInterface|null $instance
     * @return ContainerInterface
     */
    public function init(
        ?ContainerInterface $instance = null,
    ): ContainerInterface {
        if (null !== $instance) {
            self::setInstance($instance);
        }

        return $this;
    }

    /**
     * @return ContainerInterface
     */
    public static function getInstance(): ContainerInterface
    {
        if (!self::$instance) {
            // Does not use setInstance for phpstan's benefit
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param ContainerInterface $container
     * @return void
     */
    public static function setInstance(ContainerInterface $container): void
    {
        self::$instance = $container;
    }

    /**
     * @param object $instance
     * @return bool
     */
    private function isValidInstance(
        object $instance, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): bool {
        return true;
    }
}
