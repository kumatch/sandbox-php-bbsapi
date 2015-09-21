<?php
namespace Kumatch\BBSAPI\Utility;

use Rych\Random\Random;

class TokenGenerator
{
    /**
     * @return string
     */
    public function generate()
    {
        $random = new Random();

        return $random->getRandomString(40);
    }
}