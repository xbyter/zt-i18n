<?php
/**
 * @Author:      余兴 - zt14858
 * @DateTime:    2023/8/31 18:58
 * @Description: 根据小数点来来分目录，如order.forbidden，product.not_found
 */

namespace ZtI18n\KeyParsers;

class DotKeyParser extends BaseKeyParser
{
    /** @var string 分隔符 */
    protected $dot = '.';

    /** @var string 当没找到分隔符时，设置默认的group */
    protected $defaultGroup = '';

    public function __construct(string $dot = '.', string $defaultGroup = '')
    {
        $this->dot = $dot;
        $this->defaultGroup = $defaultGroup;
    }

    /**
     * 根据小数点来来分目录，如order.forbidden，product.not_found
     * @param string $key
     * @return array 例如：['group' => 'order', 'key' => '1001001']
     */
    public function parse(string $key): array
    {
        $pos = mb_strpos($key, $this->dot);
        return [
            'group' => $pos ? $this->replaceSpecialChar(mb_substr($key, 0, $pos)) : $this->defaultGroup,
            'key' => $pos ? mb_substr($key, $pos + 1) : $key,
        ];
    }
}