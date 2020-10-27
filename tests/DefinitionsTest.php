<?php

namespace Test;

use Hypario\Builder;
use Hypario\Exceptions\ContainerException;
use function Hypario\factory;
use function Hypario\get;
use function Hypario\object;
use PHPUnit\Framework\TestCase;
use Test\helpers\TestClass;
use Test\helpers\TestClass2;
use Test\helpers\TestClassParameters;
use Test\helpers\TestFactory;
use Test\helpers\TestFactory2;

class DefinitionsTest extends TestCase
{
    public function testFactoryDefinition()
    {
        $builder = new Builder();
        $builder->addDefinitions([
            'a' => factory(function () {
                return 'Hello World !';
            }),
            'b' => factory(TestFactory::class),
            'c' => factory(TestFactory2::class)
        ]);

        $container = $builder->build();

        $this->assertSame('Hello World !', $container->get('a'));
        $this->assertSame('Hello again !', $container->get('b'));

        $this->expectException(ContainerException::class);
        $container->get('c');
    }

    public function testObjectDefinition()
    {
        $builder = new Builder();
        $builder->addDefinitions([
            'Test' => object(TestClassParameters::class) // the id Test returns an instance of TestClassParameters
                ->constructor(get(TestClass::class), get(TestClass2::class), 2), // how to construct it

            // another way to construct TestClassParameters
            TestClassParameters::class => object()
                ->constructor(get(TestClass::class), get(TestClass2::class), 2)
        ]);
        $container = $builder->build();

        $this->assertSame(2, $container->get('Test')->randomParameter);
        $this->assertSame(2, $container->get(TestClassParameters::class)->randomParameter);
    }
}
