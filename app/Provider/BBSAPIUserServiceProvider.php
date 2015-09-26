<?php
namespace Kumatch\BBSAPI\Application\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Kumatch\BBSAPI\Application\Request;
use Kumatch\BBSAPI\UseCase\UserRegistration;
use Kumatch\BBSAPI\UseCase\UserAuthentication;
use Kumatch\BBSAPI\UseCase\UserTokenAuthorization;
use Kumatch\BBSAPI\Entity\User;
use Kumatch\BBSAPI\Spec\UserSpec;

class BBSAPIUserServiceProvider implements ServiceProviderInterface
{
    const HEADER_AUTHORIZATION_USER_ID = 'X-BBSAPI-USER-ID';
    const HEADER_AUTHORIZATION_TOKEN = 'X-BBSAPI-TOKEN';

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

        $app["bbsapi.user.token_authorization"] = function (Application $app) {
            return new UserTokenAuthorization($app["entity_manager"]);
        };

        $app["bbsapi.spec.user_spec"] = function () {
            return new UserSpec();
        };

        $app->before(function (Request $req) use ($app) {
            $userId = $req->headers->get(self::HEADER_AUTHORIZATION_USER_ID);
            $tokenString = $req->headers->get(self::HEADER_AUTHORIZATION_TOKEN);
            if (!$userId || !$tokenString) {
                return;
            }

            /** @var UserTokenAuthorization $service */
            $service = $app["bbsapi.user.token_authorization"];
            $user = $service->invoke($userId, $tokenString);
            if (!$user) {
                return;
            }

            $req->setUser($user);
        });

        $app->post("/user/register", function (Application $app, Request $req) {
            /** @var UserSpec $spec */
            $spec = $app["bbsapi.spec.user_spec"];
            /** @var UserRegistration $service */
            $service = $app["bbsapi.user.registration"];

            $email = $req->request->get("email");
            $username = $req->request->get("username");
            $password = $req->request->get("password");

            $user = new User();
            $user->setEmail($email)
                ->setUsername($username)
                ->setPassword($password);

            $result = $spec->validate($user);
            if (!$result->isValid()) {
                return $app->json([ "errors" => $result->getErrors() ], 400);
            }

            $alreadyExistsErrors = [
                "user" => [ sprintf("A username [%s] is already exists.", $user->getUsername()) ]
            ];

            if ($service->findByUsername($user->getUsername())) {
                return $app->json([ "errors" => $alreadyExistsErrors ]);
            }

            try {
                $user = $service->invoke($user);
            } catch (UniqueConstraintViolationException $e) {
                return $app->json([ "errors" => $alreadyExistsErrors ]);
            }

            return $app->json($spec->format($user), 201);
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
                "id" => $accessToken->getUser()->getId(),
                "token" => $accessToken->getToken(),
                "period" => $accessToken->getPeriod(),
            ]);
        });

        $app->get("/users/{username}", function (Application $app, $username) {
            /** @var UserSpec $spec */
            $spec = $app["bbsapi.spec.user_spec"];
            /** @var UserRegistration $service */
            $service = $app["bbsapi.user.registration"];

            $user = $service->findByUsername($username);
            if (!$user) {
                return $app->json(null, 404);
            }

            return $app->json($spec->format($user));
        })->assert('username', '^\w+$');
    }

    public function boot(Application $app)
    {
    }
}