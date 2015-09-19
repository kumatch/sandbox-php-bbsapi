<?php
use Silex\Application;
use Silex\ServiceProviderInterface;
use Kumatch\BBSAPI\Utility\PasswordEncoder;

class BBSAPIServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app["bbsapi.utility.password_encoder"] = function (Application $app) {
            return new PasswordEncoder($app["salt"]);
        };
    }

    public function boot(Application $app)
    {
    }
}