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
   * First, make sure pcregrep is installed
   */
  public function testPcRegrepInstalled()
  {
    $output = `type pcregrep >/dev/null; echo $?`;
    $rc = trim($output);
    if ($rc != '0') {
      $output = `type pcregrep`;
      $this->fail('You should probably install "pcregrep" : `type pcregrep` reports "'.$output);
    }
  }

    /**
     * Validate all php code with php -l.
     */
    public function testPhpSyntax()
    {
		$root = dirname(dirname(dirname(dirname(__FILE__))));
		$output = `find $root/src $root/tests -name '*.php' -type f  -exec php -l {} \; | grep -v '^No syntax errors detected'`;
	    $this->assertEquals('', $output);
    }

    /**
     * Validate all scripts with isutf8.
     */
    public function testUTF8Chars()
    {
		$root = dirname(dirname(dirname(dirname(__FILE__))));
		$output = `find $root/src $root/tests -name '*.php' -type f | xargs isutf8`;
	    $this->assertEquals('', $output);
	    $output = `find $root/src $root/tests -name '*.sql' -type f | xargs isutf8`;
	    $this->assertEquals('', $output);
	    $output = `find $root/src $root/tests -name '*.sh' -type f | xargs isutf8`;
	    $this->assertEquals('', $output);
	    $output = `find $root/src $root/tests -name '*.pl' -type f | xargs isutf8`;
	    $this->assertEquals('', $output);
    }

    /**
     * Ensure all scripts use Unix-style line endings
     */
    public function testUnixLineEndings()
    {
		$root = dirname(dirname(dirname(dirname(__FILE__))));
		$output = `find $root/src $root/tests -name '*.php' -type f | xargs pcregrep -l '\r$'`;
	    $this->assertEquals('', $output);
		$output = `find $root/src $root/tests -name '*.sql' -type f | xargs pcregrep -l '\r$'`;
	    $this->assertEquals('', $output);
		$output = `find $root/src $root/tests -name '*.sh' -type f | xargs pcregrep -l '\r$'`;
	    $this->assertEquals('', $output);
		$output = `find $root/src $root/tests -name '*.pl' -type f | xargs pcregrep -l '\r$'`;
	    $this->assertEquals('', $output);
    }

    /**
     * Ensure no scripts have SVN conflicts markers
     */
    public function testSVNConflicts()
    {
	    $root = dirname(dirname(dirname(dirname(__FILE__))));
	    $output = `find $root/src $root/tests -type f | xargs grep -l '^<<<<<<'`;
	    $this->assertEquals('', $output);
	    $output = `find $root/src $root/tests -type f | xargs grep -l '^>>>>>>'`;
	    $this->assertEquals('', $output);
    }

    /**
     * Ensure no script has an empty last line
     */
    public function testEmptyLastLine()
    {
		$root = dirname(dirname(dirname(dirname(__FILE__))));
    	$output = `find $root/src $root/tests -name '*.php' -type f | while read i ; do [ -s \$i ] && [ -z "\$(tail -n 1 \$i)" ] && echo \$i ; done`;
	    $this->assertEquals('', $output);
    }

    /**
     * Validate syntax of gettextfiles
     */
    public function testGettextSyntax()
    {
		$root = dirname(dirname(dirname(dirname(__FILE__))));
		$output = `cd $root/src ; ./utils/manage-translations.sh check 2>&1`;
	    $this->assertEquals('', $output);
    }
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
