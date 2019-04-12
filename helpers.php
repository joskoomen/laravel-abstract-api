<?php

if (!function_exists('is_laravel')) {
    function is_laravel()
    {
        return (app() && app() instanceof \Illuminate\Foundation\Application);
    }
}

if (!function_exists('is_lumen')) {
    function is_lumen()
    {
        return (app() && app() instanceof \Laravel\Lumen\Application);
    }
}
