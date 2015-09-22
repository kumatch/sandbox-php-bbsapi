<?php
namespace Kumatch\BBSAPI\Repository;

use Doctrine\ORM\EntityRepository;
use Kumatch\BBSAPI\Entity\Thread;

class ThreadRepository extends EntityRepository
{
    /**
     * @param Thread $thread
     */
    public function add(Thread $thread)
    {
        $this->getEntityManager()->persist($thread);
    }

    /**
     * @param Thread $thread
     */
    public function remove(Thread $thread)
    {
        $this->getEntityManager()->remove($thread);
    }
}