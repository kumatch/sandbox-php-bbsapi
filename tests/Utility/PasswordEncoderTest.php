<?php
namespace Kumatch\BBSAPI\Test\Utility;

use Kumatch\BBSAPI\Utility\PasswordEncoder;

class PasswordEncoderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function encode()
    {
        $salt = "8843d7f92416211de9ebb963ff4ce28125932878";
        $encoder = new PasswordEncoder($salt);

        $this->assertEquals(
            "4f0e4a59d58e630e85f3e00dc35881aa7216363a15016502d7ab0e1f7324a1b2", $encoder->encode("foobar")
        );
        $this->assertEquals(
            "eefe67eb801a8374a72657d4743a4c594fe35ef64f3d053726d4e791ff8d14d1", $encoder->encode("foobarbaz")
        );
    }
}