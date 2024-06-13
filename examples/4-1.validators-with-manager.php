<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

// NOTE: this file is not executable

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Klevu\Pipelines\ObjectManager\ObjectManagerInterface;
use Klevu\Pipelines\ObjectManager\ValidatorManager;
?>

<p>Custom validators at</p>
<ul>
    <li><code>\Acme\MyModule\Validator\MyIsValid</code></li>
    <li><code>\Acme\MyModule\Validator\IsNumeric</code></li>
    <li><code>\Acme\MyModule\Validator\IsNotEmpty</code></li>
</ul>

<h2>Without Args</h2>
<?php
    $validatorManager = new ValidatorManager();

    // Because the validator directory is automatically added to the registered namespaces
    $validator = $validatorManager->get('IsNotEmpty');
    // FQCN works regardless of namespaces
    $validator = $validatorManager->get(\Klevu\Pipelines\Validator\IsNotEmpty::class);
    // NOTE: The above two examples return different instances of the same class

    // Throws exception because class doesn't exist
    $validator = $validatorManager->get(Klevu\Pipelines\Validator\MyValidate::class);

    // Throws exception because not instance of ValidatorInterface
    $validator = $validatorManager->get(\Klevu\Pipelines\Extractor\Extractor::class);

    // Throws exception because not instantiable class
    $validator = $validatorManager->get('ValidatorInterface');
    $validator = $validatorManager->get(\Klevu\Pipelines\Validator\ValidatorInterface::class);

    // Will throw ClassNotFoundException as doesn't exist in registered namespaces
    $validator = $validatorManager->get('MyIsValid');
    // FQCN works, providing class implements ValidatorInterface
    $validator = $validatorManager->get(\Acme\MyModule\Validator\MyIsValid::class);
?>

<h2>With Shared Instances</h2>
<?php
    $validatorManager = new ValidatorManager(
        sharedInstances: [
            'MyIsValid' => new \Acme\MyModule\Validator\MyIsValid(),
            'IsNotEmpty' => new \Acme\MyModule\Validator\IsNotEmpty(),
             \Klevu\Pipelines\Validator\IsNumeric::class => new \Acme\MyModule\Validator\IsNumeric(),
            // @todo - detail changing default options
            'IsPositiveNumber' => new \Klevu\Pipelines\Validator\IsPositiveNumber(
                defaultAllowZero: false,
            ),
        ],
    );

    // Returns shared \Acme\MyModule\Validator\MyIsValid instance registered previously
    $validator = $validatorManager->get('MyIsValid');
    // Returns _new_ instantiation of \Acme\MyModule\Validator\MyIsValid (not the same one registered previously)
    $validator = $validatorManager->get(\Acme\MyModule\Validator\MyIsValid::class);

    // Returns instance of \Klevu\Pipelines\Validator\IsNotEmpty because FQCN
    $validator = $validatorManager->get(\Klevu\Pipelines\Validator\IsNotEmpty::class);
    // Returns instance of \Acme\MyModule\Validator\IsNotEmpty because FQCN
    $validator = $validatorManager->get(\Acme\MyModule\Validator\IsNotEmpty::class);
    // Returns instance of \Acme\MyModule\Validator\IsNotEmpty because explicitly registered
    $validator = $validatorManager->get('IsNotEmpty');

    // Returns instance of \Acme\MyModule\Validator\IsNumeric because overridden explicitly
    $validator = $validatorManager->get(\Klevu\Pipelines\Validator\IsNumeric::class);
    // Returns instance of \Acme\MyModule\Validator\IsNumeric because FQCN
    $validator = $validatorManager->get(\Acme\MyModule\Validator\IsNumeric::class);
    // Returns instance of \Klevu\Pipelines\Validator\IsNumeric because no instance registered so takes from namespace
    $validator = $validatorManager->get('IsNumeric');

    $validatorManager->addSharedInstance(
        identifier: 'IsNumeric',
        instance: $validatorManager->get(\Klevu\Pipelines\Validator\IsNumeric::class),
    );
    // Now, this (confusingly) returns \Acme\MyModule\Validator\IsNumeric
    $validator = $validatorManager->get('IsNumeric');

    $validatorManager->addSharedInstance(identifier: 'IsNumeric', instance: null);
    // Clearing the previous shared instance means this once again returns instance of
    //  \Klevu\Pipelines\Validator\IsNumeric from default namespace
    $validator = $validatorManager->get('IsNumeric');
?>

<h2>With Namespaces</h2>
<?php
    $validatorManager = new ValidatorManager(
        namespaces: [
            '\\Acme\\MyModule\\Validator\\' => ObjectManagerInterface::DEFAULT_NAMESPACE_SORT_ORDER,
        ],
    );

    // Returns new \Acme\MyModule\Validator\MyIsValid as found in registered in namespace
    $validator = $validatorManager->get('MyIsValid');
    // Returns new \Klevu\Pipelines\Validator\IsNumeric as default namespace always registered with default sort order
    //  (where the same sort order is specified, those registered first have higher priority)
    $validator = $validatorManager->get('IsNumeric');

    $validatorManager->registerNamespace(namespace: '\\Acme\\MyModule\\Validator\\', sortOrder: 0);
    // This returns the previously instantiated \Klevu\Pipelines\Validator\IsNumeric object as it has been cached
    $validator = $validatorManager->get('IsNumeric');

    $validatorManager->addSharedInstance(identifier: 'IsNumeric', instance: null);
    // This now returns a new \Acme\MyModule\Validator\IsNumeric object
    $validator = $validatorManager->get('IsNumeric');
?>

<h2>With Shared Instance and Namespace (lower priority)</h2>
<?php
    $validatorManager = new ValidatorManager(
        sharedInstances: [
            'IsNotEmpty' => new \Acme\MyModule\Validator\IsNotEmpty(),
        ],
        namespaces: [
            '\\Acme\\MyModule\\Validator\\' => ObjectManagerInterface::DEFAULT_NAMESPACE_SORT_ORDER,
        ],
    );

    // \Acme\MyModule\Validator\IsNotEmpty from shared instances
    $validator = $validatorManager->get('IsNotEmpty');

    $validatorManager->addSharedInstance(
        identifier: 'IsNotEmpty',
        instance: null,
    );
    // \Klevu\Pipelines\Validator\IsNotEmpty because Acme has a lower priority and shared instance cleared
    $validator = $validatorManager->get('IsNotEmpty');
?>

<h2>With Shared Instance and Namespace (higher priority)</h2>
<?php
    $validatorManager = new ValidatorManager(
        sharedInstances: [
            'IsNotEmpty' => new \Acme\MyModule\Validator\IsNotEmpty(),
        ],
        namespaces: [
            '\\Acme\\MyModule\\Validator\\' => 0,
        ],
    );

    // \Acme\MyModule\Validator\IsNotEmpty from shared instances
    $validator = $validatorManager->get('IsNotEmpty');

    $validatorManager->addSharedInstance(
        identifier: 'IsNotEmpty',
        instance: null,
    );
    // \Acme\MyModule\Validator\IsNotEmpty because Acme has a higher priority;
    // New object because we cleared the shared instance
    $validator = $validatorManager->get('IsNotEmpty');
?>
