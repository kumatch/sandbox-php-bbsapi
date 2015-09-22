<?php
namespace Kumatch\BBSAPI\Repository;

use Doctrine\ORM\EntityRepository;
use Kumatch\BBSAPI\Entity\Tag;

class TagRepository extends EntityRepository
{
    /**
     * @param Tag $tag
     */
    public function add(Tag $tag)
    {
        $this->getEntityManager()->persist($tag);
    }

    /**
     * @param Tag $tag
     */
    public function remove(Tag $tag)
    {
        $this->getEntityManager()->remove($tag);
    }
}