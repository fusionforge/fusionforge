<?php
/*
 * Copyright 2011, Roland Mas
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

require_once dirname(dirname(__FILE__)).'/Testing/SeleniumGforge.php';

class Search extends FForge_SeleniumTestCase
{
	function testProjectSearch()
	{
		$this->init();
		$this->open(ROOT) ;
		$this->waitForPageToLoad("30000");
		$this->type("//input[@name='words']", "XXXXXXXXXXXXXXXXXXXXXXXXXX");
		$this->click("//input[@name='Search']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("No matches found for"));
		$this->type("//input[@name='words']", "projecta");
		$this->click("//input[@name='Search']");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isTextPresent("No matches found for"));
	}

}
?>
