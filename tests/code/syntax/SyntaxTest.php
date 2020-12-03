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
class Syntax_Tests extends PHPUnit\Framework\TestCase
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
		$this->assertEquals(0,$rc);
	}

	/**
	 * Validate all php code with php -l.
	 */
	public function testPhpSyntax()
	{
		$root = dirname(dirname(dirname(dirname(__FILE__))));
		$output = `find $root/src $root/tests -path $root/src/plugins/wiki/www/lib -prune -or -name '*.php' -type f  -exec php -l {} \; | grep -v '^No syntax errors detected'`;
		$this->assertEquals('', $output);
	}

	/**
	 * Validate all scripts with isutf8.
	 */
	public function testUTF8Chars()
	{
		$root = dirname(dirname(dirname(dirname(__FILE__))));
		// We don't pass syntax tests on 3rd party libraries in src/vendor
		$exclude_third_party_libs="-path '$root/src/vendor' -prune -o ";
		$output = `find $root/src $root/tests $exclude_third_party_libs -name '*.php' -type f -print | xargs isutf8`;
		$this->assertEquals('', $output);
		$output = `find $root/src $root/tests $exclude_third_party_libs -name '*.css' -type f -print | xargs isutf8`;
		$this->assertEquals('', $output);
		$output = `find $root/src $root/tests $exclude_third_party_libs -name '*.sql' -type f -print | xargs isutf8`;
		$this->assertEquals('', $output);
		$output = `find $root/src $root/tests $exclude_third_party_libs -name '*.sh' -type f -print | xargs isutf8`;
		$this->assertEquals('', $output);
		$output = `find $root/src $root/tests $exclude_third_party_libs -name '*.pl' -type f -print | xargs isutf8`;
		$this->assertEquals('', $output);
		$output = `find $root/src $root/tests $exclude_third_party_libs -name '*.tmpl' -type f -print | xargs isutf8`;
		$this->assertEquals('', $output);
		$output = `find $root/src $root/tests $exclude_third_party_libs -name '*.xml' -type f -print | xargs isutf8`;
		$this->assertEquals('', $output);
	}

	/**
	 * Ensure all scripts use Unix-style line endings
	 */
	public function testUnixLineEndings()
	{
		$root = dirname(dirname(dirname(dirname(__FILE__))));
		// to see individual lines + line nums : find src tests -name '*.php' -type f | xargs pcregrep -n '\r$'
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
		// We don't pass syntax tests on 3rd party libraries in src/vendor
		$exclude_third_party_libs="-path '$root/src/vendor' -prune -o ";
		$output = `find $root/src $root/tests $exclude_third_party_libs -name '*.php' -type f -print | while read i ; do [ -s \$i ] && [ -z "\$(tail -n 1 \$i)" ] && echo \$i ; done`;
		$this->assertEquals('', $output);
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
