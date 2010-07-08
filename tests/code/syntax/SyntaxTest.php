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
   * First, make sure it is run from inside the tests/ subdir
   */
  public function testPath()
  {
    $output = `ls ../src >/dev/null; echo $?`;
    $rc = trim($output);
    if ($rc != '0') {
      $output = `ls ../src`;
      $this->fail('Must be run from inside the "tests/" subdir : `ls ../src` reports "'.$output);
    }
  }
    /**
     * Validate all php code with php -l.
     */
    public function testPhpSyntax()
    {
	    $output = `cd .. ; find src tests -name '*.php' -type f  -exec php -l {} \; | grep -v '^No syntax errors detected'`;
	    $this->assertEquals('', $output);
    }

    /**
     * Validate all scripts with isutf8.
     */
    public function testUTF8Chars()
    {
	    // Skip the wiki part which is not UTF-8 encoded.
	    $output = `cd .. ; find src tests -name '*.php' -not -path 'src/plugins/wiki/www/*' -type f | xargs isutf8`;
	    $this->assertEquals('', $output);
	    $output = `cd .. ; find src tests -name '*.sql' -type f | xargs isutf8`;
	    $this->assertEquals('', $output);
	    $output = `cd .. ; find src tests -name '*.sh' -type f | xargs isutf8`;
	    $this->assertEquals('', $output);
	    $output = `cd .. ; find src tests -name '*.pl' -type f | xargs isutf8`;
	    $this->assertEquals('', $output);
    }

    /**
     * Ensure all scripts use Unix-style line endings
     */
    public function testUnixLineEndings()
    {
	    $output = `cd .. ; find src tests -name '*.php' -type f | xargs pcregrep -l '\r$'`;
	    $this->assertEquals('', $output);
	    $output = `cd .. ; find src tests -name '*.sql' -type f | xargs pcregrep -l '\r$'`;
	    $this->assertEquals('', $output);
	    $output = `cd .. ; find src tests -name '*.sh' -type f | xargs pcregrep -l '\r$'`;
	    $this->assertEquals('', $output);
	    $output = `cd .. ; find src tests -name '*.pl' -type f | xargs pcregrep -l '\r$'`;
	    $this->assertEquals('', $output);
    }

    /**
     * Ensure no script has an empty last line
     */
    public function testEmptyLastLine()
    {
	    $output = `cd .. ; find src tests -name '*.php' -type f | while read i ; do [ -s \$i ] && [ -z "\$(tail -n 1 \$i)" ] && echo \$i ; done`;
	    $this->assertEquals('', $output);
    }
}
	
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
