<?php

/**
 * Part of the Joomla Framework Input Package
 *
 * @copyright  Copyright (C) 2005 - 2022 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input;

/**
 * Joomla! Input JSON Class
 *
 * This class decodes a JSON string from the raw request data and makes it available via the standard Input interface.
 *
 * @since  1.0
 */
class Json extends Input
{
    /**
     * The raw JSON string from the request.
     *
     * @var    string|null
     * @since  1.0
     */
    private $raw;

    /**
     * Constructor.
     *
     * @param   array|null  $source   Source data (Optional, default is the raw HTTP input decoded from JSON)
     * @param   array       $options  Array of configuration parameters (Optional)
     *
     * @since   1.0
     */
    public function __construct($source = null, array $options = [])
    {
        if ($source === null) {
            $this->raw = file_get_contents('php://input');

            // This is a workaround for where php://input has already been read.
            // See note under php://input on https://www.php.net/manual/en/wrappers.php.php
            if (empty($this->raw) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
                $this->raw = $GLOBALS['HTTP_RAW_POST_DATA'];
            }

            $source = json_decode($this->raw, true);

            if (!\is_array($source)) {
                $source = [];
            }
        }

        parent::__construct($source, $options);
    }

    /**
     * Gets an unfiltered value, or the raw JSON string from the request when called without a name.
     *
     * Calling this method without a name is deprecated, use getRawBody() instead.
     *
     * @param   string|null  $name     Name of the value to get.
     * @param   mixed        $default  Default value to return if the variable does not exist.
     *
     * @return  mixed  The unfiltered value, or the raw JSON string from the request when no name is given.
     *
     * @since   1.0
     */
    public function getRaw(?string $name = null, mixed $default = null)
    {
        if ($name !== null) {
            return $this->get($name, $default, 'raw');
        }

        trigger_deprecation(
            'joomla/input',
            '__DEPLOY_VERSION__',
            'Calling %s() without a name to get the request body is deprecated and will get an unfiltered value in 5.0.'
            . ' Use %s::getRawBody() instead.',
            __METHOD__,
            self::class
        );

        return $this->raw;
    }

    /**
     * Gets the raw JSON string from the request.
     *
     * @return  string|null  The raw JSON string from the request, or null if the data was passed to the constructor.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getRawBody(): ?string
    {
        return $this->raw;
    }
}
