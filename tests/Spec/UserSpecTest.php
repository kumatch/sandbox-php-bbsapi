<?php
namespace Kumatch\BBSAPI\Test\Spec;

use Kumatch\BBSAPI\Spec\UserSpec;
use Kumatch\BBSAPI\Entity\User;

class UserSpecTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserSpec
     */
    private $spec;

    protected function setUp()
    {
        parent::setUp();

        $this->spec = new UserSpec();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function valid()
    {
        $user = new User();
        $user->setEmail("foo@example.com")
            ->setUsername("foo_user")
            ->setPassword("foo_user_password");

        $result = $this->spec->validate($user);

        $this->assertTrue($result->isValid());
        $this->assertCount(0, $result->getErrors());
    }

    /**
     * @return array
     */
    public function provideInvalidEmail()
    {
        return [
            [ null ],
            [ "" ],
            [ " " ],
            [ "foo" ],
            [ "foo@bar" ]
        ];
    }

    /**
     * @test
     * @dataProvider provideInvalidEmail
     * @param string $email
     * @param int $errorSize
     */
    public function invalidEmail($email, $errorSize = 1)
    {
        $user = new User();
        $user->setEmail($email)
            ->setUsername("foo_user")
            ->setPassword("foo_user_password");

        $result = $this->spec->validate($user);
        $errors = $result->getErrors();

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $errors);
        $this->assertCount($errorSize, $errors["email"]);
    }


    public function provideInvalidUsername()
    {
        return [
            [ null ],
            [ "" ],
            [ " " ],
            [ "日本語" ],
            [ "toolongusername" ],
            [ "foo.bar" ]
        ];
    }

    /**
     * @test
     * @dataProvider provideInvalidUsername
     * @param string $username
     * @param int $errorSize
     */
    public function invalidUsername($username, $errorSize = 1)
    {
        $user = new User();
        $user->setEmail("foo@example.com")
            ->setUsername($username)
            ->setPassword("foo_user_password");

        $result = $this->spec->validate($user);
        $errors = $result->getErrors();

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $errors);
        $this->assertCount($errorSize, $errors["username"]);
    }

    public function provideInvalidPassword()
    {
        return [
            [ null ],
            [ "" ],
            [ " ", 2 ],
            [ "日本語", 2 ],
            [ "tooshort" ]
        ];
    }

    /**
     * @test
     * @dataProvider provideInvalidPassword
     * @param string $password
     * @param int $errorSize
     */
    public function invalidPassword($password, $errorSize = 1)
    {
        $user = new User();
        $user->setEmail("foo@example.com")
            ->setUsername("foo_user")
            ->setPassword($password);

        $result = $this->spec->validate($user);
        $errors = $result->getErrors();

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $errors);
        $this->assertCount($errorSize, $errors["password"]);
    }

    /**
     * @test
     */
    public function format()
    {
        $id = 42;
        $email = "foo@example.com";
        $username = "foo_bar";

        $user = $this->getMock('Kumatch\BBSAPI\Entity\User', array("getId"));
        $user->expects($this->any())->method("getId")->will($this->returnValue($id));
        /** @var User $user */

        $user->setEmail($email)
            ->setUsername($username);

        $results = $this->spec->format($user);

        $this->assertCount(2, $results);
        $this->assertEquals($email, $results["email"]);
        $this->assertEquals($username, $results["username"]);
    }
}