<?php
use Silex\Application;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Kumatch\Silex\JsonBodyProvider;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

$loader = require_once __DIR__ . '/../vendor/autoload.php';
$config = require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/provider/DoctrineORMServiceProvider.php";
require_once __DIR__ . "/provider/BBSAPIServiceProvider.php";

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$app = new Application();
$app->register(new JsonBodyProvider());
$app->register(new DoctrineORMServiceProvider(), [
    "doctrine_orm.config" => $config["doctrine"]
]);
$app->register(new BBSAPIServiceProvider(), [
    "salt" => $config["salt"]
]);

$app->post("/user/register", function (Application $app, Request $req) {
    /** @var \Kumatch\BBSAPI\Spec\UserSpec $spec */
    $spec = $app["bbsapi.spec.user_spec"];
    /** @var \Kumatch\BBSAPI\UseCase\UserRegistration $service */
    $service = $app["bbsapi.user.registration"];

    $email = $req->request->get("email");
    $username = $req->request->get("username");
    $password = $req->request->get("password");

    $user = new \Kumatch\BBSAPI\Entity\User();
    $user->setEmail($email)
        ->setUsername($username)
        ->setPassword($password);

    $result = $spec->validate($user);
    if (!$result->isValid()) {
        return $app->json([ "errors" => $result->getErrors() ], 400);
    }

    try {
        $user = $service->invoke($user);
    } catch (UniqueConstraintViolationException $e) {
        return $app->json([ "errors" => [
            "user" => [ sprintf("A username [%s] is already exists.", $user->getUsername()) ]
        ]]);
    }

    return $app->json([
        "email" => $user->getEmail(),
        "username" => $user->getUsername()
    ]);
});

$app->error(function (\Exception $e, $code) use ($app) {
    switch ($code) {
        case 404:
            return $app->json([ "message" => "not found." ], 404);
        default:
            return $app->json([ "message" => "sorry server error" ] , 500);
    }
});

$app->run();
