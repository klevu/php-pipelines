<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$container = \Klevu\Pipelines\ObjectManager\Container::getInstance();

$transformerManager = new \Klevu\Pipelines\ObjectManager\TransformerManager();

$transformer = $transformerManager->get('ToLowerCase');
var_dump($container, $transformerManager);