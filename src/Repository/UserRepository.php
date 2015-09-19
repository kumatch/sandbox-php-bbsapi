<?php
namespace Kumatch\BBSAPI\Repository;

use Doctrine\ORM\EntityRepository;
use Kumatch\BBSAPI\Entity\User;

class UserRepository extends EntityRepository
{
    /**
     * @param User $user
     */
    public function add(User $user)
    {
        $this->getEntityManager()->persist($user);
    }

    /**
     * @param User $user
     */
    public function remove(User $user)
    {
        $this->getEntityManager()->remove($user);
    }
}