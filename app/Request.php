<?php
namespace Kumatch\BBSAPI\Application;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Kumatch\BBSAPI\Entity\User;

class Request extends SymfonyRequest
{
    /**
     * @var User
     */
    private $user;

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }
}