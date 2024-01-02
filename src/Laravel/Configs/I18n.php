<?php
return [
    //服务端多语言存放目录，用于保存同步i18n服务端多语言
    'server_resource_dir' => storage_path('i18n/json'),
    //本地语言存放目录，用于保存本地的多语言，当服务端找不到key时会调用本地多语言
    'local_resource_dir'  => resource_path('lang'),
    //站点支持的语言
    'languages'           => ['zh-CN', 'en-US'],
    //是否使用缓存，默认使用laravel缓存
    'cache' => true,
    'cache_expiration_seconds' => 3600 * 30,
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