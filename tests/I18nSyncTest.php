<?php

namespace Ztphp\I18n\Tests;

use Xbyter\ApolloClient\ApolloClient;
use Xbyter\ApolloClient\ApolloConfigsResp;
use Ztphp\I18n\Stores\JsonFileStore;
use Ztphp\I18n\KeyParsers\DotKeyParser;
use Ztphp\I18n\KeyParsers\PrefixKeyParser;
use Ztphp\I18n\I18nApolloSync;

class I18nSyncTest extends BaseTest
{
    protected function getApolloClient(): ApolloClient
    {
        //MOCK数据
        $apolloConfigsResp = $this->getConfigsMockData();
        $apolloClient = $this->createMock(ApolloClient::class);
        $apolloClient->method('configs')->willReturn($apolloConfigsResp);
        return $apolloClient;
    }

    private function getConfigsMockData(): ApolloConfigsResp
    {
        $apolloConfigsResp = new ApolloConfigsResp();
        $apolloConfigsResp->appId = 'i18n-error-config';
        $apolloConfigsResp->cluster = 'default';
        $apolloConfigsResp->namespaceName = 'i18n-zh-CN';
        $apolloConfigsResp->releaseKey = '';
        $apolloConfigsResp->configurations = [
            '1010001' => '{0}=创建订单[{1}]失败=!:{2}',
            'product.not_found' => '商品未找到',
            '1020001' => '创建商品失败2',
            'order.forbidden' => '没有权限',
        ];
        return $apolloConfigsResp;
    }

    /**
     * JSON存储方式默认同步，会将所有key都存到zh-CN和en-US里。
     * @throws \JsonException
     * @throws \ErrorException
     */
    public function testDefaultKeyJsonSync()
    {
        $apolloClient = $this->getApolloClient();
        $dir = __DIR__ . '/../storage';
        $store = new JsonFileStore($dir);
        $sync = new I18nApolloSync($apolloClient, $store);
        $sync->setCache($this->cache, 60);
        $sync->addNamespace('zh-CN', 'fbg-eld-api-zh-CN')
            ->addNamespace('en-US', 'fbg-eld-api-en-US')
            ->syncOnce();
    }


    /**
     * 测试带前缀的Key同步，如1010XXX代表商品，1020XXX代表订单
     * @throws \JsonException
     * @throws \ErrorException
     */
    public function testPrefixKeyJsonSync()
    {
        $apolloClient = $this->getApolloClient();
        $dir = __DIR__ . '/../storage';
        $store = new JsonFileStore($dir);
        $store->setKeyParser(new PrefixKeyParser(4, 'other'));
        $sync = new I18nApolloSync($apolloClient, $store);
        $sync->setCache($this->cache, 60);
        $sync->addNamespace('zh-CN', 'fbg-eld-api-zh-CN')
            ->addNamespace('en-US', 'fbg-eld-api-en-US')
            ->syncOnce();
    }

    /**
     * 测试带.的Key同步，如order.forbidden，product.not_found
     * @throws \JsonException
     * @throws \ErrorException
     */
    public function testDotKeyJsonSync()
    {
        $apolloClient = $this->getApolloClient();
        $store = new JsonFileStore(__DIR__ . '/../storage');
        $store->setKeyParser(new DotKeyParser('.', 'other'));

        $sync = new I18nApolloSync($apolloClient, $store);
        $sync->setCache($this->cache, 60);
        $sync->addNamespace('zh-CN', 'fbg-eld-api-zh-CN')
            ->addNamespace('en-US', 'fbg-eld-api-en-US')
            ->syncOnce();
    }
}