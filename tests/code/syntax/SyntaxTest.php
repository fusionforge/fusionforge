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
	    $output = `cd .. ; find gforge tests -name '*.php' -type f  -exec php -l {} \; | grep -v '^No syntax errors detected'`;
	    $this->assertEquals('', $output);
    }

    /**
     * Validate all scripts with isutf8.
     */
    public function testUTF8Chars()
    {
	    // Skip the wiki part which is not UTF-8 encoded.
	    $output = `cd .. ; find gforge tests -name '*.php' -not -path 'gforge/plugins/wiki/www/*' -type f | xargs isutf8`;
	    $this->assertEquals('', $output);
	    $output = `cd .. ; find gforge tests -name '*.sql' -type f | xargs isutf8`;
	    $this->assertEquals('', $output);
	    $output = `cd .. ; find gforge tests -name '*.sh' -type f | xargs isutf8`;
	    $this->assertEquals('', $output);
	    $output = `cd .. ; find gforge tests -name '*.pl' -type f | xargs isutf8`;
	    $this->assertEquals('', $output);
    }

    /**
     * Ensure all scripts use Unix-style line endings
     */
    public function testUnixLineEndings()
    {
	    $output = `cd .. ; find gforge tests -name '*.php' -type f | xargs pcregrep -l '\r$'`;
	    $this->assertEquals('', $output);
	    $output = `cd .. ; find gforge tests -name '*.sql' -type f | xargs pcregrep -l '\r$'`;
	    $this->assertEquals('', $output);
	    $output = `cd .. ; find gforge tests -name '*.sh' -type f | xargs pcregrep -l '\r$'`;
	    $this->assertEquals('', $output);
	    $output = `cd .. ; find gforge tests -name '*.pl' -type f | xargs pcregrep -l '\r$'`;
	    $this->assertEquals('', $output);
    }
}
