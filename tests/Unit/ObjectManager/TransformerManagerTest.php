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
use Klevu\Pipelines\ObjectManager\TransformerManager;
use Klevu\Pipelines\Test\Fixture\TestObject;
use Klevu\Pipelines\Test\Fixture\Transformer\TestTransformer;
use Klevu\Pipelines\Test\Fixture\Transformer\Trim as TestTrimTransformer;
use Klevu\Pipelines\Transformer\AbstractConcatenate;
use Klevu\Pipelines\Transformer\TransformerInterface;
use Klevu\Pipelines\Transformer\Trim as CoreTrimTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransformerManager::class)]
class TransformerManagerTest extends TestCase
{
    #[Test]
    public function testGet_NoSharedInstance_NoRegisteredNamespace_Alias(): TransformerInterface
    {
        $transformerManager = new TransformerManager();

        $transformer = $transformerManager->get('Trim');
        $this->assertInstanceOf(CoreTrimTransformer::class, $transformer);

        // Test cached version is used
        $this->assertSame($transformer, $transformerManager->get('Trim'));

        return $transformer;
    }

    #[Test]
    #[Depends('testGet_NoSharedInstance_NoRegisteredNamespace_Alias')]
    public function testGet_NoSharedInstance_NoRegisteredNamespace_Fqcn_InDefaultNamespace(
        TransformerInterface $aliasTransformer,
    ): void {
        $transformerManager = new TransformerManager();

        $transformer = $transformerManager->get(CoreTrimTransformer::class);
        $this->assertInstanceOf(CoreTrimTransformer::class, $transformer);

        // Test cached version is used
        $this->assertSame($transformer, $transformerManager->get(CoreTrimTransformer::class));

        // No shared instances registered, so "Trim" !== Trim::class
        $this->assertEquals($aliasTransformer, $transformer);
        $this->assertNotSame($aliasTransformer, $transformer);
    }

    #[Test]
    public function testGet_NoSharedInstance_NoRegisteredNamespace_Alias_OutwithDefaultNamespace(): void
    {
        $transformerManager = new TransformerManager();

        $this->expectException(ClassNotFoundException::class);
        $transformerManager->get('TestTransformer');
    }

    #[Test]
    public function testGet_NoSharedInstance_NoRegisteredNamespace_Fqcn_OutwithDefaultNamespace(): void
    {
        $transformerManager = new TransformerManager();

        $transformer = $transformerManager->get(TestTransformer::class);
        $this->assertInstanceOf(TestTransformer::class, $transformer);
    }

    #[Test]
    public function testGet_NoSharedInstance_NoRegisteredNamespace_ClassNotFound_Alias(): void
    {
        $transformerManager = new TransformerManager();

        $this->expectException(ClassNotFoundException::class);
        $transformerManager->get('ClassNotFound');
    }

    #[Test]
    public function testGet_NoSharedInstance_NoRegisteredNamespace_ClassNotFound_Fqcn(): void
    {
        $transformerManager = new TransformerManager();

        $this->expectException(ClassNotFoundException::class);
        $transformerManager->get('\\Klevu\\Pipelines\\Transformer\\ClassNotFound');
    }

    #[Test]
    public function testGet_SharedInstance_NoRegisteredNamespace_Alias(): void
    {
        $testTransformer = new TestTransformer();
        $transformerManager = new TransformerManager(
            sharedInstances: [
                'TestTransformer' => $testTransformer,
            ],
        );

        $transformer = $transformerManager->get('TestTransformer');
        $this->assertSame($testTransformer, $transformer);
    }

    #[Test]
    public function testGet_SharedInstance_NoRegisteredNamespace_Fqcn(): void
    {
        $testTransformer = new TestTransformer();
        $transformerManager = new TransformerManager(
            sharedInstances: [
                'TestTransformer' => $testTransformer,
            ],
        );

        $transformer = $transformerManager->get(TestTransformer::class);
        $this->assertInstanceOf(TestTransformer::class, $transformer);
        $this->assertNotSame($testTransformer, $transformer);
    }

