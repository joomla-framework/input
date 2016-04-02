<?php
/**
 * Part of the Joomla Framework Input Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input;

/**
 * Joomla! Input Files Class
 *
 * @since  1.0
 */
class PHPInput extends Input
{
	/**
	 * Constructor.
	 *
	 * @param   array  $source   Source data (Optional, default is the raw HTTP input decoded from JSON)
	 * @param   array  $options  Array of configuration parameters (Optional)
	 *
	 * @since   1.0
	 */
	public function __construct(array $source = null, array $options = array())
	{
		if (is_null($source))
		{
			$inputData = file_get_contents('php://input');

			// This is a workaround for where php://input has already been read.
			// See note under php://input on http://php.net/manual/en/wrappers.php.php
			if (empty($inputData) && isset($GLOBALS['HTTP_RAW_POST_DATA']))
			{
				$inputData = $GLOBALS['HTTP_RAW_POST_DATA'];
			}

			parse_str($inputData, $data);
		}
		else
		{
			$data = $source;
		}

		parent::__construct($data, $options);
	}
}
