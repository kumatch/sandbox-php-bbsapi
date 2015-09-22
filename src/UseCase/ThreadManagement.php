<?php
namespace Kumatch\BBSAPI\UseCase;

use Doctrine\ORM\EntityManager;
use Kumatch\BBSAPI\Entity\User;
use Kumatch\BBSAPI\Entity\Thread;
use Kumatch\BBSAPI\Entity\EntityConstant;
use Kumatch\BBSAPI\Repository\ThreadRepository;
use Kumatch\BBSAPI\Value\Tags;

class ThreadManagement
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
     * @param User $user
     * @return Thread
     */
    public function create(Thread $thread, User $user)
    {
        $thread->setUser($user)
            ->setLastPostedAt(null);

        $this->threadRepository->add($thread);
        $this->entityManager->flush();

        return $thread;
    }

    /**
     * @param Thread $thread
     * @param User $user
     * @return bool
     */
    public function remove(Thread $thread, User $user)
    {
        $owner = $thread->getUser();
        if (!$owner || $owner->getId() != $user->getId()) {
            return false;
        }

        $this->threadRepository->remove($thread);
        $this->entityManager->flush();

        return true;
    }

    /**
     * @param int $threadId
     * @return Thread|null
     */
    public function findOne($threadId)
    {
        return $this->threadRepository->find($threadId);
    }

    /**
     * @param Tags $tags
     * @return array
     */
    public function findByTags(Tags $tags)
    {
        $qb = $this->threadRepository->createQueryBuilder("thread");
        $query = $qb->select()
            ->innerJoin('thread.tags', 'tag', 'WITH', 'tag.name IN (:tag_names)')
            ->setParameter("tag_names", $tags->getNames())
            ->getQuery();

        return $query->getResult();
    }
}