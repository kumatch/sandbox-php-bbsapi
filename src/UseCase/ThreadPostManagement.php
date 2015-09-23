<?php
namespace Kumatch\BBSAPI\UseCase;

use Doctrine\ORM\EntityManager;
use Kumatch\BBSAPI\Entity\Thread;
use Kumatch\BBSAPI\Entity\Post;
use Kumatch\BBSAPI\Entity\EntityConstant;
use Kumatch\BBSAPI\Repository\ThreadRepository;

class ThreadPostManagement
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ThreadRepository
     */
    private $threadRepository;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->threadRepository = $entityManager->getRepository(EntityConstant::THREAD);
    }


    /**
     * @param Thread $thread
     * @param Post $post
     * @return Post
     */
    public function register(Thread $thread, Post $post)
    {
        $thread->addPost($post)
            ->setLastPostedAt($this->currentDate());
        $post->setThread($thread);
        $this->threadRepository->add($thread);

        $this->entityManager->flush();

        return $post;
    }

    /**
     * @return \DateTime
     */
    protected function currentDate()
    {
        return new \DateTime();
    }
}