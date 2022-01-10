<?php

namespace Oiuv\TalkFunSdk;

use Illuminate\Support\Facades\Facade as LaravelFacade;

class Facade extends LaravelFacade
{
    public static function getFacadeAccessor()
    {
        return MTCloud::class;
    }
}
