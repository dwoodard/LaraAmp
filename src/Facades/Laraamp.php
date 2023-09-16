<?php

namespace Dwoodard\Laraamp\Facades;

use Illuminate\Support\Facades\Facade;

class Laraamp extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laraamp';
    }
}
