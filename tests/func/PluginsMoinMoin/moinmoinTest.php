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
		$this->populateStandardTemplate('empty');
		$this->init();
		
		$this->click("link=Admin");
		$this->waitForPageToLoad("30000");
		$this->click("link=Tools");
		$this->waitForPageToLoad("30000");
		$this->click("use_moinmoin");
		$this->click("submit");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Project information updated"));

		$this->cron_for_plugin("create-wikis.php", "moinmoin");
		sleep (5);

		$this->gotoProject('ProjectA');
		$this->click("link=MoinMoinWiki");
		sleep (10); // No <h1> in MoinMoin's default layout, so waitForPageToLoad() doesn't work
		$this->assertFalse($this->isTextPresent("ConfigurationError"));
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
