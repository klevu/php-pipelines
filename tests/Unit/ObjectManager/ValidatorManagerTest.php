<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\ObjectManager;

use Klevu\Pipelines\Exception\ObjectManager\ClassNotFoundException;
use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\ObjectManager\ObjectInstantiationException;
use Klevu\Pipelines\ObjectManager\ObjectManagerInterface;
use Klevu\Pipelines\ObjectManager\ValidatorManager;
use Klevu\Pipelines\Test\Fixture\TestObject;
use Klevu\Pipelines\Test\Fixture\Validator\AbstractValidator;
use Klevu\Pipelines\Test\Fixture\Validator\IsNotEmpty as TestIsNotEmptyValidator;
use Klevu\Pipelines\Test\Fixture\Validator\TestValidator;
use Klevu\Pipelines\Validator\IsNotEmpty as CoreIsNotEmptyValidator;
use Klevu\Pipelines\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidatorManager::class)]
class ValidatorManagerTest extends TestCase
{
    #[Test]
    public function testGet_NoSharedInstance_NoRegisteredNamespace_Alias(): ValidatorInterface
    {
        $validatorManager = new ValidatorManager();

        $validator = $validatorManager->get('IsNotEmpty');
        $this->assertInstanceOf(CoreIsNotEmptyValidator::class, $validator);

        // Test cached version is used
        $this->assertSame($validator, $validatorManager->get('IsNotEmpty'));

        return $validator;
    }

    #[Test]
    #[Depends('testGet_NoSharedInstance_NoRegisteredNamespace_Alias')]
    public function testGet_NoSharedInstance_NoRegisteredNamespace_Fqcn_InDefaultNamespace(
        ValidatorInterface $aliasValidator,
    ): void {
        $validatorManager = new ValidatorManager();

        $validator = $validatorManager->get(CoreIsNotEmptyValidator::class);
        $this->assertInstanceOf(CoreIsNotEmptyValidator::class, $validator);

        // Test cached version is used
        $this->assertsame($validator, $validatorManager->get(CoreIsNotEmptyValidator::class));

        // No shared instance registered, so "IsNotEmpty" !== IsNotEmpty::class
        $this->assertEquals($aliasValidator, $validator);
        $this->assertNotSame($aliasValidator, $validator);
    }

    #[Test]
    public function testGet_NoSharedInstance_NoRegisteredNamespace_Alias_OutwithDefaultNamespace(): void
    {
        $validatorManager = new ValidatorManager();

        $this->expectException(ClassNotFoundException::class);
        $validatorManager->get('TestValidator');
    }

    #[Test]
    public function testGet_NoSharedInstance_NoRegisteredNamespace_Fqcn_OutwithDefaultNamespace(): void
    {
        $validatorManager = new ValidatorManager();

        $validator = $validatorManager->get(TestValidator::class);
        $this->assertInstanceOf(TestValidator::class, $validator);
    }

    #[Test]
    public function testGet_NoSharedInstance_NoRegisteredNamespace_ClassNotFound_Alias(): void
    {
        $validatorManager = new ValidatorManager();

        $this->expectException(ClassNotFoundException::class);
        $validatorManager->get('ClassNotFound');
    }

    #[Test]
    public function testGet_NoSharedInstance_NoRegisteredNamespace_ClassNotFound_Fqcn(): void
    {
        $validatorManager = new ValidatorManager();

        $this->expectException(ClassNotFoundException::class);
        $validatorManager->get('\\Klevu\\Pipelines\\Validator\\ClassNotFound');
    }

    #[Test]
    public function testGet_SharedInstance_NoRegisteredNamespace_Alias(): void
    {
        $testValidator = new TestValidator();
        $validatorManager = new ValidatorManager(
            sharedInstances: [
                'TestValidator' => $testValidator,
            ],
        );

        $validator = $validatorManager->get('TestValidator');
        $this->assertSame($testValidator, $validator);
    }

    #[Test]
    public function testGet_SharedInstance_NoRegisteredNamespace_Fqcn(): void
    {
        $testValidator = new TestValidator();
        $validatorManager = new ValidatorManager(
            sharedInstances: [
                'TestValidator' => $testValidator,
            ],
        );

        $validator = $validatorManager->get(TestValidator::class);
        $this->assertInstanceOf(TestValidator::class, $validator);
        $this->assertNotSame($testValidator, $validator);
    }

