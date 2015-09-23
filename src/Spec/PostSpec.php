<?php
namespace Kumatch\BBSAPI\Spec;

use Kumatch\BBSAPI\Entity\Post;

class PostSpec extends SpecAbstract
{
    /**
     * @param Post $post
     * @return SpecResult
     */
    public function validate(Post $post)
    {
        $errors = $this->getErrors($post);

        if (!$errors) {
            return new SpecResult();
        } else {
            return new SpecResult(false, $errors);
        }
    }

    /**
     * @param Post $post
     * @return array
     */
    public function format(Post $post)
    {
        return [
            "id" => $post->getId(),
            "thread_id" => $post->getThread()->getId(),
            "content" => $post->getContent(),
            "created_at" => $post->getCreatedAt()->getTimestamp(),
        ];
    }
}