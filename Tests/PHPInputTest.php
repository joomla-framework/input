<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input\Tests;

use Joomla\Input\PHPInput;

/**
 * Test class for \Joomla\Input\Json.
 */
class PHPInputTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @testdox  Tests the default constructor behavior
	 *
	 * @covers   Joomla\Input\PHPInput::__construct
	 */
	public function test__constructDefaultBehaviour()
	{
		$instance = new PHPInput;

		$this->assertAttributeEmpty('data', $instance);
		$this->assertAttributeInstanceOf('Joomla\Filter\InputFilter', 'filter', $instance);
	}

	/**
	 * @testdox  Tests the constructor with injected data
	 *
	 * @covers   Joomla\Input\PHPInput::__construct
	 */
	public function test__constructDependencyInjection()
	{
		$src        = ['foo' => 'bar'];
		$mockFilter = $this->getMock('Joomla\Filter\InputFilter');

		$instance = new PHPInput($src, ['filter' => $mockFilter]);

		$this->assertAttributeSame($src, 'data', $instance);
		$this->assertAttributeSame($mockFilter, 'filter', $instance);
	}

	/**
	 * @testdox  Tests the constructor when reading data from the $GLOBALS
	 *
	 * @covers   Joomla\Input\PHPInput::__construct
	 *
	 * @backupGlobals enabled
	 */
	public function test__constructReadingFromGlobals()
	{
		$GLOBALS['HTTP_RAW_POST_DATA'] = 'a=1&b=two';

		$instance = new PHPInput;

		$this->assertAttributeSame(['a' => '1', 'b' => 'two'], 'data', $instance);
	}
}
