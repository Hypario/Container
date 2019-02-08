<?php
require 'vendor/autoload.php';
$builder = new \Hypario\Builder();

$builder->addDefinitions('config.php');
$container = $builder->build();
