<?php

/**
 * @copyright  Copyright (C) 2005 - 2022 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input\Tests;

use Joomla\Filter\InputFilter;
use Joomla\Input\Input;
use Joomla\Input\Json;
use Joomla\Test\TestHelper;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\Input\Json.
 */
#[CoversClass(Json::class)]
#[UsesClass(Input::class)]
class JsonTest extends TestCase
{
    #[TestDox('Tests the default constructor behavior')]
    public function test__constructDefaultBehaviour()
    {
        $instance = new Json();

        $this->assertEmpty(TestHelper::getValue($instance, 'data'), 'The JSON input defaults to php://input which should be empty in the test environment');
        $this->assertInstanceOf(InputFilter::class, TestHelper::getValue($instance, 'filter'), 'The Input object should create an InputFilter if one is not provided');
    }

    #[TestDox('Tests the constructor with injected data')]
    public function test__constructDependencyInjection()
    {
        $src        = ['foo' => 'bar'];
        $stubFilter = $this->createStub(InputFilter::class);

        $instance = new Json($src, ['filter' => $stubFilter]);

        $this->assertSame($src, TestHelper::getValue($instance, 'data'));
        $this->assertSame($stubFilter, TestHelper::getValue($instance, 'filter'));
    }

    #[BackupGlobals(true)]
    #[TestDox('Tests the constructor when reading data from the $GLOBALS')]
    public function test__constructReadingFromGlobals()
    {
        $GLOBALS['HTTP_RAW_POST_DATA'] = '{"a":1,"b":2}';

        $instance = new Json();

        $this->assertSame(['a' => 1, 'b' => 2], TestHelper::getValue($instance, 'data'));
        $this->assertInstanceOf(InputFilter::class, TestHelper::getValue($instance, 'filter'), 'The Input object should create an InputFilter if one is not provided');
    }

    #[BackupGlobals(true)]
    #[TestDox('Tests the constructor when reading data from the $GLOBALS')]
    public function testgetRaw()
    {
        $GLOBALS['HTTP_RAW_POST_DATA'] = '{"a":1,"b":2}';

        $this->assertSame($GLOBALS['HTTP_RAW_POST_DATA'], (new Json())->getRaw());
    }
}
