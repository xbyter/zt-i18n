<?php

namespace Ztphp\I18n\Caches;

class I18nLaravelCache implements I18nCacheInterface
{
    protected $store;

    protected $prefix;

    public function __construct(\Illuminate\Contracts\Cache\Store $store)
    {
        $this->store = $store;
    }

    /**
     * @param mixed $prefix
     * @return $this
     */
    public function setPrefix($prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * 获取缓存，返回null为未找到
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->store->get($key);
    }

    /**
     * 设置永久缓存
     * @param string $key
     * @param $value
     * @return bool
     */
    public function forever(string $key, $value): bool
    {
        return $this->store->forever($key, $value);
    }


    /**
     * 设置缓存
     * @param string $key
     * @param $value
     * @param int $seconds
     * @return bool
     */
    public function put(string $key, $value, int $seconds): bool
    {
        return $this->store->put($key, $value, $seconds);
    }
}