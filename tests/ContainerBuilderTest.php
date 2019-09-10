<?php

namespace Test;

use Hypario\Builder;
use Hypario\Container;
use Hypario\Exceptions\NotFoundException;
use function Hypario\factory;
use function Hypario\get;
use function Hypario\object;
use PHPUnit\Framework\TestCase;

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
        $builder->addDefinitions('tests/configTest.php');

        // test if the definition is added correctly
        $container = $builder->build();

        $this->assertTrue($container->has('foo'));
    }

    public function testAddMultipleDefinitionsFromFileWithoutDeletingData()
    {
        // create a builder and add definitions
        $builder = new Builder();
        $builder->addDefinitions('tests/configTest.php');
        $builder->addDefinitions('tests/configTest2.php');

        // test if the definitions are added correctly
        $container = $builder->build();

        $this->assertTrue($container->has('foo'));
        $this->assertTrue($container->has('foofoo'));
    }

    public function testAddMultipleDefinitionsFromFileDeletingData()
    {
        // create a builder and add definitions
        $builder = new Builder();
        $builder->addDefinitions('tests/configTest.php');
        $builder->addDefinitions('tests/configTest3.php');

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

    public function testContainerFactory()
    {

        $builder = new Builder();
        $builder->addDefinitions([
            'a' => factory(function () {
                return "Hello World !";
            }),
            'b' => factory(TestFactory::class)
        ]);

        $container = $builder->build();

        $this->assertSame('Hello World !', $container->get('a'));
        $this->assertSame('Hello again !', $container->get('b'));
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

    public function testContainerGetClassWithClassParameters()
    {
        $builder = new Builder();
        $container = $builder->build();

        $this->assertInstanceOf(TestClassParameters::class, $container->get(TestClassParameters::class));
    }

    public function testObjectDefinition()
    {
        $builder = new Builder();
        $builder->addDefinitions([
            'Test' => object(TestClassParameters::class)
                ->constructor(get(TestClass::class), get(TestClass2::class), 2)
        ]);
        $container = $builder->build();

        $this->assertEquals(2, $container->get('Test')->randomParameter);
    }
}
