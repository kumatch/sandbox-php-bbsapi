<?php
namespace Kumatch\BBSAPI\Value;

use Doctrine\Common\Collections\ArrayCollection;
use Kumatch\BBSAPI\Entity\Tag;

class Tags extends ArrayCollection
{
    /**
     * @param string[]|null $tagNames
     */
    public function __construct(array $tagNames = null)
    {
        if (is_null($tagNames)) {
            parent::__construct();
            return;
        }

        $tags = [];
        foreach ($tagNames as $tagName) {
            if (is_string($tagName) && $tagName !== "") {
                $tag = new Tag();
                $tag->setName(trim($tagName));
                array_push($tags, $tag);
            }
        }

        parent::__construct($tags);
    }

    /**
     * @return string[]
     */
    public function getNames()
    {
        return array_map(function ($tag) {
            /** @var Tag $tag */
            return $tag->getName();
        }, $this->toArray());
    }
}