<?php

namespace ZtI18n\Laravel\Providers;

use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Xbyter\ApolloClient\ApolloClient;
use Xbyter\ApolloClient\ApolloConfig;
use ZtI18n\Laravel\Commands\I18nSyncArtisan;
use ZtI18n\Loaders\FallbackLoaderInterface;
use ZtI18n\Loaders\JsonFileLoader;
use ZtI18n\Loaders\LoaderInterface;
use ZtI18n\StoreDrivers\JsonFileStore;
use ZtI18n\I18nApolloSync;
use ZtI18n\I18nClient;
use ZtI18n\I18nClientInterface;
use ZtI18n\I18nSyncInterface;

class I18nServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot()
    {
        //发布配置到config目录
        $this->publishConfig();
    }
    public function register()
    {
        //注册i18n加载器，用于加载多语言数据
        $this->registerLoader();
        //注册i18n备用加载器，当默认加载器找不到key时使用备用加载器
        $this->registerFallbackLoader();
        //注册i18n客户端, 可以根据key来获取多语言
        $this->registerClient();
        //注册i18n同步器，同步i18n服务器数据到本地
        $this->registerSync();
        //注册同步artisan命令
        $this->registerCommand();
    }

    protected function publishConfig()
    {
        $configPath = __DIR__ . '/../Configs/i18n.php';
        if (function_exists('config_path')) {
            $publishPath = config_path('i18n.php');
        } else {
            $publishPath = base_path('config/i18n.php');
        }
        $this->publishes([$configPath => $publishPath], 'config');
    }

    /**
     * 注册i18n加载器，用于加载多语言数据
     * @return void
     */
    protected function registerLoader()
    {
        $this->app->singleton(LoaderInterface::class, function ($app) {
            $dir = $this->getConfig('i18n.server_resource_dir');
            return new JsonFileLoader($dir);
        });
    }

    /**
     * 注册i18n备用加载器，当默认加载器找不到key时使用备用加载器
     * @return void
     */
    public function registerFallbackLoader()
    {
        $this->app->singleton(FallbackLoaderInterface::class, function () {
            $dir = $this->getConfig('i18n.local_resource_dir');
            return new JsonFileLoader($dir);
        });
    }

    /**
     * 注册i18n客户端
     * @return void
     */
    protected function registerClient()
    {
        $this->app->singleton(I18nClientInterface::class, function () {
            $client = new I18nClient($this->app->make(LoaderInterface::class));
            $client->setFallbackLoader($this->app->make(FallbackLoaderInterface::class));
            $client->setLang($this->app->getLocale());
            return $client;
        });
    }


    /**
     * 注册i18n同步器，同步i18n服务器数据到本地
     * @return void
     */
    protected function registerSync()
    {
        $this->app->singleton(I18nSyncInterface::class, function () {
            $languages = $this->getConfig('i18n.languages');
            $apolloClient = $this->getApolloClient();
            $dir = $this->getConfig('i18n.server_resource_dir');
            $store = new JsonFileStore($dir);

            $sync = new I18nApolloSync($apolloClient, $store);
            $namespacePrefix = $this->getConfig('i18n.apollo.namespace_prefix');
            foreach ($languages as $language) {
                $sync->addNamespace($language, "{$namespacePrefix}-{$language}");
            }
            return $sync;
        });
    }

    /**
     * 注册命令
     * @return void
     */
    protected function registerCommand()
    {
        $this->app->singleton(
            'command.zt-i18n.sync',
            function ($app) {
                return new I18nSyncArtisan();
            }
        );

        $this->commands('command.zt-i18n.sync');
    }

    private function getApolloClient(): ApolloClient
    {
        $apolloConfig = new ApolloConfig();
        $apolloConfig->configServerUrl = $this->getConfig('i18n.apollo.config_server_url');
        $apolloConfig->appId = $this->getConfig('i18n.apollo.app_id');
        $apolloConfig->cluster = $this->getConfig('i18n.apollo.cluster');
        return new ApolloClient($apolloConfig);
    }

    /**
     * @param string $key
     * @return string|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getConfig(string $key)
    {
        /** @var ConfigContract $config */
        $config = $this->app->make('config');
        return $config->get($key);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [LoaderInterface::class, FallbackLoaderInterface::class, I18nClientInterface::class, I18nSyncInterface::class, 'command.zt-i18n.sync'];
    }
}
