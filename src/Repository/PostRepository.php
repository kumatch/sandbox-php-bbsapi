<?php
namespace Kumatch\BBSAPI\Repository;

use Doctrine\ORM\EntityRepository;
use Kumatch\BBSAPI\Entity\Post;

class PostRepository extends EntityRepository
{
    /**
     * @param Post $post
     */
    public function add(Post $post)
    {
        $this->getEntityManager()->persist($post);
    }

    /**
     * @param Post $post
     */
    public function remove(Post $post)
    {
        $this->getEntityManager()->remove($post);
    }
}