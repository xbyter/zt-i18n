<?php

namespace Ztphp\I18n;

use Xbyter\ApolloClient\ApolloConfigsResp;
use Xbyter\ApolloClient\Handlers\HandlerInterface;
use Ztphp\I18n\Stores\StoreInterface;

class ApolloStoreHandler implements HandlerInterface
{
    /** @var StoreInterface $store */
    protected $store;

    /** @var string $lang */
    protected $lang;

    public function __construct(StoreInterface $store, string $lang)
    {
        $this->store = $store;
        $this->lang = $lang;
    }

    public function handle(ApolloConfigsResp $apolloConfigsResp)
    {
        $this->store->store($apolloConfigsResp->configurations, $this->lang);
    }
}