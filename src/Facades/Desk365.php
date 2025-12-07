<?php

namespace Davoodf1995\Desk365\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Davoodf1995\Desk365\Desk365
 */
class Desk365 extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Davoodf1995\Desk365\Desk365::class;
    }
}

