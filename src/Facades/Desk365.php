<?php

namespace Devmatika\Desk365\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Devmatika\Desk365\Desk365
 */
class Desk365 extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Devmatika\Desk365\Desk365::class;
    }
}



