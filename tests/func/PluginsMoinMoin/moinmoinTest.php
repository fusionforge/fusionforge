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

		// $this->gotoProject('ProjectA');
		// $this->click("link=MoinMoinWiki");
		// sleep(5); // MoinMoinWiki has no <h1> element
		// $this->assertTrue($this->isTextPresent("ConfigurationError"));

		$this->cron_for_plugin("create-wikis.php", "moinmoin");
		sleep (5);

		$this->gotoProject('ProjectA');
		$this->click("link=MoinMoinWiki");
		sleep(5); // MoinMoinWiki has no <h1> element
		$this->assertFalse($this->isTextPresent("ConfigurationError"));
		$this->assertFalse($this->isTextPresent("Wiki not created yet"));

		// $this->click("link=Create New Page");
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
