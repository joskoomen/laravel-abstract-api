<?php

use JosKoomen\AbstractApi\AbstractApiFactory as AbstractApi;

if (!function_exists('joskoomen_abstract_api')) {
    function joskoomen_abstract_api()
    {
        $factory = app(AbstractApi::class);
        return $factory;
    }
}
