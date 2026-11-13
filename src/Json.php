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
     * @var    string
     * @since  1.0
     */
    private $raw;

    /**
     * The error message if the request body could not be decoded.
     *
     * @var    string|null
     * @since  __DEPLOY_VERSION__
     */
    private ?string $error = null;

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
            $this->raw = (string) file_get_contents('php://input');
            $source    = $this->decode($this->raw);
        }

        parent::__construct($source, $options);
    }

    /**
     * Checks whether the request body could not be decoded.
     *
     * An empty body is not an error. A body that is not valid JSON, or that is valid JSON but not an object or an
     * array, is.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    public function hasError(): bool
    {
        return $this->error !== null;
    }

    /**
     * Gets the error message if the request body could not be decoded.
     *
     * @return  string|null  The error message, or null if there was no error.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Decodes the request body and records any error.
     *
     * @param   string  $raw  The request body.
     *
     * @return  array  The decoded data, an empty array on error.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function decode(string $raw): array
    {
        if (trim($raw) === '') {
            return [];
        }

        try {
            $data = json_decode($raw, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->error = $e->getMessage();

            return [];
        }

        if (!\is_array($data)) {
            $this->error = 'The request body is not a JSON object or array';

            return [];
        }

        return $data;
    }

    /**
     * Gets the raw JSON string from the request.
     *
     * @return  string  The raw JSON string from the request.
     *
     * @since   1.0
     */
    public function getRaw()
    {
        return $this->raw;
    }
}
