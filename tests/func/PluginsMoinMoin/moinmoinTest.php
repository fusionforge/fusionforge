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

require_once dirname(dirname(__FILE__)).'/Testing/SeleniumForge.php';

class PluginMoinMoin extends FForge_SeleniumTestCase
{
	protected $alreadyActive = 0;
	
	function testMoinMoin()
	{
		$this->activatePlugin('moinmoin');
		
		$this->populateStandardTemplate('empty');
		$this->init();

		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->click("use_moinmoin");
		$this->clickAndWait("submit");
		$this->assertTrue($this->isTextPresent("Project information updated"));

		$this->cron_for_plugin("create-wikis.php", "moinmoin");
		sleep (5);

		$this->gotoProject('ProjectA');
		$this->clickAndWait("link=MoinMoinWiki");
		$this->assertFalse($this->isTextPresent("ConfigurationError"));
		$this->assertFalse($this->isTextPresent("Wiki not created yet"));

		$this->clickAndWait("link=Create New Page");
		$this->assertFalse($this->isTextPresent("You are not allowed"));
		$this->type("//textarea[@id='editor-textarea']", "Pardon me, boy
Is that the Chattanooga choo choo?");
		$this->clickAndWait("//input[@name='button_save']");
		$this->gotoProject('ProjectA');
		$this->clickAndWait("link=MoinMoinWiki");
		$this->assertTrue($this->isTextPresent("Chattanooga"));
	}

	/**
	 * Method that is called after Selenium actions.
	 *
	 * @param  string $action
	 */
	protected function defaultAssertions($action)
	{
		if ($action == 'waitForPageToLoad') {
			$this->assertTrue($this->isElementPresent("//h1")
					  || $this->isElementPresent("//div[@id='footer']"));
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
