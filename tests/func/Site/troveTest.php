<?php
/*
 * Copyright (C) 2009 Alain Peyrat <aljeux@free.fr>
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

require_once 'func/Testing/SeleniumGforge.php';

class Trove extends FForge_SeleniumTestCase
{
	function testTroveAdmin()
	{
		$this->open( ROOT );
		$this->login(FORGE_ADMIN_USERNAME);
		$this->click("link=Site Admin");
		$this->waitForPageToLoad("30000");
		$this->click("link=Display Trove Map");
		$this->waitForPageToLoad("30000");

		// Test simple modification of an entry (beta => beta2)
		$this->click("//a[contains(@href, 'trove_cat_edit.php?trove_cat_id=10')]");
		$this->waitForPageToLoad("30000");
		$this->type("form_shortname", "beta2");
		$this->type("form_fullname", "4 - Beta2");
		$this->type("form_description", "Resource2 is in late phases of development. Deliverables are essentially complete, but may still have significant bugs.");
		$this->click("submit");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("4 - Beta2"));

		// Test removal of an entry (beta2) (leaf)
		$this->click("//a[contains(@href, 'trove_cat_edit.php?trove_cat_id=10')]");
		$this->waitForPageToLoad("30000");
		$this->click("delete");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isTextPresent("4 - Beta2"));

		// Test creation of a new entry (test)
		$this->click("link=Site Admin");
		$this->waitForPageToLoad("30000");
		$this->click("link=Add to the Trove Map");
		$this->waitForPageToLoad("30000");
		$this->type("form_shortname", "Test");
		$this->type("form_fullname", "Test");
		$this->type("form_description", "This is a test");
		$this->click("submit");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Test"));

		// Test removal of a non empty entry (Microsoft).
		$this->click("//a[contains(@href, 'trove_cat_edit.php?trove_cat_id=214')]");
		$this->waitForPageToLoad("30000");
		$this->click("delete");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Test"));
		$this->assertFalse($this->isTextPresent("Microsoft"));
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
