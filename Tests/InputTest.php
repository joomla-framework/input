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
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Test class for \Joomla\Input\Input.
 */
#[AllowMockObjectsWithoutExpectations]
#[CoversClass(Input::class)]
#[UsesClass(Files::class)]
class InputTest extends TestCase
{
    /**
     * The mock filter object
     *
     * @var  InputFilter|MockObject
     */
    private $filterMock;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->filterMock = $this->createMock(InputFilter::class);
    }

    /**
     * Get an Input object populated with passed in data
     *
     * @param   array|null  $data  Optional source data. If omitted, a copy of the server variable '_REQUEST' is used.
     *
     * @return  Input
     */
    protected function getInputObject(?array $data = null): Input
    {
        return new Input($data, ['filter' => $this->filterMock]);
    }

    /**
     * @throws \ReflectionException
     */
    #[TestDox('Default constructor behavior')]
    public function test__constructDefaultBehaviour(): void
    {
        $instance = new Input();

        $this->assertSame($_REQUEST, TestHelper::getValue($instance, 'data'), 'The Input input defaults to the $_REQUEST superglobal');
        $this->assertInstanceOf(InputFilter::class, TestHelper::getValue($instance, 'filter'), 'The Input object should create an InputFilter if one is not provided');
    }

    /**
     * @throws \ReflectionException
     */
    #[TestDox('Constructor with injected data')]
    public function test__constructDependencyInjection(): void
    {
        $instance = $this->getInputObject($_GET);

        $this->assertSame($_GET, TestHelper::getValue($instance, 'data'));
        $this->assertSame($this->filterMock, TestHelper::getValue($instance, 'filter'));
    }

    #[TestDox('Convenience methods are proxied')]
    public function test__callProxiesToTheGetMethod(): void
    {
        $this->filterMock->expects($this->once())
            ->method('clean')
            ->willReturnArgument(0);

        $instance = $this->getInputObject(['foo' => 'bar']);

        $this->assertSame('bar', $instance->getRaw('foo'));
    }

    #[TestDox('An error is thrown if an undefined method is called')]
    public function test__callThrowsAnErrorIfAnUndefinedMethodIsCalled(): void
    {
        $this->expectException(Throwable::class);

        $this->getInputObject()->setRaw();
    }

    /**
     * @throws \ReflectionException
     */
    #[TestDox('Magic get method correctly proxies to another global')]
    public function test__get(): void
    {
        $instance = $this->getInputObject();

        $this->assertSame($_GET, TestHelper::getValue($instance->get, 'data'));
        $this->assertArrayHasKey('get', TestHelper::getValue($instance, 'inputs'), 'An object retrieved via __get() should be cached internally');
    }

    #[TestDox('Magic get method correctly proxies to another global represented by the Input class and returns the same instance')]
    public function test__getCachedInstances(): void
    {
        $instance = $this->getInputObject();

        $this->assertSame($instance->get, $instance->get, 'The same Input instance should be returned');
    }

    #[TestDox('Magic get method correctly proxies to another global represented by an Input subclass and returns the same instance')]
    public function test__getCachedInstancesSubclasses(): void
    {
        $instance = $this->getInputObject();

        $this->assertSame($instance->files, $instance->files, 'The same Files instance should be returned');
    }

    #[TestDox('An error is thrown if an undefined property is called')]
    public function test__getThrowsAnErrorIfAnUndefinedPropertyIsCalled(): void
    {
        $this->expectException(Throwable::class);

        /** @noinspection PhpUndefinedFieldInspection */
        $this->getInputObject()->put;
    }

    #[TestDox('Data store is counted')]
    public function testCount(): void
    {
        $this->assertCount(3, $this->getInputObject(['foo' => 2, 'bar' => 3, 'gamma' => 4]));
    }

    #[TestDox('Data source is correctly read')]
    public function testGet(): void
    {
        $this->filterMock->expects($this->once())
            ->method('clean')
            ->willReturnArgument(0);

        $instance = $this->getInputObject(['foo' => 'bar']);

        $this->assertEquals('bar', $instance->get('foo'));
    }

    #[TestDox('A key is not redefined if already present')]
    public function testDefNotReadWhenValueExists(): void
    {
        $this->filterMock->expects($this->once())
            ->method('clean')
            ->willReturnArgument(0);

        $instance = $this->getInputObject(['foo' => 'bar']);

        $instance->def('foo', 'nope');

        $this->assertEquals('bar', $instance->get('foo'));
    }

    #[TestDox('A key is defined when not present')]
    public function testDefRead(): void
    {
        $this->filterMock->expects($this->once())
            ->method('clean')
            ->willReturnArgument(0);

        $instance = $this->getInputObject(['foo' => 'bar']);

        $instance->def('bar', 'nope');

        $this->assertEquals('nope', $instance->get('bar'));
    }

    #[TestDox('A key is added or overwritten in the data source')]
    public function testSet(): void
    {
        $this->filterMock->expects($this->once())
            ->method('clean')
            ->willReturnArgument(0);

        $instance = $this->getInputObject(['foo' => 'bar']);

        $instance->set('foo', 'gamma');

        $this->assertEquals('gamma', $instance->get('foo'));
    }

    #[TestDox("For a key's existence in the data source")]
    public function testExists(): void
    {
        $instance = $this->getInputObject(['foo' => 'bar']);

        $this->assertTrue($instance->exists('foo'));
    }

    #[TestDox('An array of keys is read from the data source')]
    public function testGetArray(): void
    {
        $this->filterMock
            ->method('clean')
            ->willReturnArgument(0);

        $array = [
            'var1' => 'value1',
            'var2' => 34,
            'var3' => ['test'],
            'var4' => ['var1' => ['var2' => 'test']],
        ];

        $input = $this->getInputObject($array);

        $this->assertEquals(
            $array,
            $input->getArray(
                ['var1' => 'string', 'var2' => 'int', 'var3' => 'array', 'var4' => ['var1' => ['var2' => 'array']]]
            )
        );
    }

    #[TestDox('Full data array is read from the data source')]
    public function testGetArrayWithoutSpecifiedVariables(): void
    {
        $this->filterMock
            ->method('clean')
            ->willReturnArgument(0);

        $array = [
            'var2' => 34,
            'var3' => ['var2' => 'test'],
            'var4' => ['var1' => ['var2' => 'test']],
            'var5' => ['foo' => []],
            'var6' => ['bar' => null],
            'var7' => null,
        ];

        $input = $this->getInputObject($array);

        $this->assertEquals($input->getArray(), $array);
    }

    #[BackupGlobals(true)]
    #[TestDox('Request method is returned')]
    public function testGetMethod(): void
    {
        $this->filterMock
            ->method('clean')
            ->willReturnArgument(0);

        $_SERVER['REQUEST_METHOD'] = 'custom';

        $instance = $this->getInputObject();

        $this->assertEquals('CUSTOM', $instance->getMethod());
    }

    #[BackupGlobals(true)]
    #[TestDox('Input object for the request method is returned on a GET request')]
    public function testGetInputForRequestMethodWithGetRequest(): void
    {
        $this->filterMock
            ->method('clean')
            ->willReturnArgument(0);

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $instance = $this->getInputObject();

        $this->assertInstanceOf(Input::class, $instance->getInputForRequestMethod());
        $this->assertSame($instance->get, $instance->getInputForRequestMethod(), 'A request method that does have its own superglobal returns the Input object for that global');
    }

    #[BackupGlobals(true)]
    #[TestDox('Input object for the request method is returned on a POST request')]
    public function testGetInputForRequestMethodWithPostRequest(): void
    {
        $this->filterMock
            ->method('clean')
            ->willReturnArgument(0);

        $_SERVER['REQUEST_METHOD'] = 'POST';

        $instance = $this->getInputObject();

        $this->assertInstanceOf(Input::class, $instance->getInputForRequestMethod());
        $this->assertSame($instance->post, $instance->getInputForRequestMethod(), 'A request method that does have its own superglobal returns the Input object for that global');
    }

    #[BackupGlobals(true)]
    #[TestDox('Input object for the request method is returned on a PUT request')]
    public function testGetInputForRequestMethodWithPutRequest(): void
    {
        $this->filterMock
            ->method('clean')
            ->willReturnArgument(0);

        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $instance = $this->getInputObject();

        $this->assertInstanceOf(Input::class, $instance->getInputForRequestMethod());
        $this->assertSame($instance, $instance->getInputForRequestMethod(), 'A request method that does not have its own superglobal returns the current Input object');
    }

    #[TestDox('Get method disallows access to non-whitelisted globals')]
    public function testGetDoesNotSupportNonWhitelistedGlobals(): void
    {
        $this->expectException(Throwable::class);

        $this->getInputObject()->_phpunit_configuration_file;
    }

    public static function constructorCasesProvider(): array
    {
        return [
            'no source' => [
                'constructorArgs' => null,
                'expected'        => 'value',
            ],
            'empty source' => [
                'constructorArgs' => [],
                'expected'        => null,
            ],
            'non-empty source' => [
                'constructorArgs' => ['foo' => 'bar'],
                'expected'        => null,
            ],
            'same key' => [
                'constructorArgs' => ['var' => 'bar'],
                'expected'        => 'bar',
            ],
        ];
    }

    /**
     * @return void
     */
    #[BackupGlobals(true)]
    #[DataProvider('constructorCasesProvider')]
    #[TestDox('If no source is provided ($source === null), $_REQUEST is used. If any source is provided ($source !== null), $_REQUEST is ignored.')]
    public function testConstructorUsesRequestIfNeeded($constructorArgs, $expected): void
    {
        $_REQUEST = ['var' => 'value'];

        $input = new Input($constructorArgs);

        $this->assertEquals($expected, $input->get('var'));
    }

    #[BackupGlobals(true)]
    #[TestDox('Input object for the request method GET is not polluted with POST data')]
    public function testGetRequestForPostData(): void
    {
        $_POST    = ['polluted' => '1'];
        $_GET     = [];
        $_REQUEST = array_merge($_GET, $_POST);

        $input = new Input($_GET);

        $this->assertEquals(0, $input->get->count(), 'get is being polluted by the post!');
    }

    #[BackupGlobals(true)]
    #[TestDox('Input object for the request method POST is not polluted with GET data')]
    public function testPostRequestForGetData(): void
    {
        $_GET     = ['polluted' => '1'];
        $_POST    = [];
        $_REQUEST = array_merge($_GET, $_POST);

        $input = new Input($_POST);

        $this->assertEquals(0, $input->post->count(), 'post is being polluted by the get!');
    }

    #[BackupGlobals(true)]
    #[TestDox('GET and POST data are kept separate')]
    public function testRequestFromGlobals(): void
    {
        $_GET     = ['1' => '1', '2' => '2', '3' => '3'];
        $_POST    = ['1' => '1', '2' => '2'];
        $_REQUEST = array_merge($_GET, $_POST);

        $input = new Input();

        $this->assertEquals(
            3,
            $input->get->count(),
            'Wrong number of items found in the $_GET in the input object when loading from GLOBALS'
        );
        $this->assertEquals(
            2,
            $input->post->count(),
            'Wrong number of items found in the $_POST in the input object when loading from GLOBALS'
        );
    }
}
