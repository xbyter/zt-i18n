# I18n PHP客户端

# 安装
## 1. 从composer拉取包
```php
composer require ztphp/zt-i18n
```
## 2. 执行发布命令，注册artisan命令行和拷贝默认配置文件到config目录
```php
php artisan vendor:publish --provider="ZtI18n\Laravel\Providers\I18nServiceProvider" --tag=config
```
## 3. 配置config/i18n.php文件
```php
return [
    //服务端多语言存放目录，用于保存同步i18n服务端多语言
    'server_resource_dir' => storage_path('i18n/json'),
    //本地语言存放目录，用于保存本地的多语言，当服务端找不到key时会调用本地多语言
    'local_resource_dir'  => resource_path('lang'),
    //站点支持的语言
    'languages'           => ['zh-CN', 'en-US'],
    //阿波罗配置，用于从阿波罗里同步多语言
    'apollo'              => [
        //命名空间前缀, 完整的命名空间格式为namespace_prefix + '-' + lang，例如fbg-eld-api-zh-CN
        'namespace_prefix'   => 'fbg-eld-api',
        //阿波罗配置服务器地址
        'config_server_url' => '',
        //阿波罗AppID
        'app_id'            => '',
        //阿波罗集群配置
        'cluster'           => 'default',
        //阿波罗密钥
        'secret'            => '',
    ],
];
```
## 4. 新增i18n服务感知路由（控制器I18nController返回本地新增的多语言）
```php
    Route::get('/i18n', 'ZtI18n\Laravel\Controllers\I18nController@newData');
```
# 功能示例

## 1. 从阿波罗同步数据到本地
```php
//只执行一次同步
php artisan zt-i18n:sync -O
//常驻执行同步（配合supervisor使用）
php artisan zt-i18n:sync
//常驻60秒执行同步（配合定时任务使用）
php artisan zt-i18n:sync 60
```
## 2. 根据Key进行翻译
```php
//可以使用{0},{1}等变量，如：product[{0}] not exists
app(I18nClientInterface::class)->get('10101000', ['EL320-SKU001']);

//也可以使用函数来替换上面语句
i('10101000', ['EL320-SKU001']);
```

## 3. 替换接口实现
> 如果默认的加载器/存储器不符合项目的要求，可重新实现LoaderInterface, FallbackLoaderInterface等接口，示例可参考：I18nServiceProvider
```php
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
            $namespacePrefix = $this->getConfig('apollo.namespace_prefix');
            foreach ($languages as $language) {
                $sync->addNamespace($language, "{$namespacePrefix}-{$language}");
            }
            return $sync;
        });
    }
```