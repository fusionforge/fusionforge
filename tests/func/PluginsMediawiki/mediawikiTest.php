<?php
/*
 * Copyright 2012, Roland Mas
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

class PluginMediawiki extends FForge_SeleniumTestCase
{
	protected $alreadyActive = 0;
	
	function testMediawiki()
	{
		$this->activatePlugin('mediawiki');
		
		$this->populateStandardTemplate('empty');
		$this->init();

		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->click("use_mediawiki");
		$this->clickAndWait("submit");
		$this->assertTrue($this->isTextPresent("Project information updated"));

 		$this->cron_for_plugin("create-wikis.php", "mediawiki");
 		sleep (10);

		$this->gotoProject('ProjectA');
		$this->clickAndWait("link=Mediawiki");
		$this->assertFalse($this->isTextPresent("not created yet"));

		$this->clickAndWait("link=edit this page");
		$this->assertTrue($this->isTextPresent("You have followed a link to a page that does not exist yet."));
		$this->type("//textarea[@id='wpTextbox1']", "= Bleh =
== Blahblah ==

And more lorem ipsum too.");
		$this->type("//input[@id='wpSummary']", "Page created during testsuite run");

		$this->clickAndWait("//input[@id='wpSave']");

		$this->clickAndWait("link=Mediawiki");
		$this->assertTrue($this->isTextPresent("lorem ipsum"));
		$this->assertTrue($this->isElementPresent("//h1[contains(.,'Bleh')]"));
		$this->assertTrue($this->isElementPresent("//h2[contains(.,'Blahblah')]"));
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
