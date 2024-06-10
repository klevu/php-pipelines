<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

// NOTE: this file is not executable

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Klevu\Pipelines\Model\Transformation\EscapeHtml\Quotes;
use Klevu\Pipelines\Model\Transformation\EscapeHtml\TranslationTables;
use Klevu\Pipelines\ObjectManager\ObjectManagerInterface;
use Klevu\Pipelines\ObjectManager\TransformerManager;
use Klevu\Pipelines\Transformer\EscapeHtml;
?>

<p>Custom transformers at</p>
<ul>
    <li><code>\Acme\MyModule\Transformer\MyConvert</code></li>
    <li><code>\Acme\MyModule\Transformer\ToString</code></li>
    <li><code>\Acme\MyModule\Transformer\Trim</code></li>
</ul>

<h2>Without Args</h2>
<?php
    $transformerManager = new TransformerManager();

    // Because the transformer directory is automatically added to the registered namespaces
    $transformer = $transformerManager->get('Trim');
    // FQCN works regardless of namespaces registered by default
    $transformer = $transformerManager->get(\Klevu\Pipelines\Transformer\Trim::class);
    // NOTE: The above two examples return different instances of the same class

    // Throws exception because class doesn't exist
    $transformer = $transformerManager->get(\Klevu\Pipelines\Transformer\MyConvert::class);

    // Throws exception because not instance of TransformerInterface
    $transformer = $transformerManager->get(\Klevu\Pipelines\Extractor\Extractor::class);

    // Throws exception because not instantiable class
    $transformer = $transformerManager->get('AbstractConcatenate');
    $transformer = $transformerManager->get(\Klevu\Pipelines\Transformer\AbstractConcatenate::class);

    // Will throw ClassNotFoundException as doesn't exist in registered namespaces
    $transformer = $transformerManager->get('MyConvert');
    // FQCN works, providing class implements TransformerInterface
    $transformer = $transformerManager->get(\Acme\MyModule\Transformer\MyConvert::class);
?>

<h2>With Shared Instances</h2>
<?php
    $transformerManager = new TransformerManager(
        sharedInstances: [
            'MyConvert' => new \Acme\MyModule\Transformer\MyConvert(),
            'Trim' => new \Acme\MyModule\Transformer\Trim(),
            \Klevu\Pipelines\Transformer\ToString::class => new \Acme\MyModule\Transformer\ToString(),
            // @todo - detail changing default options
            'EscapeHtml' => new EscapeHtml(
                defaultQuotes: Quotes::NOQUOTES,
                defaultTranslationTable: TranslationTables::HTML401,
                defaultAllowDoubleEncoding: true,
            ),
        ],
    );

    // Returns shared \Acme\MyModule\Transformer\MyConvert instance registered previously
    $transformer = $transformerManager->get('MyConvert');
    // Returns _new_ instantiation of \Acme\MyModule\Transformer\MyConvert (not the same one registered previously)
    $transformer = $transformerManager->get(\Acme\MyModule\Transformer\MyConvert::class);

    // Returns instance of \Klevu\Pipelines\Transformer\Trim because FQCN
    $transformer = $transformerManager->get(\Klevu\Pipelines\Transformer\Trim::class);
    // Returns instance of \Acme\MyModule\Transformer\Trim because FQCN
    $transformer = $transformerManager->get(\Acme\MyModule\Transformer\Trim::class);
    // Returns instance of \Acme\MyModule\Transformer\Trim because explicitly registered
    $transformer = $transformerManager->get('Trim');

    // Returns instance of \Acme\MyModule\Transformer\ToString because overridden explicitly
    $transformer = $transformerManager->get(\Klevu\Pipelines\Transformer\ToString::class);
    // Returns instance of \Acme\MyModule\Transformer\ToString because FQCN
    $transformer = $transformerManager->get(\Acme\MyModule\Transformer\ToString::class);
    // Returns instance of \Klevu\Pipelines\Transformer\ToString because no instance registered so takes from namespace
    $transformer = $transformerManager->get('ToString');

    $transformerManager->addSharedInstance(
        identifier: 'ToString',
        instance: $transformerManager->get(\Klevu\Pipelines\Transformer\ToString::class),
    );
    // Now, this (confusingly) returns \Acme\MyModule\Transformer\ToString
    $transformer = $transformerManager->get('ToString');

    $transformerManager->addSharedInstance(identifier: 'ToString', instance: null);
    // Clearing the previous shared instance means this once again returns instance of
    //  \Klevu\Pipelines\Transformer\ToString from default namespace
    $transformer = $transformerManager->get('ToString');
?>

<h2>With Namespaces</h2>
<?php
    $transformerManager = new TransformerManager(
        namespaces: [
            '\\Acme\\MyModule\\Transformer\\' => ObjectManagerInterface::DEFAULT_NAMESPACE_SORT_ORDER,
        ],
    );

    // Returns new \Acme\MyModule\Transformer\MyConvert as found in registered namespace
    $transformer = $transformerManager->get('MyConvert');
    // Returns new \Klevu\Pipelines\Transformer\Trim as default namespace always registered with default sort order
    //  (where the same sort order is specified, those registered first have higher priority)
    $transformer = $transformerManager->get('Trim');

    $transformerManager->registerNamespace(namespace: '\\Acme\\MyModule\\Transformer\\', sortOrder: 0);
    // This returns the previously instantiated \Klevu\Pipelines\Transformer\Trim object as it has been cached
    $transformer = $transformerManager->get('Trim');

    $transformerManager->addSharedInstance(identifier: 'Trim', instance: null);
    // This now returns a new \Acme\MyModule\Transformer\Trim object
    $transformer = $transformerManager->get('Trim');
?>

<h2>With Shared Instance and Namespace (lower priority)</h2>
<?php
    $transformerManager = new TransformerManager(
        sharedInstances: [
            'Trim' => new \Acme\MyModule\Transformer\Trim(),
        ],
        namespaces: [
            '\\Acme\\MyModule\\Transformer\\' => ObjectManagerInterface::DEFAULT_NAMESPACE_SORT_ORDER,
        ],
    );

    // \Acme\MyModule\Transformer\Trim from shared instances
    $transformer = $transformer->get('Trim');

    $transformerManager->addSharedInstance(
        identifier: 'Trim',
        instance: null,
    );
    // \Klevu\Pipelines\Transformer\Trim because Acme has a lower priority and shared instance cleared
    $transformer = $transformerManager->get('Trim');
?>

<h2>With Shared Instance and Namespace (higher priority)</h2>
<?php
    $transformerManager = new TransformerManager(
        sharedInstances: [
            'Trim' => new \Acme\MyModule\Transformer\Trim(),
        ],
        namespaces: [
            '\\Acme\\MyModule\\Transformer\\' => 0,
        ],
    );

    // \Acme\MyModule\Transformer\Trim from shared instances
    $transformer = $transformerManager->get('Trim');

    $transformerManager->addSharedInstance(
        identifier: 'Trim',
        instance: null,
    );
    // \Acme\MyModule\Transformer\Trim because Acme has a higher priority;
    // New object because we cleared the shared instance
    $transformer = $transformerManager->get('Trim');
?>
