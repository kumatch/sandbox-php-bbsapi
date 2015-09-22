<?php
namespace Kumatch\BBSAPI\Spec;

use Kumatch\BBSAPI\Entity\Tag;

class TagSpec extends SpecAbstract
{
    /**
     * @param Tag $tag
     * @return SpecResult
     */
    public function validate(Tag $tag)
    {
        $errors = $this->getErrors($tag);

        if (!$errors) {
            return new SpecResult();
        } else {
            return new SpecResult(false, $errors);
        }
    }
}