<?php
/**
 * @Author:      余兴 - zt14858
 * @DateTime:    2023/8/31 18:59
 * @Description: 翻译客户端，用于获取指定key翻译
 */

namespace ZtI18n;

use ZtI18n\Enum\LangEnum;
use ZtI18n\Loaders\LoaderInterface;
use ZtI18n\KeyParsers\KeyParserInterface;

class I18nClient implements I18nClientInterface
{
    /** @var LoaderInterface $loader */
    protected $loader;

    /** @var KeyParserInterface $keyParser 从翻译key里解析group和真实key*/
    protected $keyParser = null;

    /** @var array<string, array<string, array<string, string>>> 多语言数据 */
    protected $data = [];

    /** @var array<string, array<string, array<string, string>>> 备用的多语言数据 */
    protected $fallbackData = [];

    protected $lang = LangEnum::EN_US;

    /** @var LoaderInterface|null 备用加载器，当找不到Key翻译时调用 */
    protected $fallbackLoader = null;

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @param string $lang
     * @return I18nClient
     */
    public function setLang(string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * 备用加载器，当找不到Key翻译时调用
     * @param LoaderInterface $fallbackLoader
     * @return I18nClient
     */
    public function setFallbackLoader(LoaderInterface $fallbackLoader): self
    {
        $this->fallbackLoader = $fallbackLoader;
        return $this;
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

    /**
     * 根据key获得翻译文本
     * @param string $key
     * @param array $replace
     * @param string|null $lang
     * @return string
     */
    public function get(string $key, array $replace = [], string $lang = null): string
    {
        $lang = $lang ?? $this->lang;
        $parserItem = $this->keyParser ? $this->keyParser->parse($key) : ['key' => $key, 'group' => ''];
        $group = $parserItem['group'] ?? '';
        $key = $parserItem['key'] ?? $key;

        if (!$this->isLoaded($lang)) {
            $this->data[$lang][$group] = $this->loader->load($lang, $group);
        }

        //如果找不到翻译则找备用的Loader, 备用的也找不到则默认将Key原样返回
        $value = $this->data[$lang][$group][$key] ?? null;
        if (!$value && $this->fallbackLoader && !$this->isFallbackLoaded($lang)) {
            $this->fallbackData[$lang][$group] = $this->fallbackLoader->load($lang, $group);
            $value = $this->fallbackData[$lang][$group][$key] ?? null;
        }

        if ($value) {
            return $this->replaceVariables($value, $replace);
        }

        return $key;
    }

    /**
     * 替换变量
     * @param string $key
     * @param string[] $replace
     * @return string
     */
    protected function replaceVariables(string $key, array $replace): string
    {
        $indexes = [];
        $values = [];
        foreach ($replace as $index => $value) {
            $indexes[] = "{{$index}}";
            $values[] = $value;
        }

        return str_replace($indexes, $values, $key);
    }

    protected function isLoaded(string $lang, string $group = null): bool
    {
        return isset($this->data[$lang][$group]);
    }

    protected function isFallbackLoaded(string $lang, string $group = null): bool
    {
        return isset($this->fallbackData[$lang][$group]);
    }
}