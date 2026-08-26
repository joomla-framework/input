<?php

/**
 * @copyright  Copyright (C) 2005 - 2022 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input\Tests;

use Joomla\Filter\InputFilter;
use Joomla\Input\Files;
use Joomla\Input\Input;
use Joomla\Test\TestHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\Input\Files.
 */
#[CoversClass(Files::class)]
#[UsesClass(Input::class)]
class FilesTest extends TestCase
{
    #[TestDox('Tests the default constructor behavior')]
    public function test__constructDefaultBehaviour()
    {
        $instance = new Files();

        $this->assertSame($_FILES, TestHelper::getValue($instance, 'data'), 'The Files input defaults to the $_FILES superglobal');
        $this->assertInstanceOf(InputFilter::class, TestHelper::getValue($instance, 'filter'), 'The Input object should create an InputFilter if one is not provided');
    }

    #[TestDox('Tests the constructor with injected data')]
    public function test__constructDependencyInjection()
    {
        $src        = ['foo' => 'bar'];
        $stubFilter = $this->createStub(InputFilter::class);

        $instance = new Files($src, ['filter' => $stubFilter]);

        $this->assertSame($src, TestHelper::getValue($instance, 'data'));
        $this->assertSame($stubFilter, TestHelper::getValue($instance, 'filter'));
    }

    #[TestDox('Tests the data source is correctly read')]
    public function testGet()
    {
        $data = [
            'myfile' => [
                'name'     => 'n',
                'type'     => 'ty',
                'tmp_name' => 'tm',
                'error'    => 'e',
                'size'     => 's',
            ],
            'myfile2' => [
                'name'     => 'nn',
                'type'     => 'ttyy',
                'tmp_name' => 'ttmm',
                'error'    => 'ee',
                'size'     => 'ss',
            ],
        ];

        $stubFilter = $this->createStub(InputFilter::class);

        $instance = new Files($data, ['filter' => $stubFilter]);

        $this->assertSame('foobar', $instance->get('myfile3', 'foobar'), 'The default value is returned if data does not exist.');

        $this->assertSame(
            [
                'name'     => 'n',
                'type'     => 'ty',
                'tmp_name' => 'tm',
                'error'    => 'e',
                'size'     => 's',
            ],
            $instance->get('myfile')
        );
    }

    #[TestDox('Tests a multi-level data source is correctly read')]
    public function testGetWithMultiLevelData()
    {
        $dataArr = ['first', 'second'];
        $data    = [
            'myfile' => [
                'name'     => $dataArr,
                'type'     => $dataArr,
                'tmp_name' => $dataArr,
                'error'    => $dataArr,
                'size'     => $dataArr,
            ],
        ];

        $stubFilter = $this->createStub(InputFilter::class);

        $instance = new Files($data, ['filter' => $stubFilter]);

        $this->assertSame(
            [
                [
                    'name'     => 'first',
                    'type'     => 'first',
                    'tmp_name' => 'first',
                    'error'    => 'first',
                    'size'     => 'first',
                ],
                [
                    'name'     => 'second',
                    'type'     => 'second',
                    'tmp_name' => 'second',
                    'error'    => 'second',
                    'size'     => 'second',
                ],
            ],
            $instance->get('myfile')
        );
    }

    #[TestDox('Tests the data source cannot be modified')]
    public function testSet()
    {
        $instance = new Files();
        $instance->set('foo', 'bar');

        $this->assertFalse($instance->exists('foo'));
    }
}
