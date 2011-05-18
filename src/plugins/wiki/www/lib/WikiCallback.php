<?php //-*-php-*-
// $Id: WikiCallback.php 7964 2011-03-05 17:05:30Z vargenau $

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
class WikiCallback
{
    /**
     * Convert from Pear-style callback specification to a WikiCallback.
     *
     * This is a static member function.
     *
     * @param $pearCb mixed
     * For a global function callback, $pearCb should be a string containing
     * the name of the function.
     * For an object method callback, $pearCb should be a array of length two:
     * the first element should contain (a reference to) the object, the second
     * element should be a string containing the name of the method to be invoked.
     * @return object Returns the appropriate subclass of WikiCallback.
     * @access public
     */
    function callback ($pearCb) {
        if (is_string($pearCb))
            return new WikiFunctionCb($pearCb);
        else if (is_array($pearCb)) {
            list($object, $method) = $handler;
            return new WikiMethodCb($object, $method);
        }
        trigger_error("WikiCallback::new: bad arg", E_USER_ERROR);
    }

    /**
     * Call callback.
     *
     * @param ? mixed This method takes a variable number of arguments (zero or more).
     * The callback function is called with the specified arguments.
     * @return mixed The return value of the callback.
     * @access public
     */
    function call () {
        return $this->call_array(func_get_args());
    }

    /**
     * Call callback (with args in array).
     *
     * @param $args array Contains the arguments to be passed to the callback.
     * @return mixed The return value of the callback.
     * @see call_user_func_array.
     * @access public
     */
    function call_array ($args) {
        trigger_error('pure virtual', E_USER_ERROR);
    }

    /**
     * Convert to Pear callback.
     *
     * @return string The name of the callback function.
     *  (This value is suitable for passing as the callback parameter
     *   to a number of different Pear functions and methods.)
     * @access public
     */
    function toPearCb() {
        trigger_error('pure virtual', E_USER_ERROR);
    }
}

/**
 * Global function callback.
 */
class WikiFunctionCb
    extends WikiCallback
{
    /**
     * Constructor
     *
     * @param $functionName string Name of global function to call.
     * @access public
     */
    function WikiFunctionCb ($functionName) {
        $this->functionName = $functionName;
    }

    function call_array ($args) {
        return call_user_func_array($this->functionName, $args);
    }

    function toPearCb() {
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
     * Constructor
     *
     * @param $object object Object on which to invoke method.
     * @param $methodName string Name of method to call.
     * @access public
     */
    function WikiMethodCb(&$object, $methodName) {
        $this->object = &$object;
        $this->methodName = $methodName;
    }

    function call_array ($args) {
        $method = &$this->methodName;
        return call_user_func_array(array(&$this->object, $method), $args);
    }

    function toPearCb() {
        return array($this->object, $this->methodName);
    }
}

/**
 * Anonymous function callback.
 */
class WikiAnonymousCb
    extends WikiCallback
{
    /**
     * Constructor
     *
     * @param $args string Argument declarations
     * @param $code string Function body
     * @see create_function().
     * @access public
     */
    function WikiAnonymousCb ($args, $code) {
        $this->function = create_function($args, $code);
    }

    function call_array ($args) {
        return call_user_func_array($this->function, $args);
    }

    function toPearCb() {
        trigger_error("Can't convert WikiAnonymousCb to Pear callback",
                      E_USER_ERROR);
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
