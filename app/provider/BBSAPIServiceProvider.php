<?php
use Silex\Application;
use Silex\ServiceProviderInterface;
use Kumatch\BBSAPI\Utility\PasswordEncoder;
use Kumatch\BBSAPI\Utility\TokenGenerator;

class BBSAPIServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app["bbsapi.utility.password_encoder"] = function (Application $app) {
            return new PasswordEncoder($app["salt"]);
        };

        $app["bbsapi.utility.token_generator"] = function () {
            return new TokenGenerator();
        };
    }

    public function boot(Application $app)
    {
    }
}