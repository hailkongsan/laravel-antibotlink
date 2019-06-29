<?php

namespace Hailkongsan\AntiBotLink\Facades;

use Illuminate\Support\Facades\Facade;

class AntiBotLink extends Facade
{
	/**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'antibotlink';
    }
}
