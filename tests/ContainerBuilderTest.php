<?php

namespace Test;

use Hypario\Builder;
use Hypario\Container;
use Hypario\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;
use Test\helpers\TestClass;
use Test\helpers\TestClass2;
use Test\helpers\TestClassImplementsInterface;
use Test\helpers\TestClassParameters;
use Test\helpers\TestClassParameters2;
use Test\helpers\TestClassParameters3;
use Test\helpers\TestInterface;

class ContainerBuilderTest extends TestCase
{
    public function testBuildMethod()
    {
        $builder = new Builder();
        $this->assertInstanceOf(Container::class, $builder->build());
    }

    public function testAddDefinitionsMethodFromArray()
    {
        // create a builder
        $builder = new Builder();
        $builder->addDefinitions(['foo' => 'bar']);

        // test if the definition is added correctly
        $container = $builder->build();

        $this->assertTrue($container->has('foo'));
    }

    public function testAddMultipleDefinitionsFromArrayWithoutDeletingData()
    {
        // create a builder and add definitions
        $builder = new Builder();
        $builder->addDefinitions(['foo' => 'bar']);
        $builder->addDefinitions(['foofoo' => 'barbar']);

        // test if the definitions are added correctly
        $container = $builder->build();

        $this->assertTrue($container->has('foo'));
        $this->assertTrue($container->has('foofoo'));
    }

    public function testAddMultipleDefinitionsFromArrayDeletingData()
    {
        // create a builder and add definitions
        $builder = new Builder();
        $builder->addDefinitions(['foo' => 'bar']);
        $builder->addDefinitions(['foo' => 'barbar']);

        // test if the definitions are added correctly
        $container = $builder->build();

        $this->assertSame('barbar', $container->get('foo'));
    }

    public function testAddDefinitionsMethodFromFile()
    {
        // create a builder
        $builder = new Builder();
        $builder->addDefinitions('tests/helpers/configs/configTest.php');

        // test if the definition is added correctly
        $container = $builder->build();

        $this->assertTrue($container->has('foo'));
    }

    public function testAddMultipleDefinitionsFromFileWithoutDeletingData()
    {
        // create a builder and add definitions
        $builder = new Builder();
        $builder->addDefinitions('tests/helpers/configs/configTest.php');
        $builder->addDefinitions('tests/helpers/configs/configTest2.php');

        // test if the definitions are added correctly
        $container = $builder->build();

        $this->assertTrue($container->has('foo'));
        $this->assertTrue($container->has('foofoo'));
    }

    public function testAddMultipleDefinitionsFromFileDeletingData()
    {
        // create a builder and add definitions
        $builder = new Builder();
        $builder->addDefinitions('tests/helpers/configs/configTest.php');
        $builder->addDefinitions('tests/helpers/configs/configTest3.php');

        // test if the definitions are added correctly
        $container = $builder->build();

        $this->assertSame('barbar', $container->get('foo'));
    }

    public function testContainerGetMethod()
    {
        $builder = new Builder();
        $builder->addDefinitions(['foo' => 'bar']);

        $container = $builder->build();
        $this->assertSame('bar', $container->get('foo'));
    }

    public function testIfHasInterface()
    {
        $builder = new Builder();
        $container = $builder->build();

        $this->assertFalse($container->has(TestInterface::class));
    }

    public function testAutowireInterface()
    {
        $builder = new Builder();
        $builder->addDefinitions([
            TestInterface::class => TestClassImplementsInterface::class
        ]);

        $container = $builder->build();
        $this->assertInstanceOf(TestClassImplementsInterface::class, $container->get(TestInterface::class));
    }

    public function testAutowireInterfaceWithDefaultParameter()
    {
        $builder = new Builder();
        $container = $builder->build();

        $this->assertInstanceOf(
            TestClassParameters2::class,
            $container->get(TestClassParameters2::class)
        );
    }

    public function testContainerGetMethodFail()
    {
        $builder = new Builder();
        $container = $builder->build();

        $this->expectException(NotFoundException::class);
        $container->get('azeaze');
    }

    public function testContainerSameInstanceForEveryGet()
    {
        $builder = new Builder();
        $container = $builder->build();

        $call1 = $container->get(TestClass::class);
        $call2 = $container->get(TestClass::class);

        $this->assertSame($call1->id, $call2->id);
    }

    public function testContainerGetCallable()
    {
        $builder = new Builder();
        $builder->addDefinitions(['callable' => function () {
            return 'foo';
        }]);
        $container = $builder->build();

        $this->assertSame('foo', $container->get('callable'));
    }

    public function testContainerGetClassWithoutConstructor()
    {
        $builder = new Builder();
        $container = $builder->build();

        $this->assertInstanceOf(TestClass2::class, $container->get(TestClass2::class));
    }

    public function testContainerGetInstantiatedClass()
    {
        $builder = new Builder();
        $builder->addDefinitions([
            'instance' => new TestClass()
        ]);

        $container = $builder->build();
        $this->assertInstanceOf(TestClass::class, $container->get('instance'));
    }

    public function testContainerGetClassWithClassParameters()
    {
        $builder = new Builder();
        $container = $builder->build();

        $this->assertInstanceOf(TestClassParameters::class, $container->get(TestClassParameters::class));
    }

    public function testContainerGetClassWithClassReferenceParameters()
    {
        $builder = new Builder();
        $container = $builder->build();

        $this->assertInstanceOf(TestClassParameters3::class, $container->get(TestClassParameters3::class));
    }
}
