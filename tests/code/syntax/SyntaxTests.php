<?php

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Syntax test class.
 *
 * @package   SyntaxTests
 * @author    Alain Peyrat <aljeux@free.fr>
 * @copyright 2009 Alain Peyrat. All rights reserved.
 * @license   http://www.opensource.org/licenses/gpl-license.php  GPL License
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

    /**
     * Validate all php code with isutf8.
     */
    public function testUTF8Chars()
    {
	$output = `find ../gforge -name '*.php' -type f  -exec isutf8 {} \;`;
	$this->assertEquals('', $output);
    }
}
