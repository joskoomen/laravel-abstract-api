<?php

namespace JosKoomen\AbstractApi;


class AbstractApiFactory
{

    public static function validate($values, $urlencode = false)
    {
        return AbstractApiValidation::make()->validateValues($values, $urlencode);
    }

    public static function add_signature($values, $urlencode = false)
    {
        return AbstractApiValidation::make()->addTimeAndSignature($values, $urlencode);
    }
}
