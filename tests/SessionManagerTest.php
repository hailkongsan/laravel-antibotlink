<?php

namespace Hailkongsan\AntiBotLink\Test;

use Illuminate\Session\Store;
use Hailkongsan\AntiBotLink\AntiBotLinkSessionManager;

class SessionManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->session = new AntiBotLinkSessionManager();
    }

    public function test_it_should_init_correctly()
    {
        $this->session = new AntiBotLinkSessionManager();

        $this->assertInstanceOf(Store::class, $this->session->getInstance());
        
        $this->assertEquals(config('antibotlink.session_key'), $this->session->getKey());
    }
}