<?php

use ZtI18n\I18nClientInterface;

if (!function_exists('i')) {
    function i(string $key, array $replace = []): string
    {
        return app(I18nClientInterface::class)->get($key, $replace);
    }
}