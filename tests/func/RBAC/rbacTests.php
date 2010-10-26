<?php
/*
 * Copyright 2010, Roland Mas
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once dirname(dirname(__FILE__)).'/Testing/SeleniumGforge.php';

class RBAC extends FForge_SeleniumTestCase
{
	function testRBAC()
	{
		$this->init();

		$this->createUser ('staffmember') ;

		$this->open( ROOT ."/projects/projecta" );
		$this->waitForPageToLoad("30000");
		$this->click("link=Admin");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Project Admin: ProjectA"));
		$this->click("link=Users and permissions");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Members of ProjectA"));
		$this->click("//tr/td[contains(.,'Anonymous')]/../td/input[contains(@value,'Unlink Role')]");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Role unlinked successfully"));

		$this->logout();
		$this->assertFalse($this->isTextPresent("ProjectA"));

		$this->open( ROOT ."/projects/projecta" );
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isLoginRequired());
		$this->triggeredLogin('staffmember');
		$this->assertTrue($this->isTextPresent("This is the public description for ProjectA."));
	}

}
?>
