<?php
use Silex\Application;
use Silex\ServiceProviderInterface;
use Kumatch\BBSAPI\UseCase\UserRegistration;
use Kumatch\BBSAPI\Spec\UserSpec;
use Kumatch\BBSAPI\Utility\PasswordEncoder;

class BBSAPIServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app["bbsapi.utility.password_encoder"] = function (Application $app) {
            return new PasswordEncoder($app["salt"]);
        };

        $app["bbsapi.user.registration"] = function (Application $app) {
            return new UserRegistration($app["entity_manager"], $app["bbsapi.utility.password_encoder"]);
        };

        $app["bbsapi.spec.user_spec"] = function () {
            return new UserSpec();
        };
    }

    public function boot(Application $app)
    {
    }
}