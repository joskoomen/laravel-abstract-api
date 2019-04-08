<?php

namespace JosKoomen\AbstractApi;


use Illuminate\Support\Facades\Facade;

class AbstractApiFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'joskoomen_abstract_api';
    }

}

