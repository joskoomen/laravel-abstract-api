<?php

namespace JosKoomen\AbstractApi;

class AbstractApiValidation
{
    use AbstractApiValidationTrait;
    private static $_instance = null;

    public static function make()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new AbstractApiValidation();
        }
        return self::$_instance;
    }
}
