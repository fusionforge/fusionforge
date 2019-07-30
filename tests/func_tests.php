<?php
/**
 * Copyright FusionForge Team
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

if (@include_once '/usr/local/share/php/vendor/autoload.php') {
	$phpunitversion = 6;
	class PHPUnit_Framework_TestSuite extends PHPUnit\Framework\TestSuite {}
	class PHPUnit_Framework_TestCase extends PHPUnit\Framework\TestCase {}
} else {
	$phpunitversion = 4;
	if (!@include_once 'PHPUnit/Autoload.php') {
		include_once 'PHPUnit/Framework.php';
		require_once 'PHPUnit/TextUI/TestRunner.php';
	}
}

class AllTests {
	public static function main() {
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite() {
		$suite = new PHPUnit\Framework\TestSuite('PHPUnit');

		// Selenium tests
		if (getenv('TESTGLOB') != FALSE) {
			$files = glob(dirname(__FILE__).'/'.getenv('TESTGLOB'));
		} else {
			$files = glob(dirname(__FILE__).'/func/*/*Test.php');
		}
		natsort($files);
		$suite->addTestFiles($files);

		return $suite;
	}
}
