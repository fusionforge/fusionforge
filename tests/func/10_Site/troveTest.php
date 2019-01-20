<?php
/**
 * Copyright (C) 2009 Alain Peyrat <aljeux@free.fr>
 * Copyright 2019, Franck Villaume - TrivialDev
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

require_once dirname(dirname(__FILE__)).'/SeleniumForge.php';

class Trove extends FForge_SeleniumTestCase
{
	function testTroveAdmin()
	{
		$this->open( ROOT );
		$this->login(FORGE_ADMIN_USERNAME);
		$this->url(ROOT."/admin/");
		$this->waitForPageToLoad();
		$this->clickAndWait("link=Display Trove Map");
		$this->waitForPageToLoad();

		// Test simple modification of an entry (beta => beta2)
		$this->clickAndWait("//a[contains(@href, 'trove_cat_edit.php?trove_cat_id=10')]");
		$this->waitForPageToLoad();
		$this->type("form_shortname", "beta2");
		$this->type("form_fullname", "4 - Beta2");
		$this->type("form_description", "Resource2 is in late phases of development. Deliverables are essentially complete, but may still have significant bugs.");
		$this->clickAndWait("submit");
		$this->waitForPageToLoad();
		$this->assertTrue($this->isTextPresent("4 - Beta2"));

		// Test removal of an entry (beta2) (leaf)
		$this->clickAndWait("//a[contains(@href, 'trove_cat_edit.php?trove_cat_id=10')]");
		$this->waitForPageToLoad();
		$this->clickAndWait("delete");
		$this->waitForPageToLoad();
		$this->assertFalse($this->isTextPresent("4 - Beta2"));

		// Test creation of a new entry (test)
		$this->clickAndWait("link=Site Admin");
		$this->waitForPageToLoad();
		$this->clickAndWait("link=Add to the Trove Map");
		$this->waitForPageToLoad();
		$this->type("form_shortname", "Test");
		$this->type("form_fullname", "Test");
		$this->type("form_description", "This is a test");
		$this->clickAndWait("submit");
		$this->waitForPageToLoad();
		$this->assertTrue($this->isTextPresent("Test"));

		// Test removal of a non empty entry (Microsoft).
		$this->clickAndWait("//a[contains(@href, 'trove_cat_edit.php?trove_cat_id=214')]");
		$this->waitForPageToLoad();
		$this->clickAndWait("delete");
		$this->waitForPageToLoad();
		$this->assertTrue($this->isTextPresent("Test"));
		$this->assertFalse($this->isTextPresent("Microsoft"));
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
