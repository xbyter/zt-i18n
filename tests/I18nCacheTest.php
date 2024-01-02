<?php

namespace Ztphp\I18n\Tests;

class I18nCacheTest extends BaseTest
{

    public function testPut()
    {
        $result = $this->cache->put('Test001', 'Test001-Value', 100);
        $this->assertTrue($result);
    }

    public function testGet()
    {
        $value = $this->cache->get('Test001');

        $this->assertEquals($value, 'Test001-Value');
    }

}