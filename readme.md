[![Build Status](https://travis-ci.org/Hypario/Container.svg?branch=master)](https://travis-ci.org/Hypario/Container)
[![Coverage Status](https://coveralls.io/repos/github/Hypario/Container/badge.svg?branch=master)](https://coveralls.io/github/Hypario/Container?branch=master)

# What is this ?

This library is a Dependency Injection Container written in PHP.
I created this library to learn and using PHP-DI as an exemple

# How to use it ?

First you have to create a container builder that will build your container

```php
$builder = new Hypario\Builder();
$container = $builder->build();
```
then you can use the container to instantiate a class.

for exemple :
```php
class A {
    public function hello() {
        return "Hello World !";
    }
}

$builder = new Hypario\Builder();
$container = $builder->build();

$class = $container->get(A::class);

echo $class->hello(); // output : "Hello World !"
```
the container will instantiate the class

## But what if i have a constructor ?

Like you would do normally, you sometimes need a constructor for you class, there are different possibilities

## With a default value

You sometimes need a class with a constructor which have default values, no problem the class will be instantiated with the default values like so :

```php
class A {
    
    private $name;

    public function __construct(string $name = "John") {
        $this->name = $name;
    }

    public function hello() {
        return "Hello $this->name !";
    }
}

$builder = new Hypario\Builder();
$container = $builder->build();

$class = $container->get(A::class);
echo $class->hello(); // output : "Hello John !"
```

## With a class

Sometimes your class need another class to work, no worry, this container can instantiate the class needed (if the constructor use default values OR a class too !)

```php
class Address {

    public $address;

    public function __construct() {
        $this->address = 'France, Paris 6e'
    }
}

class Person {

    public $name;
    
    public $address;

    public function __construct(Address $address, string $name = 'John') {
        $this->name = $nom;
        $this->address = $address;
    }

    public function hello() {
        return "Hello $this->name, you live in $this->address";
    }

}

$builder = new Hypario\Builder();
$container = $builder->build();

$class = $container->get(Person::class);
echo $class->hello(); // output : "Hello John, you live in France, Paris 6e"
```

# Definitions

The definitions are an array where you define to the container how to instantiate a class, or what function you have to call for a specific word and so, define how your class should be instantiate.

You maybe thought it was strange to use a container builder instead of directly call the container right ? well in fact, before you build the container, you can define some definitions to the container builder like so :

```php
$builder = new Hypario\Builder();
$builder->addDefinitions(['foo' => 'bar']);
$container = $builder->build();

echo $container->get('foo'); // output : "bar"
```
here I used the definition like a simple array, but you can use those to instantiate a class where you need an Interface

```php
interface testInterface{

    public function hello();

}

class test implements testInterface {

    public $string = "Hello I am the test class";

    public function hello(): string {
        return $this->string;
    }
}

class A {

    public $test;

    public function __construct(testInterface $test){
        $this->test = $test;
    }
}

$builder = new Hypario\Builder();
$builder->addDefinitions([
    testInterface::class => test::class
]);
$container = $builder->build();
$class = $container->get(A::class); 
echo $class->test->hello(); // output : "Hello I am the test class
```
As we can't get an instance of an interface, as is defined in the definitions, the container will instantiate the test class which implements the testInterface
