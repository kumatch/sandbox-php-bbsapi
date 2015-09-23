<?php
namespace Kumatch\BBSAPI\Test\Spec;

use Kumatch\BBSAPI\Spec\PostSpec;
use Kumatch\BBSAPI\Entity\Post;
use Kumatch\BBSAPI\Entity\Thread;

class PostSpecTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PostSpec
     */
    private $spec;

    protected function setUp()
    {
        parent::setUp();

        $this->spec = new PostSpec();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }


    /**
     * @return array
     */
    public function provideValidContent()
    {
        $char10000 = "";
        for ($i = 0; $i < 10000; ++$i) {
            $char10000 .= "a";
        }

        return [
            ["foo"],
            ["日本語の本文"],
            ["includes marks .=<>$%&()^?/"],
            ["あいうえお\nかきくけこ\nさしすせそ"],
            [ $char10000 ]
        ];
    }

    /**
     * @test
     * @dataProvider provideValidContent
     * @param string $content
     */
    public function valid($content)
    {
        $post = new Post();
        $post->setContent($content);

        $result = $this->spec->validate($post);

        $this->assertTrue($result->isValid());
        $this->assertCount(0, $result->getErrors());
    }

    /**
     * @return array
     */
    public function provideInvalidContent()
    {
        $char10001 = "";
        for ($i = 0; $i < 10001; ++$i) {
            $char10001 .= "a";
        }

        return [
            [null],
            [""],
            [ $char10001 ]
        ];
    }

    /**
     * @test
     * @dataProvider provideInvalidContent
     * @param string $content
     * @param int $errorSize
     */
    public function invalidContent($content, $errorSize = 1)
    {
        $post = new Post();
        $post->setContent($content);

        $result = $this->spec->validate($post);
        $errors = $result->getErrors();

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $errors);
        $this->assertCount($errorSize, $errors["content"]);
    }

    /**
     * @test
     */
    public function format()
    {
        $postId = 42;
        $content = "foobarbaz";
        $createdAtUnixTime = 1234567890;

        $threadId = 27;

        $thread = $this->getMock('Kumatch\BBSAPI\Entity\Thread', array("getId"));
        $thread->expects($this->any())->method("getId")->will($this->returnValue($threadId));
        /** @var Thread $thread */

        $post = $this->getMock('Kumatch\BBSAPI\Entity\Post', array("getId"));
        $post->expects($this->any())->method("getId")->will($this->returnValue($postId));
        /** @var Post $post */

        $post->setContent($content)
            ->setThread($thread)
            ->setCreatedAt(new \DateTime('@' . $createdAtUnixTime));

        $results = $this->spec->format($post);

        $this->assertEquals($postId, $results["id"]);
        $this->assertEquals($threadId, $results["thread_id"]);
        $this->assertEquals($content, $results["content"]);
        $this->assertEquals($createdAtUnixTime, $results["created_at"]);
    }
}