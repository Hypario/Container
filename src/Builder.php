<?php

namespace Hypario;

use Hypario\Exceptions\NotFoundException;
use Hypario\Exceptions\TypeException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Builder
{
    /**
     * @var array
     */
    private $definitions = [];

    public function __construct()
    {
        include('functions.php');
    }

    /**
     * @param string $path the path to the definition.
     *
     * @throws NotFoundExceptionInterface file not found
     * @throws ContainerExceptionInterface Wrong definition
     *
     * @return void
     */
    public function addDefinitions(string $path): void
    {
        if (!file_exists($path)) {
            throw new NotFoundException("The definition file does not exist : $path");
        } else {
            $required = require_once($path);
            if (is_array($required)) {
                $this->definitions[] = $required;
            } else {
                throw new TypeException("The definition must return an array");
            }
        }
    }

    /**
     * Build the Container.
     *
     * @return ContainerInterface
     */
    public function build(): ContainerInterface
    {
        if (!empty($this->definitions)) {
            $definitions = array_merge($this->definitions[0]);
            if (count($this->definitions) > 1) {
                for ($i = 1; $i < count($this->definitions); $i++) {
                    $definitions = array_merge($definitions, $this->definitions[$i]);
                }
            }
            return new Container($definitions);
        }
        return new Container([]);
    }

}
