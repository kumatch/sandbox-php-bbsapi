<?php
namespace Kumatch\BBSAPI\Test\UseCase;

use Kumatch\BBSAPI\UseCase\UserRegistration;
use Kumatch\BBSAPI\Entity\User;
use Kumatch\BBSAPI\Entity\EntityConstant;

class UserRegistrationTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @test
     */
    public function registerSuccess()
    {
        $email = "foo@example.com";
        $username = "foobar";
        $password = "foobarbazquux";
        $encodedPassword = "7f529eb480fef6c9ea2985c8db4456a66d61e114c3fcec6825f35e6e1c470180";

        $user = new User();
        $user->setEmail($email)
            ->setUsername($username)
            ->setPassword($password);


        $repoMock = $this->getMock("RepositoryMock", array("add"));
        $repoMock->expects($this->once())->method("add")
            ->with($this->equalTo($user));

        $emMock = $this->getEntityManagerMock(array("getRepository", "flush"));
        $emMock->expects($this->once())->method("getRepository")
            ->with($this->equalTo(EntityConstant::USER))
            ->will($this->returnValue($repoMock));
        $emMock->expects($this->once())->method("flush");

        $passwordEncoderMock = $this->getPasswordEncoderMock(array("encode"));
        $passwordEncoderMock->expects($this->once())->method("encode")
            ->with($this->equalTo($password))
            ->will($this->returnValue($encodedPassword));

        /** @var \Doctrine\ORM\EntityManager $emMock */
        /** @var \Kumatch\BBSAPI\Utility\PasswordEncoder $passwordEncoderMock */

        $useCase = new UserRegistration($emMock, $passwordEncoderMock);
        $user = $useCase->invoke($user);

        $this->assertInstanceOf('Kumatch\BBSAPI\Entity\User', $user);
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($encodedPassword, $user->getEncodedPassword());
        $this->assertNull($user->getPassword());
    }

    /**
     * @test
     */
    public function findUserByUsername()
    {
        $username = "foobar";
        $user = new User();
        $user->setUsername($username);

        $repoMock = $this->getMock("RepositoryMock", array("findOneBy"));
        $repoMock->expects($this->once())->method("findOneBy")
            ->with($this->equalTo([ "username" => $username ]))
            ->will($this->returnValue($user));

        $emMock = $this->getEntityManagerMock(array("getRepository"));
        $emMock->expects($this->once())->method("getRepository")
            ->with($this->equalTo(EntityConstant::USER))
            ->will($this->returnValue($repoMock));

        $passwordEncoderMock = $this->getPasswordEncoderMock();
        /** @var \Doctrine\ORM\EntityManager $emMock */
        /** @var \Kumatch\BBSAPI\Utility\PasswordEncoder $passwordEncoderMock */

        $useCase = new UserRegistration($emMock, $passwordEncoderMock);

        $this->assertEquals($user, $useCase->findByUsername($username));
    }

    /**
     * @test
     */
    public function getNullIfFindUsernameIsNotExists()
    {
        $username = "foobar";

        $repoMock = $this->getMock("RepositoryMock", array("findOneBy"));
        $repoMock->expects($this->once())->method("findOneBy")
            ->with($this->equalTo([ "username" => $username ]))
            ->will($this->returnValue(null));

        $emMock = $this->getEntityManagerMock(array("getRepository"));
        $emMock->expects($this->once())->method("getRepository")
            ->with($this->equalTo(EntityConstant::USER))
            ->will($this->returnValue($repoMock));

        $passwordEncoderMock = $this->getPasswordEncoderMock();
        /** @var \Doctrine\ORM\EntityManager $emMock */
        /** @var \Kumatch\BBSAPI\Utility\PasswordEncoder $passwordEncoderMock */

        $useCase = new UserRegistration($emMock, $passwordEncoderMock);

        $this->assertNull($useCase->findByUsername($username));
    }
}