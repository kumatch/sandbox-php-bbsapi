<?php
namespace Kumatch\BBSAPI\Test\Spec;

use Kumatch\BBSAPI\Spec\TagsSpec;
use Kumatch\BBSAPI\Entity\Tag;
use Kumatch\BBSAPI\Value\Tags;

class TagsSpecTest extends \PHPUnit_Framework_TestCase
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
    public function valid()
    {
        $tags = new Tags([ "foo", "bar", "baz" ]);
        $result = $this->spec->validate($tags);

        $this->assertTrue($result->isValid());
        $this->assertCount(0, $result->getErrors());
    }

    /**
     * @test
     */
    public function invalid()
    {
        $tags = new Tags(["foo", "012345678901234567890", "bar"]);

        $result = $this->spec->validate($tags);
        $errors = $result->getErrors();

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $errors);
        $this->assertCount(1, $errors["tags"]);
    }
}