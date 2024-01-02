<?php

namespace Ztphp\I18n\KeyParsers;

interface KeyParserInterface
{
    /**
     * 翻译key解析，用于根据Key来分组，防止一个文件太大导致载入过慢
     * @param string $key
     * @return array 例如：['group' => 'order', 'key' => '1001001']
     */
    public function parse(string $key): array;
}