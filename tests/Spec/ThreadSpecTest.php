<?php
namespace Kumatch\BBSAPI\Test\Spec;

use Kumatch\BBSAPI\Spec\ThreadSpec;
use Kumatch\BBSAPI\Entity\Thread;
use Kumatch\BBSAPI\Entity\Tag;

class ThreadSpecTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ThreadSpec
     */
    private $spec;

    protected function setUp()
    {
        parent::setUp();

        $this->spec = new ThreadSpec();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }


    /**
     * @return array
     */
    public function provideValidTitle()
    {
        return [
            ["foo"],
            ["日本語タイトル名"],
            ["includes marks .=<>$%&()^?/"],
            ["0123456789012345678901234567890123456789"],
            ["０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９"]
        ];
    }

    /**
     * @test
     * @dataProvider provideValidTitle
     * @param string $title
     */
    public function valid($title)
    {
        $thread = new Thread();
        $thread->setTitle($title);

        $result = $this->spec->validate($thread);

        $this->assertTrue($result->isValid());
        $this->assertCount(0, $result->getErrors());
    }

    /**
     * @return array
     */
    public function provideInvalidTitle()
    {
        return [
            [null],
            [""],
            ["    \t"],
            ["01234567890123456789012345678901234567890"], // over 40 chars
            ["０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０"] // over 40 chars
        ];
    }

    /**
     * @test
     * @dataProvider provideInvalidTitle
     * @param string $title
     * @param int $errorSize
     */
    public function invalidTitle($title, $errorSize = 1)
    {
        $thread = new Thread();
        $thread->setTitle($title);

        $result = $this->spec->validate($thread);
        $errors = $result->getErrors();

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $errors);
        $this->assertCount($errorSize, $errors["title"]);
    }

    /**
     * @test
     */
    public function format()
    {
        $id = 42;
        $title = "foobarbaz";
        $createdAtUnixTime = 1234567890;
        $lastPostedAtUnixTime = 1333333333;
        $tags = ["foo", "bar"];

        $tag1 = new Tag();
        $tag1->setName($tags[0]);
        $tag2 = new Tag();
        $tag2->setName($tags[1]);

        $thread = $this->getMock('Kumatch\BBSAPI\Entity\Thread', array("getId"));
        $thread->expects($this->any())->method("getId")->will($this->returnValue($id));
        /** @var Thread $thread */

        $thread->setTitle($title)
            ->setCreatedAt(new \DateTime('@' . $createdAtUnixTime))
            ->addTag($tag1)->addTag($tag2)
        ;

        $results = $this->spec->format($thread);

        $this->assertEquals($id, $results["id"]);
        $this->assertEquals($title, $results["title"]);
        $this->assertEquals($createdAtUnixTime, $results["created_at"]);
        $this->assertEquals($tags, $results["tags"]);
        $this->assertFalse(isset($results["last_posted_at"]));

        $thread->setLastPostedAt(new \DateTime('@' . $lastPostedAtUnixTime));
        $results2 = $this->spec->format($thread);

        $this->assertEquals($lastPostedAtUnixTime, $results2["last_posted_at"]);
    }
}