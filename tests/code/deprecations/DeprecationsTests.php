<?php

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Syntax test class.
 *
 * @package   DeprecationsTests
 * @author    Roland Mas <lolando@debian.org>
 * @copyright 2009 Roland Mas
 * @license   http://www.opensource.org/licenses/gpl-license.php  GPL License
 */
class Deprecations_Tests extends PHPUnit_Framework_TestCase
{
	/**
	 * Check that no code uses db_query()
	 */
	public function testdb_query()
	{
		$output = `cd .. ; find gforge tests -name '*.php' -type f | xargs pcregrep -l '\bdb_query\b' \
					   | grep -v ^tests/code/deprecations/DeprecationsTest.php \
					   | grep -v ^gforge/db/upgrade-db.php \
					   | grep -v ^gforge/www/include/database-oci8.php \
					   | grep -v ^gforge/common/include/database-pgsql.php \
					   | grep -v ^gforge/common/include/database-mysql.php`;
		$this->assertEquals('', $output);
	}
	
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

}
