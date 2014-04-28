<?php
/*
 * Copyright (C) 2014 Roland Mas
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

class SSHTest extends FForge_SeleniumTestCase
{
	function testSSH()
	{
		$this->init();

		$this->uploadSshKey();
	    
		// Run the cronjobs
		$this->reload_nscd();
		$this->cron("homedirs.php");
		$this->cron("ssh_create.php");

		system("echo 'Trying SSH' 1>&2", $ret);
		system("ssh -v ".FORGE_ADMIN_USERNAME."@".HOST." true", $ret);
		$this->assertEquals($ret, 0);
		system("echo 'End of SSH run' 1>&2", $ret);
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
					  || $this->isElementPresent("//.[@class='page_footer']"));
		}
	}

}
?>
