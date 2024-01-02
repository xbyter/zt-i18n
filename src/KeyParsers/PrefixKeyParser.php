<?php
/**
 * @Author:      余兴 - zt14858
 * @DateTime:    2023/8/31 18:58
 * @Description: 根据截取前缀来分目录，适用于固定规则的key。如1010XXX代表商品，1020XXX代表订单
 */

namespace Ztphp\I18n\KeyParsers;

class PrefixKeyParser extends BaseKeyParser
{
    protected $prefixNum = 4;

    /** @var string 当key长度小于$prefixNum长度，设置默认的group */
    protected $defaultGroup = '';

    public function __construct(string $prefixNum, string $defaultGroup = '')
    {
        $this->prefixNum = $prefixNum;
        $this->defaultGroup = $defaultGroup;
    }

    /**
     * 根据截取前缀来分目录，适用于固定规则的key。如1010XXX代表商品，1020XXX代表订单
     * @param string $key
     * @return array 例如：['group' => 'order', 'key' => '1001001']
     */
    public function parse(string $key): array
    {
        return [
            'group' => strlen($key) >= $this->prefixNum ? $this->replaceSpecialChar(substr($key, 0, $this->prefixNum)) : $this->defaultGroup,
            'key' => $key,
        ];
    }
}