<?php

/**
 * @copyright  Copyright (C) 2005 - 2022 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input\Tests;

use Joomla\Filter\InputFilter;
use Joomla\Input\Cookie;
use Joomla\Test\TestHelper;
use PHPUnit\Framework\TestCase;

abstract class CookieDataStore
{
    private static $store = [];

    public static function reset(): void
    {
        self::$store = [];
    }

    public static function has(string $key): bool
    {
        return isset(self::$store[$key]);
    }

    public static function set(string $key, $value): void
    {
        self::$store[$key] = $value;
    }
}

/**
 * Test class for \Joomla\Input\Cookie.
 */
class CookieTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        CookieDataStore::reset();
    }

    /**
     * @testdox  Tests the input creates itself properly
     *
     * @covers   Joomla\Input\Cookie
     * @uses     Joomla\Input\Input
     */
    public function test__constructDefaultBehaviour()
    {
        $instance = new Cookie();

        $this->assertSame($_COOKIE, TestHelper::getValue($instance, 'data'), 'The Cookie input defaults to the $_COOKIE superglobal');
        $this->assertInstanceOf(InputFilter::class, TestHelper::getValue($instance, 'filter'), 'The Input object should create an InputFilter if one is not provided');
    }

    /**
     * @testdox  Tests the constructor with injected data
     *
     * @covers   Joomla\Input\Cookie
     * @uses     Joomla\Input\Input
     */
    public function test__constructDependencyInjection()
    {
        $src        = ['foo' => 'bar'];
        $mockFilter = $this->createMock(InputFilter::class);

        $instance = new Cookie($src, ['filter' => $mockFilter]);

        $this->assertSame($src, TestHelper::getValue($instance, 'data'));
        $this->assertSame($mockFilter, TestHelper::getValue($instance, 'filter'));
    }

    /**
     * @testdox  Tests that data is correctly set
     *
     * @covers   Joomla\Input\Cookie
     * @uses     Joomla\Input\Input
     */
    public function testSetWithNewSignature()
    {
        $mockFilter = $this->createMock(InputFilter::class);

        $instance = new Cookie([], ['filter' => $mockFilter]);
        $instance->set('foo', 'bar', ['expire' => 15, 'samesite' => 'Strict']);

        $this->assertTrue(CookieDataStore::has('foo'));
    }
}

// Stub for setcookie

namespace Joomla\Input;

use Joomla\Input\Tests\CookieDataStore;

function setcookie($name, $value, $options = [])
{
    CookieDataStore::set($name, $value);

    return true;
}
