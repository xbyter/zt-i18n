<?php

namespace Ztphp\I18n\Caches;

interface I18nCacheInterface
{
    /**
     * 获取缓存，返回null为未找到
     * @param string $key
     * @return mixed
     */
    public function get(string $key);


    /**
     * 设置缓存
     * @param string $key
     * @param $value
     * @param int $seconds
     * @return mixed
     */
    public function put(string $key, $value, int $seconds): bool;

    /**
     * 设置永久缓存
     * @param string $key
     * @param $value
     * @return mixed
     */
    public function forever(string $key, $value): bool;


}