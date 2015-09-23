<?php
namespace Kumatch\BBSAPI\Test\UseCase;

use Doctrine\ORM\EntityManager;
use Kumatch\BBSAPI\UseCase\UserTokenAuthorization;
use Kumatch\BBSAPI\Entity\AccessToken;
use Kumatch\BBSAPI\Entity\User;
use Kumatch\BBSAPI\Entity\EntityConstant;

class UserTokenAuthorizationTest extends \PHPUnit_Framework_TestCase
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
    public function succeedAndGetAuthorizedUser()
    {
        $userId = 42;
        $tokenString = "2hKUWekAQlAlNteA2D1gNhXLarQPDoLweuWREVen";
        $now = 1234567890;
        $period = $now + 3600;

        $user = new User();
        $accessToken = new AccessToken();
        $accessToken->setUser($user)->setPeriod($period);

        $accessTokenRepo = $this->getMock("RepositoryMock");
        $em = $this->getEntityManagerMock(array("getRepository"));
        $em->expects($this->once())->method("getRepository")
            ->with($this->equalTo(EntityConstant::ACCESS_TOKEN))
            ->will($this->returnValue($accessTokenRepo));

        $useCase = $this->getMockBuilder('Kumatch\BBSAPI\UseCase\UserTokenAuthorization')
            ->setConstructorArgs(array($em))
            ->setMethods(array("now", "findAccessToken"))
            ->getMock();
        $useCase->expects($this->once())->method("findAccessToken")
            ->with($this->equalTo($userId), $this->equalTo($tokenString))
            ->will($this->returnValue($accessToken));
        $useCase->expects($this->once())->method("now")->will($this->returnValue($now));

        /** @var UserTokenAuthorization $useCase */
        $this->assertEquals($user, $useCase->invoke($userId, $tokenString));
    }

    /**
     * @test
     */
    public function failIfAccessTokenIsNotExists()
    {
        $userId = 42;
        $tokenString = "2hKUWekAQlAlNteA2D1gNhXLarQPDoLweuWREVen";

        $accessTokenRepo = $this->getMock("RepositoryMock");
        $em = $this->getEntityManagerMock(array("getRepository"));
        $em->expects($this->once())->method("getRepository")
            ->with($this->equalTo(EntityConstant::ACCESS_TOKEN))
            ->will($this->returnValue($accessTokenRepo));

        $useCase = $this->getMockBuilder('Kumatch\BBSAPI\UseCase\UserTokenAuthorization')
            ->setConstructorArgs(array($em))
            ->setMethods(array("now", "findAccessToken"))
            ->getMock();
        $useCase->expects($this->once())->method("findAccessToken")
            ->with($this->equalTo($userId), $this->equalTo($tokenString))
            ->will($this->returnValue(null));
        $useCase->expects($this->never())->method("now");

        /** @var UserTokenAuthorization $useCase */
        $this->assertNull($useCase->invoke($userId, $tokenString));
    }

    /**
     * @test
     */
    public function failIfAccessTokenIsExpired()
    {
        $userId = 42;
        $tokenString = "2hKUWekAQlAlNteA2D1gNhXLarQPDoLweuWREVen";
        $now = 1234567890;
        $period = $now - 1;

        $user = new User();
        $accessToken = new AccessToken();
        $accessToken->setUser($user)->setPeriod($period);

        $accessTokenRepo = $this->getMock("RepositoryMock");
        $em = $this->getEntityManagerMock(array("getRepository"));
        $em->expects($this->once())->method("getRepository")
            ->with($this->equalTo(EntityConstant::ACCESS_TOKEN))
            ->will($this->returnValue($accessTokenRepo));

        $useCase = $this->getMockBuilder('Kumatch\BBSAPI\UseCase\UserTokenAuthorization')
            ->setConstructorArgs(array($em))
            ->setMethods(array("now", "findAccessToken"))
            ->getMock();
        $useCase->expects($this->once())->method("findAccessToken")
            ->with($this->equalTo($userId), $this->equalTo($tokenString))
            ->will($this->returnValue(null));

        /** @var UserTokenAuthorization $useCase */
        $this->assertNull($useCase->invoke($userId, $tokenString));
    }
}