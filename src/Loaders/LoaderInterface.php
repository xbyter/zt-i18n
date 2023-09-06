<?php

namespace ZtI18n\Loaders;


interface LoaderInterface
{
    public function load(string $lang, string $group = null);

    /**
     * 获取所有翻译文件，示例：
     *  [
     *      "zh-CN"：{
     *          "13131034": "计费时间不能为空"
     *      }
     *  ]
     * @return array<string, array<string,string>>
     */
    public function all(array $languages): array;
}