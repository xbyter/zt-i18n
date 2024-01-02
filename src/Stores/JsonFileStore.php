<?php
/**
 * @Author:      余兴 - zt14858
 * @DateTime:    2023/8/31 18:58
 * @Description: JSON格式保存
 */

namespace Ztphp\I18n\Stores;

use Ztphp\I18n\KeyParsers\KeyParserInterface;

class JsonFileStore implements StoreInterface
{
    public $dir;

    /** @var KeyParserInterface $keyParser 从翻译key里解析group和真实key */
    protected $keyParser = null;

    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    /**
     * 从翻译key里解析group和真实key
     * @param KeyParserInterface $keyParser
     * @return $this
     */
    public function setKeyParser(KeyParserInterface $keyParser): self
    {
        $this->keyParser = $keyParser;
        return $this;
    }

    public function store(array $keyValueMap, string $lang)
    {
        //获取阿波罗多语言内容并解析group
        $data = [];
        foreach ($keyValueMap as $key => $value) {
            $parserItem = $this->keyParser ? $this->keyParser->parse($key) : ['key' => $key, 'group' => ''];
            $group = $parserItem['group'] ?? '';
            $key = $parserItem['key'] ?? $key;
            $data[$group][$key] = $value;
        }

        //按group目录写入
        foreach ($data as $group => $langData) {
            $dir = $this->buildDir($this->dir, $group, $lang);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $filename = $this->buildFilename($lang, $group);
            $path = "{$dir}/{$filename}";
            file_put_contents($path, json_encode($langData, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        }
    }

    protected function buildDir(string $dir, string $group, string $lang): string
    {
        if ($group) {
            return $dir . "/" . $lang;
        }
        return $dir;
    }

    protected function buildFilename(string $lang, string $group): string
    {
        if ($group) {
            return "{$group}.json";
        }
        return "{$lang}.json";
    }
}