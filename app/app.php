<?php
use Silex\Application;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Kumatch\Silex\JsonBodyProvider;
use Kumatch\BBSAPI\Application\Request;
use Kumatch\BBSAPI\Application\Provider\DoctrineORMServiceProvider;
use Kumatch\BBSAPI\Application\Provider\BBSAPIServiceProvider;
use Kumatch\BBSAPI\Application\Provider\BBSAPIUserServiceProvider;
use Kumatch\BBSAPI\Application\Provider\BBSAPIThreadServiceProvider;

$loader = require_once __DIR__ . '/../vendor/autoload.php';
$config = require_once __DIR__ . "/../config/config.php";

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$req = Request::createFromGlobals();

$app = new Application();
$app->register(new JsonBodyProvider());
$app->register(new DoctrineORMServiceProvider(), [
    "doctrine_orm.config" => $config["doctrine"]
]);
$app->register(new BBSAPIServiceProvider(), [
    "salt" => $config["salt"]
]);
$app->register(new BBSAPIUserServiceProvider());
$app->register(new BBSAPIThreadServiceProvider());

$app->get("/", function (Application $app) {
    $composerJSON = json_decode(file_get_contents(__DIR__ . "/../composer.json"), true);
    return $app->json([
        "version" => $composerJSON["version"]
    ]);
});

$app->error(function (\Exception $e, $code) use ($app) {
    switch ($code) {
        case 404:
            return $app->json([ "message" => "not found." ], 404);
        case 405:
            return $app->json([ "message" => $e->getMessage() ], 405);
        default:
            //error_log($e->getMessage());
            return $app->json([ "message" => "sorry server error" ] , 500);
    }
});

$app->run($req);
