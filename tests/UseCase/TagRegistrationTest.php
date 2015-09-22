<?php
namespace Kumatch\BBSAPI\Test\UseCase;

use Doctrine\ORM\EntityManager;
use Kumatch\BBSAPI\Entity\Tag;
use Kumatch\BBSAPI\UseCase\TagRegistration;
use Kumatch\BBSAPI\Entity\EntityConstant;

class TagRegistrationTest extends \PHPUnit_Framework_TestCase
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
    public function succeedRegistrationANewTag()
    {
        $name = "foo";
        $tag = new Tag();
        $tag->setName($name);

        $tagRepo = $this->getMock("RepositoryMock", array("findOneBy", "add"));
        $tagRepo->expects($this->once())->method("findOneBy")
            ->with($this->equalTo(array("name" => $name)))
            ->will($this->returnValue(null));
        $tagRepo->expects($this->once())->method("add")
            ->with($this->logicalAnd(
                $this->isInstanceOf('Kumatch\BBSAPI\Entity\Tag'),
                $this->attributeEqualTo("name", $name)
            ));

        $em = $this->getEntityManagerMock(array("getRepository", "flush"));
        $em->expects($this->once())->method("getRepository")
            ->with($this->equalTo(EntityConstant::TAG))
            ->will($this->returnValue($tagRepo));
        $em->expects($this->once())->method("flush");
        /** @var EntityManager $em */

        $useCase = new TagRegistration($em);
        $tag = $useCase->register($tag);

        $this->assertInstanceOf('Kumatch\BBSAPI\Entity\Tag', $tag);
        $this->assertEquals($name, $tag->getName());
    }

    /**
     * @test
     */
    public function getAExistsTagAndDoNotRegistration()
    {
        $name = "foo";
        $tag = new Tag();
        $tag->setName($name);

        $tagRepo = $this->getMock("RepositoryMock", array("findOneBy", "add"));
        $tagRepo->expects($this->once())->method("findOneBy")
            ->with($this->equalTo(array("name" => $name)))
            ->will($this->returnValue($tag));
        $tagRepo->expects($this->never())->method("add");


        $em = $this->getEntityManagerMock(array("getRepository", "flush"));
        $em->expects($this->once())->method("getRepository")
            ->with($this->equalTo(EntityConstant::TAG))
            ->will($this->returnValue($tagRepo));
        $em->expects($this->never())->method("flush");
        /** @var EntityManager $em */

        $useCase = new TagRegistration($em);
        $tag = $useCase->register($tag);

        $this->assertInstanceOf('Kumatch\BBSAPI\Entity\Tag', $tag);
        $this->assertEquals($name, $tag->getName());
    }
}