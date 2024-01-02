<?php

namespace Ztphp\I18n\Caches;

class I18nRedisCache implements I18nCacheInterface
{
    protected $redis;

    protected $prefix;


    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
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
     * @throws \RedisException
     */
    public function get(string $key)
    {
        $value = $this->redis->get($this->prefix.$key);

        return ! is_null($value) ? $this->unserialize($value) : null;
    }

    /**
     * 设置永久缓存
     * @param string $key
     * @param $value
     * @return bool
     * @throws \RedisException
     */
    public function forever(string $key, $value): bool
    {
        return (bool) $this->redis->set(
            $this->prefix.$key,  $this->serialize($value)
        );
    }


    /**
     * 设置缓存
     * @param string $key
     * @param $value
     * @param int $seconds
     * @return bool
     * @throws \RedisException
     */
    public function put(string $key, $value, int $seconds): bool
    {
        return (bool) $this->redis->setex(
            $this->prefix.$key, (int) max(1, $seconds), $this->serialize($value)
        );
    }


    /**
     * Serialize the value.
     *
     * @param  mixed  $value
     * @return float|int|string
     */
    protected function serialize($value)
    {
        return is_numeric($value) && ! in_array($value, [INF, -INF]) && ! is_nan($value) ? $value : serialize($value);
    }

    /**
     * Unserialize the value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        return is_numeric($value) ? $value : unserialize($value);
    }
}