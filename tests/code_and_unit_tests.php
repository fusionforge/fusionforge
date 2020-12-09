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

@include_once '/usr/local/share/php/vendor/autoload.php';

@include_once 'PHP/Token/Stream/Autoload.php';
@include_once 'Text/Template/Autoload.php';

class AllTests {
	public static function main() {
		PHPUnit\TextUI\TestRunner::run(self::suite());
	}

	public static function suite() {
		$suite = new PHPUnit\Framework\TestSuite('PHPUnit');

		// Unit tests
		$suite->addTestFiles(glob("unit/*/*Test.php"));

		// Code tests
		$suite->addTestFiles(glob("code/*/*Test.php"));

		return $suite;
	}
}
