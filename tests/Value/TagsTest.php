<?php
namespace Kumatch\BBSAPI\Test\Value;

use Kumatch\BBSAPI\Spec\TagsSpec;
use Kumatch\BBSAPI\Entity\Tag;
use Kumatch\BBSAPI\Value\Tags;

class TagsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TagsSpec
     */
    private $spec;

    protected function setUp()
    {
        parent::setUp();

        $this->spec = new TagsSpec();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }


    /**
     * @return array
     */
    public function provideValidTagName()
    {
        return [
            ["foo"],
            ["日本語名"],
            [".=<>$%&()^?/"],
            ["01234567890123456789"],
            ["０１２３４５６７８９０１２３４５６７８９"]
        ];
    }

    /**
     * @test
     */
    public function create()
    {
        $tagNames = [ "foo", "日本語", "<>$%&()" ];
        $tags = new Tags($tagNames);

        $this->assertCount(3, $tags);
        $this->assertEquals($tagNames[0], $tags[0]->getName());
        $this->assertEquals($tagNames[1], $tags[1]->getName());
        $this->assertEquals($tagNames[2], $tags[2]->getName());
        $this->assertEquals($tagNames, $tags->getNames());
    }

    /**
     * @test
     */
    public function filterNotString()
    {
        $tagNames = [ "foo", null, "", 123, [], true, (object)[], function () {} ];
        $tags = new Tags($tagNames);

        $this->assertCount(1, $tags);
        $this->assertEquals($tagNames[0], $tags[0]->getName());
    }
}