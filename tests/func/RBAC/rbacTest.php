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
	function testAnonymousProjectReadAccess()
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
		$this->assertTrue($this->isTextPresent("Project Members"));
	}

	function testGlobalRolesAndPermissions()
	{
		$this->login("admin");

		$this->open( ROOT );
		$this->waitForPageToLoad("30000");
		$this->click("link=Site Admin");
		$this->waitForPageToLoad("30000");

		// Create "Project approvers" role
		$this->type ("//form[contains(@action,'globalroleedit.php')]//input[@name='role_name']", "Project approvers") ;
		$this->click ("//form[contains(@action,'globalroleedit.php')]//input[@value='Create Role']") ;
		$this->waitForPageToLoad("30000");

		// Grant it permissions
		$this->select("//select[@name='data[approve_projects][-1]']", "label=Approve projects");
		$this->select("//select[@name='data[approve_news][-1]']", "label=Approve news");
		$this->click ("//input[@value='Submit']") ;
		$this->waitForPageToLoad("30000");

		// Check permissions were saved
		$this->click("link=Site Admin");
		$this->waitForPageToLoad("30000");
		$this->select ("//form[contains(@action,'globalroleedit.php')]//select[@name='role_id']", "label=Project approvers") ;
		$this->click ("//form[contains(@action,'globalroleedit.php')]//input[@value='Edit Role']") ;
		$this->waitForPageToLoad("30000");

		$this->assertSelected("//select[@name='data[approve_projects][-1]']", "Approve projects");
		$this->assertNotSelected("//select[@name='data[approve_projects][-1]']", "No access");
		$this->assertSelected("//select[@name='data[approve_news][-1]']", "Approve news");
		
		// Whoops, we don't actually want the news moderation bit, unset it
		$this->select("//select[@name='data[approve_news][-1]']", "label=No access");
		$this->click ("//input[@value='Submit']") ;
		$this->waitForPageToLoad("30000");
		$this->assertSelected("//select[@name='data[approve_projects][-1]']", "Approve projects");
		$this->assertSelected("//select[@name='data[approve_news][-1]']", "No access");

		// Create users for "Project approvers" and "News moderators" roles
		$this->createUser ("projapp") ;
		$this->createUser ("newsmod") ;

		// Add them to their respective roles, check they're here
		$this->click("link=Site Admin");
		$this->waitForPageToLoad("30000");
		$this->select ("//form[contains(@action,'globalroleedit.php')]//select[@name='role_id']", "label=Project approvers") ;
		$this->click ("//form[contains(@action,'globalroleedit.php')]//input[@value='Edit Role']") ;
		$this->waitForPageToLoad("30000");
		$this->type ("//form[contains(@action,'globalroleedit.php')]//input[@name='form_unix_name']", "projapp") ;
		$this->click ("//input[@value='Add User']") ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("projapp Lastname"));
		
		$this->click("link=Site Admin");
		$this->waitForPageToLoad("30000");
		$this->select ("//form[contains(@action,'globalroleedit.php')]//select[@name='role_id']", "label=News moderators") ;
		$this->click ("//form[contains(@action,'globalroleedit.php')]//input[@value='Edit Role']") ;
		$this->waitForPageToLoad("30000");
		$this->type ("//form[contains(@action,'globalroleedit.php')]//input[@name='form_unix_name']", "newsmod") ;
		$this->click ("//input[@value='Add User']") ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("newsmod Lastname"));

		// Add a wrong user to the role, then remove it
		$this->type ("//form[contains(@action,'globalroleedit.php')]//input[@name='form_unix_name']", "projapp") ;
		$this->click ("//input[@value='Add User']") ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("projapp Lastname"));
		$this->assertTrue($this->isTextPresent("newsmod Lastname"));
		$this->click ("//a[contains(@href,'/users/projapp')]/../input[@name='rmuser']") ;
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isTextPresent("projapp Lastname"));
		$this->assertTrue($this->isTextPresent("newsmod Lastname"));

		// Register unprivileged user
		$this->createUser ("toto") ;

		// Temporarily grant project approval rights to user
		// (For cases where project_registration_restricted=true)
		$this->click("link=Site Admin");
		$this->waitForPageToLoad("30000");
		$this->select ("//form[contains(@action,'globalroleedit.php')]//select[@name='role_id']", "label=Project approvers") ;
		$this->click ("//form[contains(@action,'globalroleedit.php')]//input[@value='Edit Role']") ;
		$this->waitForPageToLoad("30000");
		$this->type ("//form[contains(@action,'globalroleedit.php')]//input[@name='form_unix_name']", "toto") ;
		$this->click ("//input[@value='Add User']") ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("toto Lastname"));
		
		// Register project
		$this->registerProject ("TotoProject", "toto") ;

		// Revoke project approval rights
		$this->click("link=Site Admin");
		$this->waitForPageToLoad("30000");
		$this->select ("//form[contains(@action,'globalroleedit.php')]//select[@name='role_id']", "label=Project approvers") ;
		$this->click ("//form[contains(@action,'globalroleedit.php')]//input[@value='Edit Role']") ;
		$this->waitForPageToLoad("30000");
		$this->click ("//a[contains(@href,'/users/toto')]/../input[@name='rmuser']") ;
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isTextPresent("toto Lastname"));

		// Try approving it as two users without the right to do so
		$this->switchUser ("toto") ;
		$this->open( ROOT . '/admin/approve-pending.php') ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue ($this->isPermissionDenied()) ;
		$this->switchUser ("newsmod") ;
		$this->open( ROOT . '/admin/approve-pending.php') ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue ($this->isPermissionDenied()) ;

		// Approve it with a user that only has approve_projects
		$this->approveProject ("TotoProject", "projapp") ;

		// Submit a news in the project
		$this->switchUser ("toto") ;
		$this->gotoProject ("TotoProject") ;
		$this->click("link=News") ;
		$this->waitForPageToLoad("30000");
		$this->click("link=Submit") ;
		$this->waitForPageToLoad("30000");
		$this->type("summary", "First TotoNews");
		$this->type("details", "This is a simple news for Toto's project.");
		$this->click("submit");
		$this->waitForPageToLoad("30000");

		// Try to push it to front page with user toto
		$this->open( ROOT . '/news/admin/') ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue ($this->isPermissionDenied()) ;

		// Try to push it to front page with user projapp
		$this->switchUser ("projapp") ;
		$this->open( ROOT . '/news/admin/') ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue ($this->isPermissionDenied()) ;

		// Push it to front page with user newsmod
		$this->switchUser ("newsmod") ;
		$this->open( ROOT . '/news/admin/') ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue ($this->isTextPresent("These items need to be approved")) ;
		$this->assertTrue ($this->isTextPresent("First TotoNews")) ;
		$this->click ("//a[contains(.,'First TotoNews')]") ;
		$this->waitForPageToLoad("30000");
		$this->click ("//input[@type='radio' and @value='1']") ;
		$this->click ("submit") ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue ($this->isTextPresent("These items were approved this past week")) ;
		$this->open( ROOT ) ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue ($this->isTextPresent("First TotoNews")) ;
	}

	function testProjectRolesAndPermissions()
	{
		$this->login("admin");

		$this->createUser ("bigboss") ;
		$this->createUser ("guru") ;
		$this->createUser ("docmaster") ;
		$this->createUser ("trainee") ;

		// Create "Project moderators" role
		$this->click("link=Site Admin");
		$this->waitForPageToLoad("30000");
		$this->type ("//form[contains(@action,'globalroleedit.php')]//input[@name='role_name']", "Project moderators") ;
		$this->click ("//form[contains(@action,'globalroleedit.php')]//input[@value='Create Role']") ;
		$this->waitForPageToLoad("30000");

		// Grant it permissions
		$this->select("//select[@name='data[approve_projects][-1]']", "label=Approve projects");
		$this->click ("//input[@value='Submit']") ;
		$this->waitForPageToLoad("30000");

		// Add bigboss
		$this->type ("//form[contains(@action,'globalroleedit.php')]//input[@name='form_unix_name']", "bigboss") ;
		$this->click ("//input[@value='Add User']") ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("bigboss Lastname"));

		// Create "Documentation masters" role
		$this->click("link=Site Admin");
		$this->waitForPageToLoad("30000");
		$this->type ("//form[contains(@action,'globalroleedit.php')]//input[@name='role_name']", "Documentation masters") ;
		$this->click ("//form[contains(@action,'globalroleedit.php')]//input[@value='Create Role']") ;
		$this->waitForPageToLoad("30000");

		// Make it shared
		$this->click ("//input[@type='checkbox' and @name='public']") ;
		$this->click ("//input[@value='Submit']") ;
		$this->waitForPageToLoad("30000");

		// Add docmaster
		$this->type ("//form[contains(@action,'globalroleedit.php')]//input[@name='form_unix_name']", "docmaster") ;
		$this->click ("//input[@value='Add User']") ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("docmaster Lastname"));

		// Register projects
		$this->switchUser ("bigboss") ;
		$this->registerProject ("MetaProject", "bigboss") ;
		$this->approveProject ("MetaProject", "bigboss") ;
		$this->registerProject ("SubProject", "bigboss") ;
		$this->approveProject ("SubProject", "bigboss") ;

		// Add users
		$this->gotoProject ("MetaProject") ;
		$this->click("link=Admin");
		$this->waitForPageToLoad("30000");
		$this->click("link=Users and permissions");
		$this->waitForPageToLoad("30000");

		$this->type ("//form[contains(@action,'users.php')]//input[@name='form_unix_name']", "guru") ;
		$this->select("//select[@name='role_id']", "label=Senior Developer");
		$this->click ("//input[@value='Add Member']") ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("guru Lastname"));
		$this->assertTrue($this->isElementPresent("
//input[@value=
   //tr/td/a[.='guru Lastname']/../..//input[@name='user_id']/@value
]
/../td[.='Senior Developer']")) ;

		$this->type ("//form[contains(@action,'users.php')]//input[@name='form_unix_name']", "trainee") ;
		$this->select("//select[@name='role_id']", "label=Junior Developer");
		$this->click ("//input[@value='Add Member']") ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("trainee Lastname"));
		$this->assertTrue($this->isElementPresent("
//input[@value=
   //tr/td/a[.='trainee Lastname']/../..//input[@name='user_id']/@value
]
/../td[.='Junior Developer']")) ;

		$this->type ("//form[contains(@action,'users.php')]//input[@name='form_unix_name']", "docmaster") ;
		$this->select("//select[@name='role_id']", "label=Doc Writer");
		$this->click ("//input[@value='Add Member']") ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("docmaster Lastname"));
		$this->assertTrue($this->isElementPresent("
//input[@value=
   //tr/td/a[.='docmaster Lastname']/../..//input[@name='user_id']/@value
]
/../td[.='Doc Writer']")) ;

		$this->type ("//form[contains(@action,'users.php')]//input[@name='form_unix_name']", "bigboss") ;
		$this->select("//select[@name='role_id']", "label=Senior Developer");
		$this->click ("//input[@value='Add Member']") ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("bigboss Lastname"));
		$this->assertTrue($this->isElementPresent("
//input[@value=
   //tr/td/a[.='bigboss Lastname']/../..//input[@name='user_id']/@value
]
/../td[.='Senior Developer']")) ;

		// Oops, bigboss doesn't need the extra role after all
		$this->click ("
//input[@value=
   //tr/td/a[.='bigboss Lastname']/../..//input[@name='user_id']/@value
]
/../td[.='Senior Developer']/../td/input[@value='Remove']") ;
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("
//input[@value=
   //tr/td/a[.='bigboss Lastname']/../..//input[@name='user_id']/@value
]
/../td[.='Senior Developer']")) ;

		// Remove/re-add a user
		$this->click ("
//input[@value=
   //tr/td/a[.='trainee Lastname']/../..//input[@name='user_id']/@value
]
/../td[.='Junior Developer']/../td/input[@value='Remove']") ;
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isTextPresent("trainee Lastname"));

		$this->type ("//form[contains(@action,'users.php')]//input[@name='form_unix_name']", "trainee") ;
		$this->select("//select[@name='role_id']", "label=Junior Developer");
		$this->click ("//input[@value='Add Member']") ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("trainee Lastname"));
		$this->assertTrue($this->isElementPresent("
//input[@value=
   //tr/td/a[.='trainee Lastname']/../..//input[@name='user_id']/@value
]
/../td[.='Junior Developer']")) ;

		// Edit permissions of the JD role
		$this->gotoProject ("MetaProject") ;
		$this->click("link=Admin");
		$this->waitForPageToLoad("30000");
		$this->click("link=Users and permissions");
		$this->waitForPageToLoad("30000");

		$this->click ("//td[.='Junior Developer']/../td/input[@value='Edit Permissions']") ;
		$this->waitForPageToLoad("30000");

		$this->select("//select[contains(@name,'data[frs]')]", "label=View public packages only");
		$this->select("//select[contains(@name,'data[docman]')]", "label=Read only");
		$this->click ("//input[@value='Submit']") ;
		$this->waitForPageToLoad("30000");
		$this->assertSelected("//select[contains(@name,'data[docman]')]", "Read only");
		$this->assertSelected("//select[contains(@name,'data[frs]')]", "View public packages only");
		$this->select("//select[contains(@name,'data[frs]')]", "label=View all packages");
		$this->click ("//input[@value='Submit']") ;
		$this->assertSelected("//select[contains(@name,'data[frs]')]", "View all packages");
		$this->waitForPageToLoad("30000");

		// Check that SD is technician on trackers but DM isn't
		$this->click("link=Tracker");
		$this->waitForPageToLoad("30000");
		$this->click("link=Bugs");
		$this->waitForPageToLoad("30000");
		$this->click("link=Submit New");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//select[@name='assigned_to']")) ;
		$this->assertTrue($this->isElementPresent("//select[@name='assigned_to']/option[.='guru Lastname']")) ;
		$this->assertFalse($this->isElementPresent("//select[@name='assigned_to']/option[.='docmaster Lastname']")) ;

		// Check that SD is a manager on trackers but JD isn't
		$this->switchUser('guru');
		$this->gotoProject ("MetaProject") ;
		$this->click("link=Tracker");
		$this->waitForPageToLoad("30000");
		$this->click("link=Bugs");
		$this->waitForPageToLoad("30000");
		$this->click("link=Submit New");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//select[@name='assigned_to']")) ;

		$this->switchUser('trainee');
		$this->gotoProject ("MetaProject") ;
		$this->click("link=Tracker");
		$this->waitForPageToLoad("30000");
		$this->click("link=Bugs");
		$this->waitForPageToLoad("30000");
		$this->click("link=Submit New");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//select[@name='assigned_to']")) ;

		// Also check that guru isn't a manager on SubProject yet
		$this->switchUser('guru');
		$this->gotoProject ("SubProject") ;
		$this->click("link=Tracker");
		$this->waitForPageToLoad("30000");
		$this->click("link=Bugs");
		$this->waitForPageToLoad("30000");
		$this->click("link=Submit New");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//select[@name='assigned_to']")) ;

		// Mark SD role as shared
		$this->switchUser('bigboss');
		$this->gotoProject ("MetaProject") ;
		$this->click("link=Admin");
		$this->waitForPageToLoad("30000");
		$this->click("link=Users and permissions");
		$this->waitForPageToLoad("30000");
		$this->click ("//td[.='Senior Developer']/../td/input[@value='Edit Permissions']") ;
		$this->waitForPageToLoad("30000");
		$this->click ("//input[@type='checkbox' and @name='public']") ;
		$this->click ("//input[@value='Submit']") ;
		$this->waitForPageToLoad("30000");

		// Link MetaProject/SD role into SubProject
		$this->gotoProject ("SubProject") ;
		$this->click("link=Admin");
		$this->waitForPageToLoad("30000");
		$this->click("link=Users and permissions");
		$this->waitForPageToLoad("30000");

		$this->assertTrue($this->isElementPresent("//input[@value='Link external role']/../../td/select/option[.='Senior Developer (in project MetaProject)']")) ;
		$this->select("//input[@value='Link external role']/../../td/select", "label=Senior Developer (in project MetaProject)") ;
		$this->click("//input[@value='Link external role']") ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//tr/td[.='Senior Developer (in project MetaProject)']/../td/input[contains(@value,'Unlink Role')]"));

		// Grant it tracker manager permissions
		$this->click ("//td[.='Senior Developer (in project MetaProject)']/../td/input[@value='Edit Permissions']") ;
		$this->waitForPageToLoad("30000");
		$this->select("//select[contains(@name,'data[tracker]')]", "label=Manager");
		$this->click ("//input[@value='Submit']") ;
		$this->waitForPageToLoad("30000");

		// Check that guru now has manager permissions on SubProject
		$this->switchUser('guru');
		$this->gotoProject ("SubProject") ;
		$this->click("link=Tracker");
		$this->waitForPageToLoad("30000");
		$this->click("link=Bugs");
		$this->waitForPageToLoad("30000");
		$this->click("link=Submit New");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//select[@name='assigned_to']")) ;

		// Link global "Documentation masters" role into SubProject
		$this->switchUser ("bigboss") ;
		$this->gotoProject ("SubProject") ;
		$this->click("link=Admin");
		$this->waitForPageToLoad("30000");
		$this->click("link=Users and permissions");
		$this->waitForPageToLoad("30000");

		$this->assertTrue($this->isElementPresent("//input[@value='Link external role']/../../td/select/option[.='Documentation masters (global role)']")) ;
		$this->assertFalse($this->isElementPresent("//input[@value='Link external role']/../../td/select/option[.='Project moderators (global role)']")) ;
		$this->select("//input[@value='Link external role']/../../td/select", "label=Documentation masters (global role)") ;
		$this->click("//input[@value='Link external role']") ;
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//tr/td[.='Documentation masters (global role)']/../td/input[contains(@value,'Unlink Role')]"));

	}
}
?>
