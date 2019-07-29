<?php

namespace Hypario;

use Psr\Container\ContainerInterface;

interface DefinitionsInterface
{

    /**
     * This function must implement what your definition will do
     * @param ContainerInterface $container
     * @param array $definitions
     * @param $id
     */
    public function handle(ContainerInterface $container, array $definitions, $id);
}
