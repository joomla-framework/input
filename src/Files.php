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
    protected $decodedData = [];

    /**
     * The class constructor.
     *
     * @param   array|null  $source   Source data (Optional, default is $_FILES)
     * @param   array       $options  Array of configuration parameters (Optional)
     *
     * @since   1.0
     */
    public function __construct($source = null, array $options = [])
    {
        $source = $source ?? $_FILES;
        parent::__construct($source, $options);
    }

    /**
     * The keys every entry of a $_FILES or compatible array has.
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    private const REQUIRED_KEYS = ['name', 'type', 'tmp_name', 'error', 'size'];

    /**
     * Gets a value from the input data.
     *
     * The values are returned as uploaded, they are not filtered. The client controls `name`, `type` and `full_path`;
     * use makeSafeFileName() before using the name in a path.
     *
     * @param   string  $name     The name of the input property (usually the name of the files INPUT tag) to get.
     * @param   mixed   $default  The default value to return if the named property does not exist or is not a valid
     *                            file entry.
     * @param   string  $filter   Not used, the values are never filtered.
     *
     * @return  mixed  The file entry (a list of entries for multi-file inputs), or the default value.
     *
     * @since   1.0
     */
    public function get($name, $default = null, $filter = 'cmd')
    {
        if (isset($this->data[$name]) && $this->isFileEntry($this->data[$name])) {
            $data = [
                $this->data[$name]['name'],
                $this->data[$name]['type'],
                $this->data[$name]['tmp_name'],
                $this->data[$name]['error'],
                $this->data[$name]['size'],
            ];

            if (\array_key_exists('full_path', $this->data[$name])) {
                $data[] = $this->data[$name]['full_path'];
            }

            return $this->decodeData($data);
        }

        return $default;
    }

    /**
     * Returns a file name that is safe to use in a path.
     *
     * Removes any directory part, all characters other than letters, digits, `.`, `_`, `-` and space, sequences of
     * dots and leading dots.
     *
     * @param   string  $fileName  The file name, usually the client-supplied `name` of an uploaded file.
     *
     * @return  string  The cleaned file name. May be empty.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function makeSafeFileName(string $fileName): string
    {
        // Remove the directory part, whatever the separator
        $fileName = (string) preg_replace('#^.*[/\\\\]#s', '', $fileName);

        return trim((string) preg_replace(['#[^\p{L}\p{N}._\- ]#u', '#\.{2,}#', '#^\.+#'], '', $fileName));
    }

    /**
     * Checks that a value has the structure of an entry in a $_FILES or compatible array.
     *
     * @param   mixed  $entry  The value to check.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    private function isFileEntry(mixed $entry): bool
    {
        if (!\is_array($entry)) {
            return false;
        }

        foreach (self::REQUIRED_KEYS as $key) {
            if (!\array_key_exists($key, $entry) || \is_array($entry[$key]) !== \is_array($entry['name'])) {
                return false;
            }
        }

        return true;
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
    protected function decodeData(array $data)
    {
        $result = [];

        if (\is_array($data[0])) {
            foreach ($data[0] as $k => $v) {
                $entry = [$data[0][$k], $data[1][$k], $data[2][$k], $data[3][$k], $data[4][$k]];

                if (\array_key_exists(5, $data)) {
                    $entry[] = $data[5][$k] ?? null;
                }

                $result[$k] = $this->decodeData($entry);
            }

            return $result;
        }

        $result = ['name' => $data[0], 'type' => $data[1], 'tmp_name' => $data[2], 'error' => $data[3], 'size' => $data[4]];

        if (\array_key_exists(5, $data)) {
            $result['full_path'] = $data[5];
        }

        return $result;
    }

    /**
     * Sets a value.
     *
     * Uploaded files cannot be changed, so this method does nothing.
     *
     * @param   string  $name   The name of the input property to set.
     * @param   mixed   $value  The value to assign to the input property.
     *
     * @return  void
     *
     * @since       1.0
     * @deprecated  5.0  Will throw a LogicException, uploaded files cannot be changed.
     */
    public function set($name, $value)
    {
        trigger_deprecation(
            'joomla/input',
            '__DEPLOY_VERSION__',
            'Calling %s() has no effect and is deprecated, it will throw a LogicException in 5.0.',
            __METHOD__
        );
    }

    /**
     * Defines a value.
     *
     * Uploaded files cannot be changed, so this method does nothing.
     *
     * @param   string  $name   The name of the input property to define.
     * @param   mixed   $value  The value to assign to the input property.
     *
     * @return  void
     *
     * @since       __DEPLOY_VERSION__
     * @deprecated  5.0  Will throw a LogicException, uploaded files cannot be changed.
     */
    public function def($name, $value)
    {
        trigger_deprecation(
            'joomla/input',
            '__DEPLOY_VERSION__',
            'Calling %s() has no effect and is deprecated, it will throw a LogicException in 5.0.',
            __METHOD__
        );
    }
}
