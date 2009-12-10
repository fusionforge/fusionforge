<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once dirname(__FILE__) . '/../../../gforge/common/include/database-pgsql.php';

/**
 * Simple tests for the utils library.
 *
 * @package   Tests
 * @author    Alain Peyrat <aljeux@free.fr>
 * @copyright 2009 Alain Peyrat. All rights reserved.
 * @license   GPL License
 */
class Database_Utils_Tests extends PHPUnit_Framework_TestCase
{
	/**
	 * test the validate_hostname function.
	 */
	public function testConstructQPA()
	{
		$qpa = db_construct_qpa () ;
		$this->assertEquals($qpa[0], '');

		$qpa = db_construct_qpa ($qpa, 'SELECT foo FROM bar') ;
		$this->assertEquals($qpa[0], 'SELECT foo FROM bar');
		$this->assertEquals(count($qpa[1]), 0);
		$this->assertEquals($qpa[2], 0);

		$qpa = db_construct_qpa ($qpa, ' WHERE name = $1', array ('nrst')) ;
		$this->assertEquals($qpa[0], 'SELECT foo FROM bar WHERE name = $1');

		$qpa = db_construct_qpa ($qpa, ' AND mail = $1 AND addr LIKE $2', array ('auie@foobar',
									'bépo')) ;
		$this->assertEquals($qpa[0], 'SELECT foo FROM bar WHERE name = $1 AND mail = $2 AND addr LIKE $3');

		$qpa = db_construct_qpa ($qpa, ' AND quux = $1', array ('jldv')) ;
		$this->assertEquals($qpa[0], 'SELECT foo FROM bar WHERE name = $1 AND mail = $2 AND addr LIKE $3 AND quux = $4');
		$this->assertEquals($qpa[1], array ('nrst', 'auie@foobar', 'bépo', 'jldv')) ;
		$this->assertEquals($qpa[2], 4) ;		

		$qpa = db_construct_qpa ($qpa, ' AND long1 = $1 AND long2 = $2 AND long3 = $3 AND long4 = $4 AND long5 = $5 AND long6 = $6 AND long7 = $7 AND long8 = $8 AND long9 = $9 AND long10 = $10 AND long11 = $11 AND long12 = $12', array (1,2,3,4,5,6,7,8,9,10,11,12)) ;
		$this->assertEquals($qpa[0], 'SELECT foo FROM bar WHERE name = $1 AND mail = $2 AND addr LIKE $3 AND quux = $4 AND long1 = $5 AND long2 = $6 AND long3 = $7 AND long4 = $8 AND long5 = $9 AND long6 = $10 AND long7 = $11 AND long8 = $12 AND long9 = $13 AND long10 = $14 AND long11 = $15 AND long12 = $16') ;

		$this->assertEquals($qpa[1], array ('nrst', 'auie@foobar', 'bépo', 'jldv', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12)) ;
		$this->assertEquals($qpa[2], 16) ;		

	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
