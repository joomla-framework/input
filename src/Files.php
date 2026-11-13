<?php

/**
 * Part of the Joomla Framework Input Package
 *
 * @copyright  Copyright (C) 2005 - 2022 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input;

/**
 * Joomla! Input Files Class
 *
 * @since  1.0
 */
class Files extends Input
{
    /**
     * The pivoted data from a $_FILES or compatible array.
     *
     * @var    array
     * @since  1.0
     */
    protected array $decodedData = [];

    /**
     * The class constructor.
     *
     * @param   array|null  $source   Source data (Optional, default is $_FILES)
     * @param   array       $options  Array of configuration parameters (Optional)
     *
     * @since   1.0
     */
    public function __construct(?array $source = null, array $options = [])
    {
        $source = $source ?? $_FILES;
        parent::__construct($source, $options);
    }

    /**
     * Gets a value from the input data.
     *
     * @param   string  $name     The name of the input property (usually the name of the files INPUT tag) to get.
     * @param   mixed   $default  The default value to return if the named property does not exist.
     * @param   string  $filter   The filter to apply to the value.
     *
     * @return  mixed  The filtered input value.
     *
     * @see     \Joomla\Filter\InputFilter::clean()
     * @since   1.0
     */
    public function get(string $name, mixed $default = null, $filter = 'cmd'): mixed
    {
        if (isset($this->data[$name])) {
            $results = $this->decodeData(
                [
                    $this->data[$name]['name'],
                    $this->data[$name]['type'],
                    $this->data[$name]['tmp_name'],
                    $this->data[$name]['error'],
                    $this->data[$name]['size'],
                ]
            );

            return $results;
        }

        return $default;
    }

    /**
     * Method to decode a data array.
     *
     * @param   array  $data  The data array to decode.
     *
     * @return  array
     *
     * @since   1.0
     */
    protected function decodeData(array $data): array
    {
        $result = [];

        if (\is_array($data[0])) {
            foreach ($data[0] as $k => $v) {
                $result[$k] = $this->decodeData([$data[0][$k], $data[1][$k], $data[2][$k], $data[3][$k], $data[4][$k]]);
            }

            return $result;
        }

        return ['name' => $data[0], 'type' => $data[1], 'tmp_name' => $data[2], 'error' => $data[3], 'size' => $data[4]];
    }

    /**
     * Sets a value.
     *
     * Uploaded files cannot be changed.
     *
     * @param   string  $name   The name of the input property to set.
     * @param   mixed   $value  The value to assign to the input property.
     *
     * @return  void
     *
     * @throws  \LogicException  Always.
     * @since   1.0
     */
    public function set(string $name, mixed $value)
    {
        throw new \LogicException(\sprintf('%s() is not supported, uploaded files cannot be changed.', __METHOD__));
    }

    /**
     * Defines a value.
     *
     * Uploaded files cannot be changed.
     *
     * @param   string  $name   The name of the input property to define.
     * @param   mixed   $value  The value to assign to the input property.
     *
     * @return  void
     *
     * @throws  \LogicException  Always.
     * @since   __DEPLOY_VERSION__
     */
    public function def(string $name, mixed $value): void
    {
        throw new \LogicException(\sprintf('%s() is not supported, uploaded files cannot be changed.', __METHOD__));
    }
}
