<?php

namespace ZtI18n\Laravel\Controllers;


use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use ZtI18n\Loaders\FallbackLoaderInterface;
use ZtI18n\Loaders\LoaderInterface;
use ZtI18n\I18nDiff;

class I18nController extends Controller
{
    /** @var LoaderInterface */
    protected $loader;

    /** @var LoaderInterface */
    protected $fallbackLoader;

    public function __construct(LoaderInterface $loader, FallbackLoaderInterface $fallbackLoader)
    {
        $this->loader = $loader;
        $this->fallbackLoader = $fallbackLoader;
    }

    /**
     * 本地新增的数据
     * @throws BindingResolutionException
     */
    public function newData(): JsonResponse
    {
        //获取多语言配置
        /** @var ConfigContract $config */
        $config = Container::getInstance()->make('config');
        $languages = $config->get('i18n.languages');

        //加载本地语言和服务器同步下来的语言并做对比
        $localData = $this->fallbackLoader->all($languages);
        $serverData = $this->loader->all($languages);
        $i18nDiff = new I18nDiff();
        $diffData = $i18nDiff->diff($localData, $serverData);

        /** @var ResponseFactory $response */
        $response = Container::getInstance()->make(ResponseFactory::class);
        return $response->json([
            'errorChangeInfo' => $diffData,
            'isErrorChange' => (bool)$diffData,
            //'initTimestampMs' => 0,
            //'initI18InfoSize' => 0,
        ]);
    }
}
