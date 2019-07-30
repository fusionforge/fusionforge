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

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Simple test to check that htmlpurifier is installed
 *
 * @package   Tests
 * @author    Olivier Berger <olivier.berger@it-sudparis.eu>
 * @copyright 2009 Olivier Berger & Institut TELECOM
 * @license   GPL License
 */
class HtmlPurifier_Tests extends PHPUnit\Framework\TestCase
{
	/**
	 * Test that include of lib doesn't fail, otherwise give some hint on missing package
	 */
	public function testHtmlPurifier()
	{
	  try {
	    // cannot test this as fails hard
	    //require_once('HTMLPurifier.auto.php');
	    // so include instead :
	    include 'HTMLPurifier.auto.php';
	  }

	  catch (PHPUnit\Framework\Error $expected) {
	    $this->fail('You probably need to install htmlpurifier : '.$expected->getMessage());
            return;
	  }

	}

}
