<?php

namespace Hailkongsan\AntiBotLink\Test;

use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            'Hailkongsan\AntiBotLink\AntiBotLinkServiceProvider',
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'AntiBotLink' => 'Hailkongsan\AntiBotLink\Facades\AntiBotLink',
        ];
    }
}