    #[Test]
    #[Depends('testGet_NoSharedInstance_NoRegisteredNamespace_Alias')]
    public function testGet_SharedInstance_NoRegisteredNamespace_AliasOverride(): void
    {
        $testTransformer = new TestTransformer();
        $transformerManager = new TransformerManager(
            sharedInstances: [
                'Trim' => $testTransformer,
            ],
        );

        $aliasTransformer = $transformerManager->get('Trim');
        $this->assertSame($testTransformer, $aliasTransformer);

        $fqcnTransformer = $transformerManager->get(CoreTrimTransformer::class);
        $this->assertInstanceOf(CoreTrimTransformer::class, $fqcnTransformer);

        $transformerManager->addSharedInstance(
            identifier: CoreTrimTransformer::class,
            instance: $testTransformer,
        );
        $newFqcnTransformer = $transformerManager->get(CoreTrimTransformer::class);
        $this->assertSame($testTransformer, $newFqcnTransformer);

        $transformerManager->addSharedInstance(
            identifier: 'Trim',
            instance: null,
        );
        $newAliasTransformer = $transformerManager->get('Trim');
        $this->assertInstanceOf(CoreTrimTransformer::class, $newAliasTransformer);
    }

    #[Test]
    #[Depends('testGet_SharedInstance_NoRegisteredNamespace_Fqcn')]
    public function testGet_SharedInstance_NoRegisteredNamespace_FqcnOverride(): void
    {
        $testTransformer = new TestTransformer();
        $transformerManager = new TransformerManager(
            sharedInstances: [
                CoreTrimTransformer::class => $testTransformer,
            ],
        );

        $aliasTransformer = $transformerManager->get('Trim');
        $this->assertInstanceOf(CoreTrimTransformer::class, $aliasTransformer);

        $fqcnTransformer = $transformerManager->get(CoreTrimTransformer::class);
        $this->assertSame($testTransformer, $fqcnTransformer);

        $transformerManager->addSharedInstance(
            identifier: 'Trim',
            instance: $testTransformer,
        );
        $newAliasTransformer = $transformerManager->get('Trim');
        $this->assertSame($testTransformer, $newAliasTransformer);

        $transformerManager->addSharedInstance(
            identifier: CoreTrimTransformer::class,
            instance: null,
        );
        $newFqcnTransformer = $transformerManager->get(CoreTrimTransformer::class);
        $this->assertInstanceOf(CoreTrimTransformer::class, $newFqcnTransformer);
    }

    #[Test]
    public function testGet_NoSharedInstance_RegisteredNamespace_DefaultSortOrder(): void
    {
        $transformerManager = new TransformerManager(
            namespaces: [
                '\\Klevu\\Pipelines\\Test\\Fixture\\Transformer\\' => ObjectManagerInterface::DEFAULT_NAMESPACE_SORT_ORDER, // phpcs:ignore Generic.Files.LineLength.TooLong
            ],
        );

        $testTransformerAlias = $transformerManager->get('TestTransformer');
        $this->assertInstanceOf(TestTransformer::class, $testTransformerAlias);
        $testTransformerFqcn = $transformerManager->get(TestTransformer::class);
        $this->assertInstanceOf(TestTransformer::class, $testTransformerFqcn);
        $this->assertNotSame($testTransformerAlias, $testTransformerFqcn);

        $trimTransformerAlias = $transformerManager->get('Trim');
        $this->assertInstanceOf(CoreTrimTransformer::class, $trimTransformerAlias);
        $trimTransformerFqcnTest = $transformerManager->get(TestTrimTransformer::class);
        $this->assertInstanceOf(TestTrimTransformer::class, $trimTransformerFqcnTest);
        $trimTransformerFqcnCore = $transformerManager->get(CoreTrimTransformer::class);
        $this->assertInstanceOf(CoreTrimTransformer::class, $trimTransformerFqcnCore);

        $transformerManager->registerNamespace(
            namespace: '\\Klevu\\Pipelines\\Test\\Fixture\\Transformer\\',
            sortOrder: 0,
        );
        $newTrimTransformerAlias = $transformerManager->get('Trim');
        $this->assertSame($trimTransformerAlias, $newTrimTransformerAlias);

        $transformerManager->addSharedInstance(
            identifier: 'Trim',
            instance: null,
        );
        $resetTrimTransformerAlias = $transformerManager->get('Trim');
        $this->assertInstanceOf(TestTrimTransformer::class, $resetTrimTransformerAlias);

        $newTrimTransformerFqcnTest = $transformerManager->get(TestTrimTransformer::class);
        $this->assertSame($trimTransformerFqcnTest, $newTrimTransformerFqcnTest);
        $this->assertNotSame($trimTransformerFqcnTest, $resetTrimTransformerAlias);
    }

