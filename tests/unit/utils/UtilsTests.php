<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once dirname(__FILE__) . '/../../../gforge/common/include/utils.php';

/**
 * Simple math test class.
 *
 * @package   Example
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: 0.4.7
 * @link      http://www.phpundercontrol.org/
 */
class Utils_Tests extends PHPUnit_Framework_TestCase
{
    /**
     * test the validate_email function.
     */
    public function testEmail()
    {
	$this->assertTrue(validate_email('al@fx.fr'), 'al@fx.fr is a valid email address');

	$this->assertFalse(validate_email('al @fx.fr'), 'al @fx.fr is not a valid email address');

	$this->assertFalse(validate_email('al'), 'al is not a valid email address');
    }

    /**
     * test the validate_hostname function.
     */
    public function testHostname()
    {
	$this->assertTrue(valid_hostname('myhost.com'), 'myhost.com is a valid hostname.');

	$this->assertTrue(valid_hostname('myhost.com.'), 'myhost.com. is a valid hostname.');

	$this->assertFalse(valid_hostname('my host.com'), 'my host.com is not a valid hostname');

	$this->assertFalse(valid_hostname('O@O'), 'O@O is not a valid hostname');
    }
}
