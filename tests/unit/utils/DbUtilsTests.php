<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once dirname(__FILE__) . '/../../../gforge/common/database/database-pgsql.php';

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
	public function testHostname()
	{
		$qpa = db_construct_qpa () ;
		$this->assertTrue($qpa[0], '');

		$qpa = db_constract_qpa ('SELECT foo FROM bar') ;
		$this->assertTrue($qpa[0], 'SELECT foo FROM bar');
		$this->assertTrue(count($qpa[1]), 0);
		$this->assertTrue($qpa[2], 0);

		$qpa = db_construct_qpa ($qpa, ' WHERE name = $1', array ('nrst')) ;
		$this->assertTrue($qpa[0], 'SELECT foo FROM bar WHERE name = $1');

		$qpa = db_construct_qpa ($qpa, ' AND mail = $1 AND addr LIKE $2', array ('auie@foobar',
									'bépo')) ;
		$this->assertTrue($qpa[0], 'SELECT foo FROM bar WHERE name = $1 AND mail = $2 AND addr LIKE $3');

		$qpa = db_construct_qpa ($qpa, ' AND quux = $1', array ('jldv')) ;
		$this->assertTrue($qpa[0], 'SELECT foo FROM bar WHERE name = $1 AND mail = $2 AND addr LIKE $3 AND quux = $4');
		$this->assertTrue($qpa[1][0], 'nrst') ;
		$this->assertTrue($qpa[1][1], 'auie') ;
		$this->assertTrue($qpa[1][2], 'bépo') ;
		$this->assertTrue($qpa[1][3], 'jldv') ;
		$this->assertTrue($qpa[2], 4) ;		
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