    #[Test]
    public function testGet_NoSharedInstance_RegisteredNamespace_PrioritySortOrder(): void
    {
        $transformerManager = new TransformerManager(
            namespaces: [
                '\\Klevu\\Pipelines\\Test\\Fixture\\Transformer\\' => 0,
            ],
        );

        $testTransformerAlias = $transformerManager->get('TestTransformer');
        $this->assertInstanceOf(TestTransformer::class, $testTransformerAlias);
        $testTransformerFqcn = $transformerManager->get(TestTransformer::class);
        $this->assertInstanceOf(TestTransformer::class, $testTransformerFqcn);
        $this->assertNotSame($testTransformerAlias, $testTransformerFqcn);

        $trimTransformerAlias = $transformerManager->get('Trim');
        $this->assertInstanceOf(TestTrimTransformer::class, $trimTransformerAlias);
        $trimTransformerFqcnTest = $transformerManager->get(TestTrimTransformer::class);
        $this->assertInstanceOf(TestTrimTransformer::class, $trimTransformerFqcnTest);
        $trimTransformerFqcnCore = $transformerManager->get(CoreTrimTransformer::class);
        $this->assertInstanceOf(CoreTrimTransformer::class, $trimTransformerFqcnCore);

        $transformerManager->registerNamespace('\\Klevu\\Pipelines\\Test\\Fixture\\Transformer\\');
        $newTrimTransformerAlias = $transformerManager->get('Trim');
        $this->assertSame($trimTransformerAlias, $newTrimTransformerAlias);

        $transformerManager->addSharedInstance(
            identifier: 'Trim',
            instance: null,
        );
        $resetTrimTransformerAlias = $transformerManager->get('Trim');
        $this->assertInstanceOf(CoreTrimTransformer::class, $resetTrimTransformerAlias);

        $newTrimTransformerFqcnCore = $transformerManager->get(CoreTrimTransformer::class);
        $this->assertSame($trimTransformerFqcnCore, $newTrimTransformerFqcnCore);
        $this->assertNotSame($trimTransformerFqcnCore, $resetTrimTransformerAlias);
    }

    #[Test]
    public function testGet_SharedInstance_RegisteredNamespace_DefaultSortOrder(): void
    {
        $testTrimTransformer = new TestTrimTransformer();
        $transformerManager = new TransformerManager(
            sharedInstances: [
                'Trim' => $testTrimTransformer,
            ],
            namespaces: [
                '\\Klevu\\Pipelines\\Test\\Fixture\\Transformer\\' => ObjectManagerInterface::DEFAULT_NAMESPACE_SORT_ORDER, // phpcs:ignore Generic.Files.LineLength.TooLong
            ],
        );

        $transformer = $transformerManager->get('Trim');
        $this->assertSame($testTrimTransformer, $transformer);

        $transformerManager->addSharedInstance(
            identifier: 'Trim',
            instance: null,
        );
        $newTransformer = $transformerManager->get('Trim');
        $this->assertInstanceOf(CoreTrimTransformer::class, $newTransformer);
    }

    #[Test]
    public function testGet_SharedInstance_RegisteredNamespace_PrioritySortOrder(): void
    {
        $testTrimTransformer = new TestTrimTransformer();
        $transformerManager = new TransformerManager(
            sharedInstances: [
                'Trim' => $testTrimTransformer,
            ],
            namespaces: [
                '\\Klevu\\Pipelines\\Test\\Fixture\\Transformer\\' => 0,
            ],
        );

        $transformer = $transformerManager->get('Trim');
        $this->assertSame($testTrimTransformer, $transformer);

        $transformerManager->addSharedInstance(
            identifier: 'Trim',
            instance: null,
        );
        $newTransformer = $transformerManager->get('Trim');
        $this->assertInstanceOf(TestTrimTransformer::class, $newTransformer);
        $this->assertNotSame($testTrimTransformer, $newTransformer);
    }

    #[Test]
    public function testGet_NotInstantiable(): void
    {
        $transformerManager = new TransformerManager();

        $this->expectException(ObjectInstantiationException::class);
        $transformerManager->get(AbstractConcatenate::class);
    }

    #[Test]
    public function testGet_NotTransformer(): void
    {
        $transformerManager = new TransformerManager();

        $this->expectException(InvalidClassException::class);
        $transformerManager->get(TestObject::class);
    }
}
