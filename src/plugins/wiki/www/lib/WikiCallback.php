<?php
/**
 * Copyright © 2001 Jeff Dairiki
 * Copyright © 2004 Reini Urban
 *
 * This file is part of PhpWiki.
 *
 * PhpWiki is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * PhpWiki is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 */

/**
 * A callback
 *
 * This is a virtual class.
 *
 * Subclases of WikiCallback can be used to represent either
 * global function callbacks, or object method callbacks.
 *
 * @see WikiFunctionCb, WikiMethodCb.
 */
abstract class WikiCallback
{
    /**
     * Call callback.
     *
     * @param ? mixed This method takes a variable number of arguments (zero or more).
     * The callback function is called with the specified arguments.
     * @return mixed The return value of the callback.
     */
    public function call()
    {
        return $this->call_array(func_get_args());
    }

    /**
     * Call callback (with args in array).
     *
     * @param $args array Contains the arguments to be passed to the callback.
     * @return mixed The return value of the callback.
     * @see call_user_func_array.
     */
    abstract public function call_array($args);

    /**
     * Convert to Pear callback.
     *
     * @return string The name of the callback function.
     *  (This value is suitable for passing as the callback parameter
     *   to a number of different Pear functions and methods.)
     */
    abstract public function toPearCb();
}

/**
 * Global function callback.
 */
class WikiFunctionCb
    extends WikiCallback
{
    /**
     * @param string $functionName Name of global function to call.
     */
    function __construct($functionName)
    {
        $this->functionName = $functionName;
    }

    function call_array($args)
    {
        return call_user_func_array($this->functionName, $args);
    }

    function toPearCb()
    {
        return $this->functionName;
    }
}

/**
 * Object Method Callback.
 */
class WikiMethodCb
    extends WikiCallback
{
    /**
     * @param object $object Object on which to invoke method.
     * @param string $methodName Name of method to call.
     */
    function __construct(&$object, $methodName)
    {
        $this->object = &$object;
        $this->methodName = $methodName;
    }

    function call_array($args)
    {
        $method = &$this->methodName;
        return call_user_func_array(array(&$this->object, $method), $args);
    }

    function toPearCb()
    {
        return array($this->object, $this->methodName);
    }
}
