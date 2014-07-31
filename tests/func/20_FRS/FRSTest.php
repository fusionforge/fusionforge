<?php
/**
 * Copyright, 2014, Franck Villaume - TrivialDev
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

require_once dirname(dirname(__FILE__)).'/Testing/SeleniumForge.php';

class CreateFRS extends FForge_SeleniumTestCase
{
	function testCreateFRSPackage()
	{
		$this->init();
		$this->clickAndWait("link=Files");
		$this->assertFalse($this->isTextPresent("Permission denied."));
		$this->assertTrue($this->isTextPresent("Administration"));
		$this->clickAndWait("link=Administration");
		$this->type("//input[@name='package_name']", "My Package Name");
		$this->clickAndWait("//input[@name='submit' and @value='Create This Package']");
		$this->assertTextPresent("Added Package");
	}
}