    #[Test]
    #[Depends('testGet_NoSharedInstance_NoRegisteredNamespace_Alias')]
    public function testGet_SharedInstance_NoRegisteredNamespace_AliasOverride(): void
    {
        $testValidator = new TestValidator();
        $validatorManager = new ValidatorManager(
            sharedInstances: [
                'IsNotEmpty' => $testValidator,
            ],
        );

        $aliasValidator = $validatorManager->get('IsNotEmpty');
        $this->assertSame($testValidator, $aliasValidator);

        $fqcnValidator = $validatorManager->get(CoreIsNotEmptyValidator::class);
        $this->assertInstanceOf(CoreIsNotEmptyValidator::class, $fqcnValidator);

        $validatorManager->addSharedInstance(
            identifier: CoreIsNotEmptyValidator::class,
            instance: $testValidator,
        );
        $newFqcnValidator = $validatorManager->get(CoreIsNotEmptyValidator::class);
        $this->assertSame($testValidator, $newFqcnValidator);

        $validatorManager->addSharedInstance(
            identifier: 'IsNotEmpty',
            instance: null,
        );
        $newAliasValidator = $validatorManager->get('IsNotEmpty');
        $this->assertInstanceOf(CoreIsNotEmptyValidator::class, $newAliasValidator);
    }

    #[Test]
    #[Depends('testGet_SharedInstance_NoRegisteredNamespace_Fqcn')]
    public function testGet_SharedInstance_NoRegisteredNamespace_FqcnOverride(): void
    {
        $testValidator = new TestValidator();
        $validatorManager = new ValidatorManager(
            sharedInstances: [
                CoreIsNotEmptyValidator::class => $testValidator,
            ],
        );

        $aliasValidator = $validatorManager->get('IsNotEmpty');
        $this->assertInstanceOf(CoreIsNotEmptyValidator::class, $aliasValidator);

        $fqcnValidator = $validatorManager->get(CoreIsNotEmptyValidator::class);
        $this->assertSame($testValidator, $fqcnValidator);

        $validatorManager->addSharedInstance(
            identifier: 'IsNotEmpty',
            instance: $testValidator,
        );
        $newAliasValidator = $validatorManager->get('IsNotEmpty');
        $this->assertSame($testValidator, $newAliasValidator);

        $validatorManager->addSharedInstance(
            identifier: CoreIsNotEmptyValidator::class,
            instance: null,
        );
        $newFqcnValidator = $validatorManager->get(CoreIsNotEmptyValidator::class);
        $this->assertInstanceOf(CoreIsNotEmptyValidator::class, $newFqcnValidator);
    }

    #[Test]
    public function testGet_NoSharedInstance_RegisteredNamespace_DefaultSortOrder(): void
    {
        $validatorManager = new ValidatorManager(
            namespaces: [
                '\\Klevu\\Pipelines\\Test\\Fixture\\Validator\\' => ObjectManagerInterface::DEFAULT_NAMESPACE_SORT_ORDER, // phpcs:ignore Generic.Files.LineLength.TooLong
            ],
        );

        $testValidatorAlias = $validatorManager->get('TestValidator');
        $this->assertInstanceOf(TestValidator::class, $testValidatorAlias);
        $testValidatorFqcn = $validatorManager->get(TestValidator::class);
        $this->assertInstanceOf(TestValidator::class, $testValidatorFqcn);
        $this->assertNotSame($testValidatorAlias, $testValidatorFqcn);

        $isNotEmptyValidatorAlias = $validatorManager->get('IsNotEmpty');
        $this->assertInstanceOf(CoreIsNotEmptyValidator::class, $isNotEmptyValidatorAlias);
        $isNotEmptyValidatorFqcnTest = $validatorManager->get(TestIsNotEmptyValidator::class);
        $this->assertInstanceOf(TestIsNotEmptyValidator::class, $isNotEmptyValidatorFqcnTest);
        $isNotEmptyValidatorFqcnCore = $validatorManager->get(CoreIsNotEmptyValidator::class);
        $this->assertInstanceOf(CoreIsNotEmptyValidator::class, $isNotEmptyValidatorFqcnCore);

        $validatorManager->registerNamespace(
            namespace: '\\Klevu\\Pipelines\\Test\\Fixture\\Validator\\',
            sortOrder: 0,
        );
        $newIsNotEmptyValidatorAlias = $validatorManager->get('IsNotEmpty');
        $this->assertSame($isNotEmptyValidatorAlias, $newIsNotEmptyValidatorAlias);

        $validatorManager->addSharedInstance(
            identifier: 'IsNotEmpty',
            instance: null,
        );
        $resetIsNotEmptyValidatorAlias = $validatorManager->get('IsNotEmpty');
        $this->assertInstanceOf(TestIsNotEmptyValidator::class, $resetIsNotEmptyValidatorAlias);

        $newIsNotEmptyValidatorFqcnTest = $validatorManager->get(TestIsNotEmptyValidator::class);
        $this->assertSame($isNotEmptyValidatorFqcnTest, $newIsNotEmptyValidatorFqcnTest);
        $this->assertNotSame($isNotEmptyValidatorFqcnTest, $resetIsNotEmptyValidatorAlias);
    }

