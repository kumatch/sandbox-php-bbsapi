<?php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Annotations\AnnotationRegistry;

$root = __DIR__ . "/../..";
$loader = require $root . "/vendor/autoload.php";
$config = require $root . "/config/config.php";

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$isDevMode = true;
$proxyDir = null;
$cache = null;
$useSimpleAnnotationReader = false;
$ormConfig = Setup::createAnnotationMetadataConfiguration(
    array($root.'/src'), $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader
);

return EntityManager::create($config["doctrine"], $ormConfig);
