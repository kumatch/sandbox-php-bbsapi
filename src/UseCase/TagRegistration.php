<?php
namespace Kumatch\BBSAPI\UseCase;

use Doctrine\ORM\EntityManager;
use Kumatch\BBSAPI\Entity\Tag;
use Kumatch\BBSAPI\Entity\EntityConstant;
use Kumatch\BBSAPI\Repository\TagRepository;

class TagRegistration
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->tagRepository = $entityManager->getRepository(EntityConstant::TAG);
    }


    /**
     * @param Tag $tag
     * @return Tag
     */
    public function register(Tag $tag)
    {
        $existsTag = $this->findByName($tag->getName());
        if ($existsTag) {
            return $existsTag;
        }

        $this->tagRepository->add($tag);
        $this->entityManager->flush();

        return $tag;
    }

    /**
     * @param string $name
     * @return Tag|null
     */
    public function findByName($name)
    {
        $criteria = array("name" => $name);

        return $this->tagRepository->findOneBy($criteria);
    }
}