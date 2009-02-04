<?php

require_once 'PHPUnit/Framework/TestCase.php';

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
class Syntax_Tests extends PHPUnit_Framework_TestCase
{
    /**
     * Validate all php code with php -l.
     */
    public function testPhpSyntax()
    {
	$output = `find ../gforge -name '*.php' -type f  -exec php -l {} \; | grep -v '^No syntax errors detected'`;
	$this->assertEquals('', $output);
    }
}
