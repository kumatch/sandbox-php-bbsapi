<?php
namespace Kumatch\BBSAPI\Spec;

use Kumatch\BBSAPI\Entity\Thread;
use Kumatch\BBSAPI\Entity\Tag;

class ThreadSpec extends SpecAbstract
{
    /**
     * @param Thread $thread
     * @return SpecResult
     */
    public function validate(Thread $thread)
    {
        $errors = $this->getErrors($thread);

        if (!$errors) {
            return new SpecResult();
        } else {
            return new SpecResult(false, $errors);
        }
    }

    /**
     * @param Thread $thread
     * @return array
     */
    public function format(Thread $thread)
    {
        return [
            "id" => $thread->getId(),
            "title" => $thread->getTitle(),
            "created_at" => $thread->getCreatedAt()->getTimestamp(),
            "tags" => array_map(function ($tag) {
                /** @var Tag $tag */
                return $tag->getName();
            }, $thread->getTags())
        ];
    }
}