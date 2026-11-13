<?php

/**
 * @copyright  Copyright (C) 2005 - 2022 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input\Tests;

use Joomla\Filter\InputFilter;
use Joomla\Input\Json;
use Joomla\Test\TestHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Replaces the php:// stream wrapper so that php://input returns a given body.
 */
class PhpInputStream
{
    public static string $body = '';

    public $context;

    private int $position = 0;

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        return $path === 'php://input';
    }

    public function stream_read(int $count): string
    {
        $chunk           = substr(self::$body, $this->position, $count);
        $this->position += \strlen($chunk);

        return $chunk;
    }

    public function stream_eof(): bool
    {
        return $this->position >= \strlen(self::$body);
    }

    public function stream_stat(): array
    {
        return [];
    }

    /**
     * Creates a Json input with the given request body.
     */
    public static function createJson(string $body): Json
    {
        self::$body = $body;

        stream_wrapper_unregister('php');
        stream_wrapper_register('php', self::class);

        try {
            return new Json();
        } finally {
            stream_wrapper_restore('php');
        }
    }
}

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

    #[TestDox('Tests the constructor when reading data from the request body')]
    public function test__constructReadingFromRequestBody()
    {
        $instance = PhpInputStream::createJson('{"a":1,"b":2}');

        $this->assertSame(['a' => 1, 'b' => 2], TestHelper::getValue($instance, 'data'));
        $this->assertInstanceOf(InputFilter::class, TestHelper::getValue($instance, 'filter'), 'The Input object should create an InputFilter if one is not provided');
        $this->assertFalse($instance->hasError());
        $this->assertNull($instance->getError());
    }

    #[TestDox('HTTP_RAW_POST_DATA is not read')]
    public function test__constructIgnoresRawPostData()
    {
        $GLOBALS['HTTP_RAW_POST_DATA'] = '{"a":1,"b":2}';

        try {
            $instance = PhpInputStream::createJson('');
        } finally {
            unset($GLOBALS['HTTP_RAW_POST_DATA']);
        }

        $this->assertSame([], TestHelper::getValue($instance, 'data'));
        $this->assertSame('', $instance->getRaw());
    }

    public static function emptyBodiesProvider(): array
    {
        return [
            'empty'      => [''],
            'whitespace' => [" \n "],
        ];
    }

    #[DataProvider('emptyBodiesProvider')]
    #[TestDox('An empty request body is not an error')]
    public function testEmptyBodyIsNoError(string $body)
    {
        $instance = PhpInputStream::createJson($body);

        $this->assertSame([], TestHelper::getValue($instance, 'data'));
        $this->assertFalse($instance->hasError());
    }

    public static function invalidBodiesProvider(): array
    {
        return [
            'syntax error' => ['{"a":', 'Syntax error'],
            'scalar'       => ['42', 'The request body is not a JSON object or array'],
            'null'         => ['null', 'The request body is not a JSON object or array'],
        ];
    }

    #[DataProvider('invalidBodiesProvider')]
    #[TestDox('A request body that cannot be decoded is reported')]
    public function testInvalidBodyIsReported(string $body, string $message)
    {
        $instance = PhpInputStream::createJson($body);

        $this->assertSame([], TestHelper::getValue($instance, 'data'));
        $this->assertTrue($instance->hasError());
        $this->assertSame($message, $instance->getError());
    }

    #[TestDox('Tests getting the raw request body')]
    public function testgetRaw()
    {
        $this->assertSame('{"a":1,"b":2}', PhpInputStream::createJson('{"a":1,"b":2}')->getRaw());
    }
}
