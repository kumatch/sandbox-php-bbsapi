<?php
namespace Kumatch\BBSAPI\Test\Spec;

use Kumatch\BBSAPI\Spec\TagSpec;
use Kumatch\BBSAPI\Entity\Tag;

class TagSpecTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TagSpec
     */
    private $spec;

    protected function setUp()
    {
        parent::setUp();

        $this->spec = new TagSpec();
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
     * @dataProvider provideValidTagName
     * @param string $name
     */
    public function valid($name)
    {
        $tag = new Tag();
        $tag->setName($name);

        $result = $this->spec->validate($tag);

        $this->assertTrue($result->isValid());
        $this->assertCount(0, $result->getErrors());
    }

    /**
     * @return array
     */
    public function provideInvalidTagName()
    {
        return [
            [null],
            [""],
            ["    \t"],
            ["012345678901234567890"], // over 20 chars
            ["０１２３４５６７８９０１２３４５６７８９０"] // over 20 chars
        ];
    }

    /**
     * @test
     * @dataProvider provideInvalidTagName
     * @param string $name
     * @param int $errorSize
     */
    public function invalidTagName($name, $errorSize = 1)
    {
        $tag = new Tag();
        $tag->setName($name);

        $result = $this->spec->validate($tag);
        $errors = $result->getErrors();

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $errors);
        $this->assertCount($errorSize, $errors["name"]);
    }
}