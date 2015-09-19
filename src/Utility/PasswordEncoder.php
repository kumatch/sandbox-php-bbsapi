<?php
namespace Kumatch\BBSAPI\Utility;

class PasswordEncoder
{
    /**
     * @var string
     */
    protected $salt;

    /**
     * @param string $salt
     */
    public function __construct($salt)
    {
        $this->salt = $salt;
    }

    /**
     * @param string $password
     * @return string
     */
    public function encode($password)
    {
        return hash("sha256", sprintf("%s%s", $this->salt, $password));
    }
}