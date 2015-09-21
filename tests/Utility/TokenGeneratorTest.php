<?php
namespace Kumatch\BBSAPI\Test\Utility;

use Kumatch\BBSAPI\Utility\TokenGenerator;

class AccessTokenGeneratorTest extends \PHPUnit_Framework_TestCase
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
    public function generate()
    {
        $generator = new TokenGenerator();

        $this->assertRegExp('/^[a-z0-9\.\/]{40}$/i', $generator->generate());
    }
}