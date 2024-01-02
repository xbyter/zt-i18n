<?php
/**
 * @Author:      余兴 - zt14858
 * @DateTime:    2023/8/31 18:59
 * @Description: 翻译客户端，用于获取指定key翻译
 */

namespace Ztphp\I18n;

use Ztphp\I18n\Caches\I18nCacheInterface;
use Ztphp\I18n\Enum\LangEnum;
use Ztphp\I18n\KeyParsers\KeyParserInterface;
use Ztphp\I18n\Loaders\LoaderInterface;

class I18nClient implements I18nClientInterface
{
    /** @var LoaderInterface $loader */
    protected $loader;

    /** @var LoaderInterface|null 备用加载器，当找不到Key翻译时调用 */
    protected $fallbackLoader = null;

    /** @var KeyParserInterface $keyParser 从翻译key里解析group和真实key*/
    protected $keyParser = null;

    /** @var I18nCacheInterface 缓存接口 */
    protected $cache = null;

    /** @var int 过期秒数，0为永不过期 */
    protected $expirationSeconds;

    /** @var array<string, array<string, array<string, string>>> 多语言数据 */
    protected $data = [];

    /** @var array<string, string> 多语言缓存数据 */
    protected $cacheData = [];

    /** @var array<string, array<string, array<string, string>>> 备用的多语言数据 */
    protected $fallbackData = [];

    protected $lang = LangEnum::EN_US;



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
     * 设置缓存管理器
     * @param I18nCacheInterface|null $cache
     * @return $this
     */
    public function setCache(I18nCacheInterface $cache, int $expirationSeconds = 3600): self
    {
        $this->cache = $cache;
        $this->expirationSeconds = $expirationSeconds;
        return $this;
    }

    /**
     * 获取缓存的值
     * @param string $key
     * @param string $lang
     * @return mixed|string|null
     */
    protected function getCacheValue(string $key, string $lang)
    {
        if (!$this->cache) {
            return null;
        }
        $cacheKey = "{$lang}.{$key}";
        if (isset($this->cacheData[$cacheKey])) {
            return $this->cacheData[$cacheKey];
        }

        $value = $this->cache->get($cacheKey);
        if ($value === null) {
            return null;
        }

        $this->cacheData[$cacheKey] = $value;

        return $value;
    }

    /**
     * 设置缓存的值
     * @param string $key
     * @param string $lang
     * @param $value
     * @return void
     */
    protected function setCacheValue(string $key, string $lang, $value)
    {
        if (!$this->cache) {
            return;
        }
        $cacheKey = "{$lang}.{$key}";

        $value = $this->cache->put($cacheKey, $value, $this->expirationSeconds);

        $this->cacheData[$cacheKey] = $value;
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

        $value = $this->getCacheValue($key, $lang);
        if (!$value) {
            $parserItem = $this->keyParser ? $this->keyParser->parse($key) : ['key' => $key, 'group' => ''];
            $parseGroup = $parserItem['group'] ?? '';
            $parseKey = $parserItem['key'] ?? $key;

            if (!$this->isLoaded($lang, $parseGroup)) {
                $this->data[$lang][$parseGroup] = $this->loader->load($lang, $parseGroup);
            }

            //如果找不到翻译则找备用的Loader, 备用的也找不到则默认将Key原样返回
            $value = $this->data[$lang][$parseGroup][$parseKey] ?? null;
            if (!$value && $this->fallbackLoader && !$this->isFallbackLoaded($lang, $parseGroup)) {
                $this->fallbackData[$lang][$parseGroup] = $this->fallbackLoader->load($lang, $parseGroup);
                $value = $this->fallbackData[$lang][$parseGroup][$parseKey] ?? null;
            }
            $value && $this->setCacheValue($key, $lang, $value);
        }

        if ($value) {
            return $this->replaceVariables($value, $replace);
        }

        return $parseKey;
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