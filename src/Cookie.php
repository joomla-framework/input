<?php

/**
 * Part of the Joomla Framework Input Package
 *
 * @copyright  Copyright (C) 2005 - 2022 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input;

/**
 * Joomla! Input Cookie Class
 *
 * @since  1.0
 */
class Cookie extends Input
{
    /**
     * Constructor.
     *
     * @param   array|null  $source   Source data (Optional, default is $_COOKIE)
     * @param   array       $options  Array of configuration parameters (Optional)
     *
     * @since   1.0
     */
    public function __construct(?array $source = null, array $options = [])
    {
        $source = $source ?? $_COOKIE;
        parent::__construct($source, $options);
    }

    /**
     * Sets a value
     *
     * @param   string $name        Name of the value to set.
     * @param   mixed  $value       Value to assign to the input.
     * @param array    $options     An associative array which may have any of the keys expires, path, domain,
     *                              secure, httponly and samesite. The values have the same meaning as described
     *                              for the parameters with the same name. The value of the samesite element
     *                              should be None, Lax or Strict. Options not given default to httponly = true,
     *                              samesite = Lax and secure = true if the current request uses HTTPS; the other
     *                              options default to the defaults of setcookie().
     *
     * @return  boolean  True if the cookie was sent, false if it could not be (e.g. headers already sent).
     *
     * @link    https://www.ietf.org/rfc/rfc2109.txt
     * @link    https://php.net/manual/en/function.setcookie.php
     *
     * @since   1.0
     */
    public function set(string $name, mixed $value, array $options = []): bool
    {
        $options += ['httponly' => true, 'samesite' => 'Lax', 'secure' => $this->isHttps()];

        // Set the cookie
        if (!setcookie($name, $value, $options)) {
            return false;
        }

        $this->data[$name] = $value;

        return true;
    }

    /**
     * Checks whether the current request uses HTTPS.
     *
     * Proxy headers such as X-Forwarded-Proto are not trusted.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    private function isHttps(): bool
    {
        return !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
    }
}
