<?php
/**
 * Copyright (C) 2015 Roland Mas
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

class MessageTest extends FForge_SeleniumTestCase
{
	protected $alreadyActive = 0;

	function testMessage()
	{
		$this->skip_on_src_installs();
		$this->skip_on_deb_installs();
		$this->skip_on_rpm_installs();
		$this->_activateMessagePlugin();

		$this->clickAndWait("link=Site Admin");
		$this->clickAndWait("link=Configure Global Message");
		$this->type("//textarea[@name='body']", "Forge under maintenance, please bear with us.");
		$this->clickAndWait("//input[@value='Save']");

		$this->open( ROOT );
		$this->assertTrue($this->waitForTextPresent("Forge under maintenance"));

		$this->open( ROOT . '/projects/projecta') ;
		$this->assertTrue($this->waitForTextPresent("Forge under maintenance"));

		$this->clickAndWait("link=Site Admin");
		$this->clickAndWait("link=Configure Global Message");
		$this->type("//textarea[@name='body']", "Forge recently upgraded, please report problems.");
		$this->clickAndWait("//input[@value='Save']");

		$this->open( ROOT );
		$this->assertFalse($this->waitForTextPresent("Forge under maintenance"));
		$this->assertTrue($this->waitForTextPresent("Forge recently upgraded"));

		$this->open( ROOT . '/projects/projecta') ;
		$this->assertFalse($this->waitForTextPresent("Forge under maintenance"));
		$this->assertTrue($this->waitForTextPresent("Forge recently upgraded"));

		$this->clickAndWait("link=Site Admin");
		$this->clickAndWait("link=Configure Global Message");
		$this->type("//textarea[@name='body']", "");
		$this->clickAndWait("//input[@value='Save']");

		$this->open( ROOT );
		$this->assertFalse($this->waitForTextPresent("Forge under maintenance"));
		$this->assertFalse($this->waitForTextPresent("Forge recently upgraded"));

		$this->open( ROOT . '/projects/projecta') ;
		$this->assertFalse($this->waitForTextPresent("Forge under maintenance"));
		$this->assertFalse($this->waitForTextPresent("Forge recently upgraded"));
	}

	private function _activateMessagePlugin() {
		if (! $this->alreadyActive) {
			$this->activatePlugin('message');
			$this->alreadyActive = 1;
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
