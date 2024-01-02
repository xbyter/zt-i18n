<?php

namespace Ztphp\I18n;

use Xbyter\ApolloClient\ApolloConfigsResp;
use Xbyter\ApolloClient\Handlers\HandlerInterface;
use Ztphp\I18n\Caches\I18nCacheInterface;

class ApolloCacheHandler implements HandlerInterface
{
    /** @var I18nCacheInterface */
    protected $cache;

    /** @var int 过期秒数，0为永不过期 */
    protected $expirationSeconds;

    /** @var string $lang */
    protected $lang;

    public function __construct(I18nCacheInterface $cache, int $expirationSeconds, string $lang)
    {
        $this->cache = $cache;
        $this->expirationSeconds = $expirationSeconds;
        $this->lang = $lang;
    }

    public function handle(ApolloConfigsResp $apolloConfigsResp)
    {
        foreach ($apolloConfigsResp->configurations as $key => $value) {
            $cacheKey = "{$this->lang}.{$key}";
            if ($this->expirationSeconds > 0) {
                $this->cache->put($cacheKey, $value, $this->expirationSeconds);
            } else {
                $this->cache->forever($cacheKey, $value);
            }
        }
    }
}