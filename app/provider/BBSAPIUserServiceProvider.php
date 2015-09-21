<?php
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Kumatch\BBSAPI\UseCase\UserRegistration;
use Kumatch\BBSAPI\UseCase\UserAuthentication;
use Kumatch\BBSAPI\Spec\UserSpec;

class BBSAPIUserServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app["bbsapi.user.registration"] = function (Application $app) {
            return new UserRegistration($app["entity_manager"], $app["bbsapi.utility.password_encoder"]);
        };

        $app["bbsapi.user.authentication"] = function (Application $app) {
            return new UserAuthentication(
                $app["entity_manager"], $app["bbsapi.utility.password_encoder"], $app["bbsapi.utility.token_generator"]
            );
        };


        $app["bbsapi.spec.user_spec"] = function () {
            return new UserSpec();
        };

        $app->post("/user/register", function (Application $app, Request $req) {
            /** @var UserSpec $spec */
            $spec = $app["bbsapi.spec.user_spec"];
            /** @var UserRegistration $service */
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


        $app->post("/user/authorize", function (Application $app, Request $req) {
            /** @var UserAuthentication $service */
            $service = $app["bbsapi.user.authentication"];

            $username = $req->request->get("username");
            $password = $req->request->get("password");

            $accessToken = $service->invoke($username, $password);
            if (!$accessToken) {
                return $app->json(null, 401);
            }

            return $app->json([
                "token" => $accessToken->getToken(),
                "period" => $accessToken->getPeriod(),
            ]);
        });
    }

    public function boot(Application $app)
    {
    }
}