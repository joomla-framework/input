<?php

/**
 * @copyright  Copyright (C) 2005 - 2022 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input\Tests;

use Joomla\Filter\InputFilter;
use Joomla\Input\Files;
use Joomla\Test\TestHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\Input\Files.
 */
class FilesTest extends TestCase
{
    /**
     * @testdox  Tests the default constructor behavior
     *
     * @covers   Joomla\Input\Files
     * @uses     Joomla\Input\Input
     */
    public function test__constructDefaultBehaviour()
    {
        $instance = new Files();

        $this->assertSame($_FILES, TestHelper::getValue($instance, 'data'), 'The Files input defaults to the $_FILES superglobal');
        $this->assertInstanceOf(InputFilter::class, TestHelper::getValue($instance, 'filter'), 'The Input object should create an InputFilter if one is not provided');
    }

    /**
     * @testdox  Tests the constructor with injected data
     *
     * @covers   Joomla\Input\Files
     * @uses     Joomla\Input\Input
     */
    public function test__constructDependencyInjection()
    {
        $src        = ['foo' => 'bar'];
        $mockFilter = $this->createMock(InputFilter::class);

        $instance = new Files($src, ['filter' => $mockFilter]);

        $this->assertSame($src, TestHelper::getValue($instance, 'data'));
        $this->assertSame($mockFilter, TestHelper::getValue($instance, 'filter'));
    }

    /**
     * @testdox  Tests the data source is correctly read
     *
     * @covers   Joomla\Input\Files
     * @uses     Joomla\Input\Input
     */
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

        $mockFilter = $this->createMock(InputFilter::class);

        $instance = new Files($data, ['filter' => $mockFilter]);

        $this->assertEquals('foobar', $instance->get('myfile3', 'foobar'), 'The default value is returned if data does not exist.');

        $this->assertEquals(
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

    /**
     * @testdox  Tests a multi-level data source is correctly read
     *
     * @covers   Joomla\Input\Files
     * @uses     Joomla\Input\Input
     */
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

        $mockFilter = $this->createMock(InputFilter::class);

        $instance = new Files($data, ['filter' => $mockFilter]);

        $this->assertEquals(
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

    /**
     * @testdox  Tests the data source cannot be modified
     *
     * @covers   Joomla\Input\Files
     * @uses     Joomla\Input\Input
     */
    public function testSet()
    {
        $instance = new Files();
        $instance->set('foo', 'bar');

        $this->assertFalse($instance->exists('foo'));
    }

    #[TestDox('Calling set() triggers a deprecation')]
    public function testSetIsDeprecated(): void
    {
        $this->expectUserDeprecationMessage(
            'Since joomla/input __DEPLOY_VERSION__: Calling Joomla\Input\Files::set() has no effect and is deprecated, it will throw a LogicException in 5.0.'
        );

        (new Files([]))->set('foo', 'bar');
    }

    #[TestDox('The data source cannot be modified through def()')]
    public function testDef(): void
    {
        $this->expectUserDeprecationMessage(
            'Since joomla/input __DEPLOY_VERSION__: Calling Joomla\Input\Files::def() has no effect and is deprecated, it will throw a LogicException in 5.0.'
        );

        $instance = new Files([]);
        $instance->def('fake', ['name' => 'f', 'type' => 't', 'tmp_name' => '/etc/passwd', 'error' => 0, 'size' => 1]);

        $this->assertFalse($instance->exists('fake'));
        $this->assertNull($instance->get('fake'));
    }

    public static function malformedEntriesProvider(): array
    {
        return [
            'string'                   => ['not a file'],
            'missing keys'             => [['name' => 'a']],
            'list in name only'        => [['name' => ['a'], 'type' => 't', 'tmp_name' => 'tm', 'error' => 0, 'size' => 1]],
            'scalar in name, list else' => [['name' => 'a', 'type' => ['t'], 'tmp_name' => ['tm'], 'error' => [0], 'size' => [1]]],
        ];
    }

    #[DataProvider('malformedEntriesProvider')]
    #[TestDox('The default value is returned for a value that is not a file entry')]
    public function testGetMalformedEntry(mixed $entry): void
    {
        $instance = new Files(['myfile' => $entry]);

        $this->assertSame('default', $instance->get('myfile', 'default'));
    }

    #[TestDox('The full_path of directory uploads is returned')]
    public function testGetFullPath(): void
    {
        $instance = new Files(
            [
                'single' => ['name' => 'a.txt', 'type' => 't', 'tmp_name' => 'tm', 'error' => 0, 'size' => 1, 'full_path' => 'dir/a.txt'],
                'multi'  => [
                    'name'      => ['a.txt', 'b.txt'],
                    'type'      => ['t', 't'],
                    'tmp_name'  => ['tm1', 'tm2'],
                    'error'     => [0, 0],
                    'size'      => [1, 2],
                    'full_path' => ['dir/a.txt', 'dir/sub/b.txt'],
                ],
            ]
        );

        $this->assertSame(
            ['name' => 'a.txt', 'type' => 't', 'tmp_name' => 'tm', 'error' => 0, 'size' => 1, 'full_path' => 'dir/a.txt'],
            $instance->get('single')
        );
        $this->assertSame('dir/sub/b.txt', $instance->get('multi')[1]['full_path']);
    }

    public static function fileNamesProvider(): array
    {
        return [
            'plain'             => ['image.png', 'image.png'],
            'traversal'         => ['../../a.php', 'a.php'],
            'windows path'      => ['C:\\temp\\evil.php', 'evil.php'],
            'hidden file'       => ['.htaccess', 'htaccess'],
            'dot sequence'      => ['a..b.txt', 'ab.txt'],
            'special chars'     => ['<script>"x".php', 'scriptx.php'],
            'unicode and space' => ['Über Datei.pdf', 'Über Datei.pdf'],
            'nothing left'      => ['../', ''],
        ];
    }

    #[DataProvider('fileNamesProvider')]
    #[TestDox('A file name is made safe')]
    public function testMakeSafeFileName(string $fileName, string $expected): void
    {
        $this->assertSame($expected, (new Files([]))->makeSafeFileName($fileName));
    }
}
