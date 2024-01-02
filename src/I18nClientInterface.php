<?php

namespace Ztphp\I18n;

interface I18nClientInterface
{
    /**
     * 获得翻译
     * @param string $key
     * @param array $replace
     * @param string|null $lang
     * @return string
     */
    public function get(string $key, array $replace = [], string $lang = null): string;
}