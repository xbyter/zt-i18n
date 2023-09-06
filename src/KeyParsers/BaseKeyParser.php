<?php
/**
 * @Author:      余兴 - zt14858
 * @DateTime:    2023/8/31 18:58
 * @Description: 根据小数点来来分目录，如order.forbidden，product.not_found
 */

namespace ZtI18n\KeyParsers;

abstract class BaseKeyParser implements KeyParserInterface
{

    /**
     * 替换特殊字符
     * @param string $group
     * @return string
     */
    public function replaceSpecialChar(string $group): string
    {
        return str_replace(["/", "\\"], "-", $group);
    }
}