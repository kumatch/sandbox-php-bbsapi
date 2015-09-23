<?php
namespace Kumatch\BBSAPI\Test\UseCase;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Kumatch\BBSAPI\UseCase\ThreadPostManagement;
use Kumatch\BBSAPI\Entity\Thread;
use Kumatch\BBSAPI\Entity\Post;
use Kumatch\BBSAPI\Entity\EntityConstant;

class ThreadPostManagementTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    protected function getEntityManagerMock(array $methods = null)
    {
        return $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }


    /**
     * @test
     */
    public function succeedRegistration()
    {
        $thread = new Thread();
        $post = new Post();
        $date = new \DateTime('@1234567890');

        $threadRepo = $this->getMock("RepositoryMock", array("add"));
        $threadRepo->expects($this->once())->method("add")
            ->with($this->logicalAnd(
                $this->isInstanceOf('Kumatch\BBSAPI\Entity\Thread'),
                $this->attributeEqualTo("posts", new ArrayCollection([$post]))
            ));

        $em = $this->getEntityManagerMock(array("getRepository", "flush"));
        $em->expects($this->once())->method("getRepository")
            ->with($this->equalTo(EntityConstant::THREAD))
            ->will($this->returnValue($threadRepo));
        $em->expects($this->once())->method("flush");

        $useCase = $this->getMockBuilder('Kumatch\BBSAPI\UseCase\ThreadPostManagement')
            ->setConstructorArgs(array($em))
            ->setMethods(array("currentDate"))
            ->getMock();
        $useCase->expects($this->once())->method("currentDate")
            ->will($this->returnValue($date));
        /** @var ThreadPostManagement $useCase */

        $post = $useCase->register($thread, $post);

        $this->assertInstanceOf('Kumatch\BBSAPI\Entity\Post', $post);
        $this->assertEquals($thread, $post->getThread());
        $this->assertEquals($date, $post->getThread()->getLastPostedAt());
    }
}