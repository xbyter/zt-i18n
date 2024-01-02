<?php

namespace Ztphp\I18n\Tests;

use PHPUnit\Framework\TestCase;
use Ztphp\I18n\Caches\I18nRedisCache;

class BaseTest extends TestCase
{
    protected $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $redis = $this->getRedis();
        $this->cache = new I18nRedisCache($redis);
        $this->cache->setPrefix('pre.');
    }

    protected function getRedis(int $db = 10): \Redis
    {
        $config = [
            'host' => '10.81.56.209',
            'password' => 'z8_UX7BCi_XYckrM',
            'port' => 63100,
            'database' => $db,
        ];

        $redis = new \Redis();
        $redis->connect($config['host'], $config['port']);
        $redis->auth($config['password']);
        $redis->select($config['database']);
        return $redis;
    }

}