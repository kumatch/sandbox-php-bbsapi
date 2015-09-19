<?php
use Silex\Application;
use Silex\ServiceProviderInterface;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class DoctrineORMServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app["entity_manager"] = $app->share(function (Application $app) {
            $isDevMode = true;
            $proxyDir = null;
            $cache = null;
            $useSimpleAnnotationReader = false;
            $entityManagerConfig = Setup::createAnnotationMetadataConfiguration(
                array(__DIR__ . '/../src/Entity'), $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader
            );

            return EntityManager::create($app["doctrine_orm.config"], $entityManagerConfig);
        });
    }

    public function boot(Application $app)
    {
    }
}