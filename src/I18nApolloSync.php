<?php
/**
 * @Author:      余兴 - zt14858
 * @DateTime:    2023/8/31 18:59
 * @DescrserverIption: 从阿波罗同步翻译
 */

namespace Ztphp\I18n;

use Xbyter\ApolloClient\ApolloClient;
use Xbyter\ApolloClient\ApolloConfigSync;
use Ztphp\I18n\Caches\I18nCacheInterface;
use Ztphp\I18n\Stores\StoreInterface;

class I18nApolloSync implements I18nSyncInterface
{
    /** @var ApolloClient $apolloClient */
    protected $apolloClient;

    /** @var StoreInterface $store */
    protected $store;

    /** @var I18nCacheInterface */
    protected $cache;

    /** @var int 过期秒数，0为永不过期 */
    protected $expirationSeconds;

    /** @var array<string, string> */
    protected $langNamespaces = [];

    /** @var string 服务端IP，用于阿波罗灰度 */
    protected $serverIp = '';

    public function __construct(ApolloClient $apolloClient, StoreInterface $store)
    {
        $this->apolloClient = $apolloClient;
        $this->store = $store;
    }

    /**
     * 添加命名空间，用于阿波罗i18n同步
     * @param string $lang 多用于文件名
     * @param string $namespace 用于阿波罗命名空间同步
     * @return $this
     */
    public function addNamespace(string $lang, string $namespace): self
    {
        $this->langNamespaces[$lang] = $namespace;
        return $this;
    }

    /**
     * 设置服务器IP，用于阿波罗灰度
     * @param string $serverIp
     * @return I18nApolloSync
     */
    public function setServerIp(string $serverIp): self
    {
        $this->serverIp = $serverIp;
        return $this;
    }

    /**
     * 设置缓存
     * @param I18nCacheInterface $cache
     * @param int $expirationSeconds
     * @return $this
     */
    public function setCache(I18nCacheInterface $cache, int $expirationSeconds = 3600): self
    {
        $this->cache = $cache;
        $this->expirationSeconds = $expirationSeconds;
        return $this;
    }


    /**
     * 构建阿波罗同步客户端类
     * @return ApolloConfigSync
     */
    protected function buildApolloSyncClient(): ApolloConfigSync
    {
        //阿波罗配置
        $sync = new ApolloConfigSync($this->apolloClient);
        foreach ($this->langNamespaces as $lang => $namespace) {
            $handler = new ApolloStoreHandler($this->store, $lang);
            $sync->addHandler($namespace, $handler);

            if ($this->cache) {
                $cacheHandler = new ApolloCacheHandler($this->cache, $this->expirationSeconds, $lang);
                $sync->addHandler($namespace, $cacheHandler);
            }
        }
        return $sync;
    }

    /**
     * 同步并监听阿波罗i18n改变
     * @param int $listenTimeout 监听多久，0为一直监听，定时任务的话则是60的倍数
     * @return void
     */
    public function syncAndListen(int $listenTimeout = 0)
    {
        $sync = $this->buildApolloSyncClient();
        $sync->run($this->serverIp, $listenTimeout);
    }

    /**
     * 同步一次阿波罗i18n
     * @return void
     * @throws \ErrorException
     * @throws \JsonException
     */
    public function syncOnce()
    {
        $sync = $this->buildApolloSyncClient();
        $sync->force();
    }
}