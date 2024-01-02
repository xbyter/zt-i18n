<?php
/**
 * @Author:      余兴 - zt14858
 * @DateTime:    2023/8/31 19:36
 * @Description: JSON翻译文件载入
 */

namespace Ztphp\I18n\Loaders;


class JsonFileLoader implements LoaderInterface, FallbackLoaderInterface
{
    /** @var string 要加载的目录 */
    public $dir;

    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    /**
     *
     * @param string $lang
     * @param string|null $group
     * @return array<string, string>
     */
    public function load(string $lang, string $group = null): array
    {
        if ($group) {
            return $this->loadFile($this->dir . "/{$lang}/{$group}.json");
        }
        return $this->loadFile($this->dir . "/{$lang}.json}");
    }

    protected function loadFile(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }
        return json_decode(file_get_contents($path), true);
    }

    /**
     * 获取所有翻译文件，示例：
     *  [
     *      "zh-CN"：{
     *          "13131034": "计费时间不能为空"
     *      }
     *  ]
     * @param string[] $languages
     * @return array<string, array<string,string>>
     */
    public function all(array $languages): array
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY //只遍历文件节点
        );

        $data = [];
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            foreach ($languages as $lang) {
                if ($file->getBasename() !== "{$lang}.json") {
                    continue;
                }
                !isset($data[$lang]) && $data[$lang] = [];
                $data[$lang] += (array)json_decode(file_get_contents($file->getPathname()), true);
            }
        }
        return $data;
    }
}