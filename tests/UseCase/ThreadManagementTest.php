<?php
namespace Kumatch\BBSAPI\Test\UseCase;

use Doctrine\ORM\EntityManager;
use Kumatch\BBSAPI\Entity\Thread;
use Kumatch\BBSAPI\UseCase\ThreadManagement;
use Kumatch\BBSAPI\Entity\User;
use Kumatch\BBSAPI\Entity\EntityConstant;
use Kumatch\BBSAPI\Value\Tags;

class ThreadManagementTest extends \PHPUnit_Framework_TestCase
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
    public function succeedCreation()
    {
        $title = "foo";
        $thread = new Thread();
        $thread->setTitle($title);

        $user = new User();
        $user->setUsername("foo@example.com");

        $threadRepo = $this->getMock("RepositoryMock", array("add"));
        $threadRepo->expects($this->once())->method("add")
            ->with($this->logicalAnd(
                $this->isInstanceOf('Kumatch\BBSAPI\Entity\Thread'),
                $this->attributeEqualTo("title", $title),
                $this->attributeEqualTo("user", $user),
                $this->attributeEqualTo("lastPostedAt", null)
            ));

        $em = $this->getEntityManagerMock(array("getRepository", "flush"));
        $em->expects($this->once())->method("getRepository")
            ->with($this->equalTo(EntityConstant::THREAD))
            ->will($this->returnValue($threadRepo));
        $em->expects($this->once())->method("flush");

        /** @var EntityManager $em */
        $useCase = new ThreadManagement($em);
        $thread = $useCase->create($thread, $user);

        $this->assertInstanceOf('Kumatch\BBSAPI\Entity\Thread', $thread);
        $this->assertEquals($title, $thread->getTitle());
        $this->assertEquals($user, $thread->getUser());
        $this->assertNull($thread->getLastPostedAt());
    }

    /**
     * @test
     */
    public function succeedRemoving()
    {
        $userId = 42;
        $user = $this->getMock('Kumatch\BBSAPI\Entity\User', array("getId"));
        $user->expects($this->exactly(2))->method("getId")->will($this->returnValue($userId));
        /** @var \Kumatch\BBSAPI\Entity\User $user */

        $thread = new Thread();
        $thread->setUser($user);

        $threadRepo = $this->getMock("RepositoryMock", array("remove"));
        $threadRepo->expects($this->once())->method("remove")
            ->with($this->equalTo($thread));

        $em = $this->getEntityManagerMock(array("getRepository", "flush"));
        $em->expects($this->once())->method("getRepository")
            ->with($this->equalTo(EntityConstant::THREAD))
            ->will($this->returnValue($threadRepo));
        $em->expects($this->once())->method("flush");

        /** @var EntityManager $em */
        $useCase = new ThreadManagement($em);
        $result = $useCase->remove($thread, $user);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function failRemovingIfThreadOwnerIsDifferent()
    {
        $userId1 = 42;
        $userId2 = 27;

        $user1 = $this->getMock('Kumatch\BBSAPI\Entity\User', array("getId"));
        $user1->expects($this->once())->method("getId")->will($this->returnValue($userId1));
        $user2 = $this->getMock('Kumatch\BBSAPI\Entity\User', array("getId"));
        $user2->expects($this->once())->method("getId")->will($this->returnValue($userId2));
        /** @var \Kumatch\BBSAPI\Entity\User $user1 */
        /** @var \Kumatch\BBSAPI\Entity\User $user2 */

        $thread = new Thread();
        $thread->setUser($user2);

        $threadRepo = $this->getMock("RepositoryMock", array("remove"));
        $threadRepo->expects($this->never())->method("remove");

        $em = $this->getEntityManagerMock(array("getRepository", "flush"));
        $em->expects($this->once())->method("getRepository")
            ->with($this->equalTo(EntityConstant::THREAD))
            ->will($this->returnValue($threadRepo));
        $em->expects($this->never())->method("flush");

        /** @var EntityManager $em */
        $useCase = new ThreadManagement($em);
        $result = $useCase->remove($thread, $user1);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function findThreadOne()
    {
        $threadId = 42;
        $thread = new Thread();

        $threadRepo = $this->getMock("RepositoryMock", array("find"));
        $threadRepo->expects($this->once())->method("find")
            ->with($this->equalTo($threadId))
            ->will($this->returnValue($thread));

        $em = $this->getEntityManagerMock(array("getRepository"));
        $em->expects($this->once())->method("getRepository")
            ->with($this->equalTo(EntityConstant::THREAD))
            ->will($this->returnValue($threadRepo));

        /** @var EntityManager $em */
        $useCase = new ThreadManagement($em);

        $this->assertEquals($thread, $useCase->findOne($threadId));
    }

    /**
     * @test
     */
    public function getNullIfThreadIdIsNotExists()
    {
        $threadId = 42;

        $threadRepo = $this->getMock("RepositoryMock", array("find"));
        $threadRepo->expects($this->once())->method("find")
            ->with($this->equalTo($threadId))
            ->will($this->returnValue(null));

        $em = $this->getEntityManagerMock(array("getRepository"));
        $em->expects($this->once())->method("getRepository")
            ->with($this->equalTo(EntityConstant::THREAD))
            ->will($this->returnValue($threadRepo));

        /** @var EntityManager $em */
        $useCase = new ThreadManagement($em);

        $this->assertNull($useCase->findOne($threadId));
    }

    /**
     * @test
     */
    public function findThreadsByTags()
    {
        $tagNames = [ "foo", "bar", "baz" ];
        $tags = new Tags($tagNames);
        $results = [ "ok" ];

        $query = $this->getMock("QueryMock", array("getResult"));
        $query->expects($this->once())->method("getResult")
            ->will($this->returnValue($results));

        $queryBuilder = $this->getMock('QueryBuilderMock', array("select", "innerJoin", "setParameter", "getQuery"));
        $queryBuilder->expects($this->once())->method("select")->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method("innerJoin")->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method("setParameter")
            ->with($this->equalTo("tag_names"), $this->equalTo($tagNames))
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method("getQuery")
            ->will($this->returnValue($query));

        $threadRepo = $this->getMock("RepositoryMock", array("createQueryBuilder"));
        $threadRepo->expects($this->once())->method("createQueryBuilder")
            ->will($this->returnValue($queryBuilder));

        $em = $this->getEntityManagerMock(array("getRepository"));
        $em->expects($this->once())->method("getRepository")
            ->with($this->equalTo(EntityConstant::THREAD))
            ->will($this->returnValue($threadRepo));

        /** @var EntityManager $em */
        $useCase = new ThreadManagement($em);

        $this->assertEquals($results, $useCase->findByTags($tags));
    }
}