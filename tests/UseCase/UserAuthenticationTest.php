<?php
namespace Kumatch\BBSAPI\Test\UseCase;

use Kumatch\BBSAPI\UseCase\UserAuthentication;
use Kumatch\BBSAPI\Entity\User;
use Kumatch\BBSAPI\Entity\EntityConstant;

class UserAuthenticationTest extends \PHPUnit_Framework_TestCase
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

    protected function getPasswordEncoderMock(array $methods = null)
    {
        return $this->getMockBuilder('Kumatch\BBSAPI\Utility\PasswordEncoder')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    protected function getTokenGeneratorMock(array $methods = null)
    {
        return $this->getMockBuilder('Kumatch\BBSAPI\Utility\TokenGenerator')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @test
     */
    public function succeedAuthentication()
    {
        $username = "foobar";
        $password = "foobarbazquux";
        $encodedPassword = "7f529eb480fef6c9ea2985c8db4456a66d61e114c3fcec6825f35e6e1c470180";

        $token = "oHyu823kfhdGFpVzLf/PiCoVbo5IqwuQSN0YeiJj";
        $now = 1234567890;
        $period = $now + (UserAuthentication::PERIOD_HOURS * 3600);

        $user = new User();
        $user->setUsername($username)->setEncodedPassword($encodedPassword);

        $userRepo = $this->getMock("RepositoryMock", array("findOneBy"));
        $userRepo->expects($this->once())->method("findOneBy")
            ->with($this->equalTo(array("username" => $username)))
            ->will($this->returnValue($user));

        $accessTokenRepo = $this->getMock("RepositoryMock", array("add"));
        $accessTokenRepo->expects($this->once())->method("add")
            ->with($this->logicalAnd(
                $this->isInstanceOf('Kumatch\BBSAPI\Entity\AccessToken'),
                $this->attributeEqualTo("token", $token),
                $this->attributeEqualTo("period", $period),
                $this->attributeEqualTo("user", $user)
            ));


        $em = $this->getEntityManagerMock(array("getRepository", "flush"));
        $em->expects($this->at(0))->method("getRepository")
            ->with($this->equalTo(EntityConstant::USER))
            ->will($this->returnValue($userRepo));
        $em->expects($this->at(1))->method("getRepository")
            ->with($this->equalTo(EntityConstant::ACCESS_TOKEN))
            ->will($this->returnValue($accessTokenRepo));
        $em->expects($this->once())->method("flush");

        $passwordEncoder = $this->getPasswordEncoderMock(array("encode"));
        $passwordEncoder->expects($this->once())->method("encode")
            ->with($this->equalTo($password))
            ->will($this->returnValue($encodedPassword));

        $tokenGenerator = $this->getTokenGeneratorMock(array("generate"));
        $tokenGenerator->expects($this->once())->method("generate")
            ->will($this->returnValue($token));


        $useCase = $this->getMockBuilder('Kumatch\BBSAPI\UseCase\UserAuthentication')
            ->setConstructorArgs(array($em, $passwordEncoder, $tokenGenerator))
            ->setMethods(array("now"))
            ->getMock();
        $useCase->expects($this->once())->method("now")->will($this->returnValue($now));

        /** @var UserAuthentication $useCase */
        $accessToken = $useCase->invoke($username, $password);

        $this->assertInstanceOf('Kumatch\BBSAPI\Entity\AccessToken', $accessToken);
        $this->assertEquals($token, $accessToken->getToken());
        $this->assertEquals($period, $accessToken->getPeriod());
        $this->assertEquals($user, $accessToken->getUser());
    }

    /**
     * @test
     */
    public function failIfUsernameIsNotExists()
    {
        $username = "foobar";
        $password = "foobarbazquux";

        $userRepo = $this->getMock("RepositoryMock", array("findOneBy"));
        $userRepo->expects($this->once())->method("findOneBy")
            ->with($this->equalTo(array("username" => $username)))
            ->will($this->returnValue(null));

        $accessTokenRepo = $this->getMock("RepositoryMock", array("add"));
        $accessTokenRepo->expects($this->never())->method("add");


        $em = $this->getEntityManagerMock(array("getRepository", "flush"));
        $em->expects($this->at(0))->method("getRepository")
            ->with($this->equalTo(EntityConstant::USER))
            ->will($this->returnValue($userRepo));
        $em->expects($this->at(1))->method("getRepository")
            ->with($this->equalTo(EntityConstant::ACCESS_TOKEN))
            ->will($this->returnValue($accessTokenRepo));
        $em->expects($this->never())->method("flush");

        $passwordEncoder = $this->getPasswordEncoderMock(array("encode"));
        $passwordEncoder->expects($this->never())->method("encode");

        $tokenGenerator = $this->getTokenGeneratorMock(array("generate"));
        $tokenGenerator->expects($this->never())->method("generate");

        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Kumatch\BBSAPI\Utility\PasswordEncoder $passwordEncoder */
        /** @var \Kumatch\BBSAPI\Utility\TokenGenerator $tokenGenerator */
        $useCase = new UserAuthentication($em, $passwordEncoder, $tokenGenerator);

        /** @var UserAuthentication $useCase */
        $this->assertFalse($useCase->invoke($username, $password));
    }

    /**
     * @test
     */
    public function failIfPasswordIsInvalid()
    {
        $username = "foobar";
        $password = "foobarbazquux";
        $encodedPassword = "7f529eb480fef6c9ea2985c8db4456a66d61e114c3fcec6825f35e6e1c470180";

        $token = "oHyu823kfhdGFpVzLf/PiCoVbo5IqwuQSN0YeiJj";
        $now = 1234567890;

        $user = new User();
        $user->setUsername($username)->setEncodedPassword($encodedPassword);

        $userRepo = $this->getMock("RepositoryMock", array("findOneBy"));
        $userRepo->expects($this->once())->method("findOneBy")
            ->with($this->equalTo(array("username" => $username)))
            ->will($this->returnValue($user));

        $accessTokenRepo = $this->getMock("RepositoryMock", array("add"));
        $accessTokenRepo->expects($this->never())->method("add");


        $em = $this->getEntityManagerMock(array("getRepository", "flush"));
        $em->expects($this->at(0))->method("getRepository")
            ->with($this->equalTo(EntityConstant::USER))
            ->will($this->returnValue($userRepo));
        $em->expects($this->at(1))->method("getRepository")
            ->with($this->equalTo(EntityConstant::ACCESS_TOKEN))
            ->will($this->returnValue($accessTokenRepo));
        $em->expects($this->never())->method("flush");

        $passwordEncoder = $this->getPasswordEncoderMock(array("encode"));
        $passwordEncoder->expects($this->once())->method("encode")
            ->with($this->equalTo($password))
            ->will($this->returnValue("invalid_password_string"));

        $tokenGenerator = $this->getTokenGeneratorMock(array("generate"));
        $tokenGenerator->expects($this->never())->method("generate");


        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Kumatch\BBSAPI\Utility\PasswordEncoder $passwordEncoder */
        /** @var \Kumatch\BBSAPI\Utility\TokenGenerator $tokenGenerator */
        $useCase = new UserAuthentication($em, $passwordEncoder, $tokenGenerator);

        /** @var UserAuthentication $useCase */
        $this->assertFalse($useCase->invoke($username, $password));
    }
}