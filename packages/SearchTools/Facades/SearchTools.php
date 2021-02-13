<?php

namespace Package\SearchTools\Facades;

use Illuminate\Support\Facades\Facade;

class SearchTools extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Package\SearchTools\SearchTools::class;
    }
}