    #[Test]
    public function testGet_NoSharedInstance_RegisteredNamespace_PrioritySortOrder(): void
    {
        $validatorManager = new ValidatorManager(
            namespaces: [
                '\\Klevu\\Pipelines\\Test\\Fixture\\Validator\\' => ObjectManagerInterface::DEFAULT_NAMESPACE_SORT_ORDER, // phpcs:ignore Generic.Files.LineLength.TooLong
            ],
        );

        $testValidatorAlias = $validatorManager->get('TestValidator');
        $this->assertInstanceOf(TestValidator::class, $testValidatorAlias);
        $testValidatorFqcn = $validatorManager->get(TestValidator::class);
        $this->assertInstanceOf(TestValidator::class, $testValidatorFqcn);
        $this->assertNotSame($testValidatorAlias, $testValidatorFqcn);

        $isNotEmptyValidatorAlias = $validatorManager->get('IsNotEmpty');
        $this->assertInstanceOf(CoreIsNotEmptyValidator::class, $isNotEmptyValidatorAlias);
        $isNotEmptyValidatorFqcnTest = $validatorManager->get(TestIsNotEmptyValidator::class);
        $this->assertInstanceOf(TestIsNotEmptyValidator::class, $isNotEmptyValidatorFqcnTest);
        $isNotEmptyValidatorFqcnCore = $validatorManager->get(CoreIsNotEmptyValidator::class);
        $this->assertInstanceOf(CoreIsNotEmptyValidator::class, $isNotEmptyValidatorFqcnCore);

        $validatorManager->registerNamespace('\\Klevu\\Pipelines\\Test\\Fixture\\Validator\\');
        $newIsNotEmptyValidatorAlias = $validatorManager->get('IsNotEmpty');
        $this->assertSame($isNotEmptyValidatorAlias, $newIsNotEmptyValidatorAlias);

        $validatorManager->addSharedInstance(
            identifier: 'IsNotEmpty',
            instance: null,
        );
        $resetIsNotEmptyValidatorAlias = $validatorManager->get('IsNotEmpty');
        $this->assertInstanceOf(CoreIsNotEmptyValidator::class, $resetIsNotEmptyValidatorAlias);

        $newIsNotEmptyValidatorFqcnCore = $validatorManager->get(CoreIsNotEmptyValidator::class);
        $this->assertSame($isNotEmptyValidatorFqcnCore, $newIsNotEmptyValidatorFqcnCore);
        $this->assertNotSame($isNotEmptyValidatorFqcnTest, $resetIsNotEmptyValidatorAlias);
    }

    #[Test]
    public function testGet_SharedInstance_RegisteredNamespace_DefaultSortOrder(): void
    {
        $testIsNotEmptyValidator = new TestIsNotEmptyValidator();
        $validatorManager = new ValidatorManager(
            sharedInstances: [
                'IsNotEmpty' => $testIsNotEmptyValidator,
            ],
            namespaces: [
                '\\Klevu\\Pipelines\\Test\\Fixture\\Validator\\' => ObjectManagerInterface::DEFAULT_NAMESPACE_SORT_ORDER, // phpcs:ignore Generic.Files.LineLength.TooLong
            ],
        );

        $validator = $validatorManager->get('IsNotEmpty');
        $this->assertSame($testIsNotEmptyValidator, $validator);

        $validatorManager->addSharedInstance(
            identifier: 'IsNotEmpty',
            instance: null,
        );
        $newValidator = $validatorManager->get('IsNotEmpty');
        $this->assertInstanceOf(CoreIsNotEmptyValidator::class, $newValidator);
    }

    #[Test]
    public function testGet_SharedInstance_RegisteredNamespace_PrioritySortOrder(): void
    {
        $testIsNotEmptyValidator = new TestIsNotEmptyValidator();
        $validatorManager = new ValidatorManager(
            sharedInstances: [
                'IsNotEmpty' => $testIsNotEmptyValidator,
            ],
            namespaces: [
                '\\Klevu\\Pipelines\\Test\\Fixture\\Validator\\' => 0,
            ],
        );

        $validator = $validatorManager->get('IsNotEmpty');
        $this->assertSame($testIsNotEmptyValidator, $validator);

        $validatorManager->addSharedInstance(
            identifier: 'IsNotEmpty',
            instance: null,
        );
        $newValidator = $validatorManager->get('IsNotEmpty');
        $this->assertInstanceOf(TestIsNotEmptyValidator::class, $newValidator);
        $this->assertNotSame($testIsNotEmptyValidator, $newValidator);
    }

    #[Test]
    public function testGet_NotInstantiable(): void
    {
        $validatorManager = new ValidatorManager();

        $this->expectException(ObjectInstantiationException::class);
        $validatorManager->get(AbstractValidator::class);
    }

    #[Test]
    public function testGet_NotTransformer(): void
    {
        $validatorManager = new ValidatorManager();

        $this->expectException(InvalidClassException::class);
        $validatorManager->get(TestObject::class);
    }
}
