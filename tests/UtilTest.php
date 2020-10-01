<?php

use Jenssegers\Model\Util;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    public function testLower()
    {
        $this->assertSame('foo bar baz', Util::stringLower('FOO BAR BAZ'));
        $this->assertSame('foo bar baz', Util::stringLower('fOo Bar bAz'));
    }

    public function testSnake()
    {
        $this->assertSame('laravel_p_h_p_framework', Util::stringSnake('LaravelPHPFramework'));
        $this->assertSame('laravel_php_framework', Util::stringSnake('LaravelPhpFramework'));
        $this->assertSame('laravel php framework', Util::stringSnake('LaravelPhpFramework', ' '));
        $this->assertSame('laravel_php_framework', Util::stringSnake('Laravel Php Framework'));
        $this->assertSame('laravel_php_framework', Util::stringSnake('Laravel    Php      Framework   '));
        // ensure cache keys don't overlap
        $this->assertSame('laravel__php__framework', Util::stringSnake('LaravelPhpFramework', '__'));
        $this->assertSame('laravel_php_framework_', Util::stringSnake('LaravelPhpFramework_', '_'));
        $this->assertSame('laravel_php_framework', Util::stringSnake('laravel php Framework'));
        $this->assertSame('laravel_php_frame_work', Util::stringSnake('laravel php FrameWork'));
        // prevent breaking changes
        $this->assertSame('foo-bar', Util::stringSnake('foo-bar'));
        $this->assertSame('foo-_bar', Util::stringSnake('Foo-Bar'));
        $this->assertSame('foo__bar', Util::stringSnake('Foo_Bar'));
        $this->assertSame('żółtałódka', Util::stringSnake('ŻółtaŁódka'));
    }

    public function testStudly()
    {
        $this->assertSame('LaravelPHPFramework', Util::stringStudly('laravel_p_h_p_framework'));
        $this->assertSame('LaravelPhpFramework', Util::stringStudly('laravel_php_framework'));
        $this->assertSame('LaravelPhPFramework', Util::stringStudly('laravel-phP-framework'));
        $this->assertSame('LaravelPhpFramework', Util::stringStudly('laravel  -_-  php   -_-   framework   '));

        $this->assertSame('FooBar', Util::stringStudly('fooBar'));
        $this->assertSame('FooBar', Util::stringStudly('foo_bar'));
        $this->assertSame('FooBar', Util::stringStudly('foo_bar')); // test cache
        $this->assertSame('FooBarBaz', Util::stringStudly('foo-barBaz'));
        $this->assertSame('FooBarBaz', Util::stringStudly('foo-bar_baz'));
    }
}
