<?php

namespace ZtI18nTests;

use PHPUnit\Framework\TestCase;
use ZtI18n\Enum\LangEnum;
use ZtI18n\Loaders\JsonFileLoader;
use ZtI18n\I18nClient;
use ZtI18n\I18nDiff;
use ZtI18n\KeyParsers\DotKeyParser;
use ZtI18n\KeyParsers\PrefixKeyParser;

class I18nClientTest extends TestCase
{
    public function testDefaultKeyJsonGet()
    {
        $dir = __DIR__ . '/../storage';
        $loader = new JsonFileLoader($dir);
        $client = new I18nClient($loader);
        $data = $client->get('1010001', ['a','b','c']);
        var_dump($data);
    }

    public function testPrefixKeyJsonGet()
    {
        $dir = __DIR__ . '/../storage';
        $loader = new JsonFileLoader($dir);
        $client = new I18nClient($loader);
        $client->setKeyParser(new PrefixKeyParser(4, 'other'));
        $data = $client->get('1010001', ['a','b','c']);
        var_dump($data);
    }


    public function testDotKeyJsonGet()
    {
        $dir = __DIR__ . '/../storage';
        $loader = new JsonFileLoader($dir);
        $client = new I18nClient($loader);
        $client->setKeyParser(new DotKeyParser('.', 'other'));
        $data = $client->get('1010001', ['a','b','c']);
        var_dump($data);
    }

    public function testAll()
    {
        $dir = __DIR__ . '/../storage';
        $loader = new JsonFileLoader($dir);
        $data = $loader->all([LangEnum::EN_US, LangEnum::ZH_CN]);
        var_dump($data);
    }

    public function testDiff()
    {
        $dir = __DIR__ . '/../storage';
        $loader = new JsonFileLoader($dir);
        $diff = new I18nDiff();
        $data = $diff->diff([LangEnum::EN_US => ['a' => 123], LangEnum::ZH_CN => ['1020001' => '创建商品失败2']], $loader->all([LangEnum::EN_US, LangEnum::ZH_CN]));
        var_dump($data);
    }
}