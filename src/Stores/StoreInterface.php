<?php

namespace Ztphp\I18n\Stores;



interface StoreInterface
{
    /**
     * @param array $keyValueMap, key=>value形式 例如：['1011001' => '商品不存在']
     * @param string $lang
     * @return mixed
     */
    public function store(array $keyValueMap, string $lang);
}