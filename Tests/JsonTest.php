<?php

/**
 * @copyright  Copyright (C) 2005 - 2022 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input\Tests;

use Joomla\Filter\InputFilter;
use Joomla\Input\Json;
use Joomla\Test\TestHelper;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\Input\Json.
 */
class JsonTest extends TestCase
{
    /**
     * @testdox  Tests the default constructor behavior
     *
     * @covers   Joomla\Input\Json
     * @uses     Joomla\Input\Input
     */
    public function test__constructDefaultBehaviour()
    {
        $instance = new Json();

        $this->assertEmpty(TestHelper::getValue($instance, 'data'), 'The JSON input defaults to php://input which should be empty in the test environment');
        $this->assertInstanceOf(InputFilter::class, TestHelper::getValue($instance, 'filter'), 'The Input object should create an InputFilter if one is not provided');
    }

    /**
     * @testdox  Tests the constructor with injected data
     *
     * @covers   Joomla\Input\Json
     * @uses     Joomla\Input\Input
     */
    public function test__constructDependencyInjection()
    {
        $src        = ['foo' => 'bar'];
        $mockFilter = $this->createMock(InputFilter::class);

        $instance = new Json($src, ['filter' => $mockFilter]);

        $this->assertSame($src, TestHelper::getValue($instance, 'data'));
        $this->assertSame($mockFilter, TestHelper::getValue($instance, 'filter'));
    }

    /**
     * @testdox  Tests the constructor when reading data from the $GLOBALS
     *
     * @covers   Joomla\Input\Json
     * @uses     Joomla\Input\Input
     *
     * @backupGlobals enabled
     */
    public function test__constructReadingFromGlobals()
    {
        $GLOBALS['HTTP_RAW_POST_DATA'] = '{"a":1,"b":2}';

        $instance = new Json();

        $this->assertSame(['a' => 1, 'b' => 2], TestHelper::getValue($instance, 'data'));
        $this->assertInstanceOf(InputFilter::class, TestHelper::getValue($instance, 'filter'), 'The Input object should create an InputFilter if one is not provided');
    }

    /**
     * @testdox  Tests the constructor when reading data from the $GLOBALS
     *
     * @covers   Joomla\Input\Json
     * @uses     Joomla\Input\Input
     *
     * @backupGlobals enabled
     */
    public function testgetRaw()
    {
        $GLOBALS['HTTP_RAW_POST_DATA'] = '{"a":1,"b":2}';

        $this->assertSame($GLOBALS['HTTP_RAW_POST_DATA'], (new Json())->getRaw());
    }

    #[TestDox('getRaw() with a name returns the unfiltered value')]
    public function testGetRawWithName(): void
    {
        $instance = new Json(['field' => '<b>value</b>', 'list' => [1, 2]]);

        $this->assertSame('<b>value</b>', $instance->getRaw('field'));
        $this->assertSame([1, 2], $instance->getRaw('list'));
        $this->assertSame('default', $instance->getRaw('missing', 'default'));
    }

    #[TestDox('getRaw() without a name is deprecated')]
    public function testGetRawWithoutNameIsDeprecated(): void
    {
        $this->expectUserDeprecationMessage(
            'Since joomla/input __DEPLOY_VERSION__: Calling Joomla\Input\Json::getRaw() without a name to get the request body is deprecated'
            . ' and will get an unfiltered value in 5.0. Use Joomla\Input\Json::getRawBody() instead.'
        );

        $this->assertNull((new Json(['field' => 'value']))->getRaw());
    }

    #[TestDox('getRawBody() returns null if the data was passed to the constructor')]
    public function testGetRawBodyWithSource(): void
    {
        $this->assertNull((new Json(['field' => 'value']))->getRawBody());
    }
}
