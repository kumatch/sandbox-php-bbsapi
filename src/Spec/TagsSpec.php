<?php
namespace Kumatch\BBSAPI\Spec;

use Kumatch\BBSAPI\Value\Tags;
use Kumatch\BBSAPI\Entity\Tag;

class TagsSpec extends SpecAbstract
{
    /**
     * @param Tags $tags
     * @return SpecResult
     */
    public function validate(Tags $tags)
    {
        $spec = new TagSpec();

        foreach ($tags as $tag) {
            /** @var Tag $tag */
            $result = $spec->validate($tag);
            if (!$result->isValid()) {
                $errors = $result->getErrors();
                return new SpecResult(false, [ "tags" => $errors["name"] ]);
            }
        }

        return new SpecResult();
    }
}