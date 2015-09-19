<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;

$em = require_once './bootstrap.php';

return ConsoleRunner::createHelperSet($em);