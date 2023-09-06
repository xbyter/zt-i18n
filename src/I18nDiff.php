<?php
/**
 * @Author:      余兴 - zt14858
 * @DateTime:    2023/9/4 17:24
 * @Description: 根据传过来的值跟Store里的翻译文件做差异对比
 */


namespace ZtI18n;


class I18nDiff
{
    /**
     * 对比差异
     * @param array $localData
     * @param array $serverData
     * @return array<string, array<string, string>>
     */
    public function diff(array $localData, array $serverData): array
    {
        $languages = array_keys($localData);
        $diffData = [];
        foreach ($languages as $lang) {
            $diffItem = array_diff_key($localData[$lang], $serverData[$lang] ?? []);
            $diffItem && $diffData[$lang] = $diffItem;
        }
        return $diffData;
    }
}