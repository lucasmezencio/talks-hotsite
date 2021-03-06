<?php

if (false === class_exists('Symfony\Component\ClassLoader\UniversalClassLoader', false)) {
    require_once __DIR__ . '/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';
}

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony' => __DIR__ . '/vendor',
    'Silex' => __DIR__ . '/src',
    'Doctrine\\Common' => __DIR__ . '/vendor/doctrine-common/lib',
    'Doctrine\\DBAL' => __DIR__ . '/vendor/doctrine-dbal/lib',
    'Monolog' => __DIR__ . '/vendor/monolog/src',
    'Redpanda\\Gravatar' => __DIR__ . '/vendor/gravatar/src',
));
$loader->registerPrefixes(array(
    'Pimple' => __DIR__ . '/vendor/pimple/lib',
    'Twig_' => __DIR__ . '/vendor/twig/lib',
));
$loader->register();
