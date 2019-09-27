<?php

namespace Hailkongsan\AntiBotLink\Test;

use Carbon\Carbon;
use Hailkongsan\AntiBotLink\AntiBotLink;
use Intervention\Image\ImageManagerStatic;
use Hailkongsan\AntiBotLink\AntiBotLinkSessionManager;

/**
 * @todo Add more test.
 */
class AntiBotLinkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->antiBotLink = new AntiBotLink();
    }

    public function test_it_should_init_correctly()
    {
        $this->assertInstanceOf(AntiBotLinkSessionManager::class, $this->antiBotLink->getSession());
        $this->assertInstanceOf(ImageManagerStatic::class, $this->antiBotLink->getImageInstance());
    }

    public function test_it_should_generate()
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $result = $this->antiBotLink->generate();

        $this->assertTrue($result);
        $this->assertCount(config('antibotlink.links'), $this->antiBotLink->getSession()->get('links'));
        $this->assertCount(config('antibotlink.links') + 1, $this->antiBotLink->getSession()->get('data'));
        $this->assertEquals($now->addSeconds(config('antibotlink.expire')), $this->antiBotLink->getSession()->get('expire_at'));
        $this->assertTrue($this->antiBotLink->getSession()->has('solution'));
    }

    public function test_it_should_clear_session_and_return_true_when_verify_with_valid_solution()
    {
        $this->antiBotLink->generate();

        $result = $this->antiBotLink->verify($this->antiBotLink->getSession()->get('solution'));
        $this->assertTrue($result);
        $this->assertEmpty($this->antiBotLink->getSession()->get());
    }

    public function test_it_should_return_false_when_verify_with_invalid_solution()
    {
        $this->antiBotLink->generate();
        $this->assertFalse($this->antiBotLink->verify('some invalid solution'));
    }
}
