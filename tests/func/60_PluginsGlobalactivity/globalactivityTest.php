<?php
/**
 * Copyright 2011, Roland Mas
 * Copyright 2013, Franck Villaume - TrivialDev
 * Copyright (C) 2015  Inria (Sylvain Beucler)
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

class Activity extends FForge_SeleniumTestCase
{
	public $fixture = 'projecta';

	function testActivity()
	{
		$this->loadAndCacheFixture();

		$this->activatePlugin('globalactivity');

		// Open a tracker item

		$this->gotoProject('ProjectA');
		$this->clickAndWait("link=Tracker");
		$this->clickAndWait("link=Bugs");
		$this->clickAndWait("link=Submit New");
		$this->type("summary", "Bug1 boustrophédon");
		$this->type("details", "brebis outremanchienne");
		$this->clickAndWait("//form[@id='trackeraddform']//input[@type='submit']");
		$this->clickAndWait("link=Bug1 boustrophédon");
		$this->type("details", 'Ceci était une référence au « Génie des Alpages », rien à voir avec Charlie - also, ZONGO, and needle');
		$this->clickAndWait("submit");

		// Create a task

		$this->gotoProject('ProjectA');
		$this->clickAndWait("link=Tasks");
		$this->clickAndWait("link=To Do");
		$this->clickAndWait("link=Add Task");
		$this->type("summary", "Task1 the brain");
		$this->type("details", "The same thing we do every night, Pinky - try to take over the world! - also, ZONGO");
		$this->type("hours", "199");
		$this->clickAndWait("submit");

		$this->clickAndWait("link=Task1 the brain");
		$this->type("details", 'This is the needle for tasks');
		$this->clickAndWait("submit");

		// Post a message in a forum

		$this->gotoProject('ProjectA');
		$this->clickAndWait("link=Forums");
		$this->clickAndWait("link=open-discussion");
		$this->click("link=Start New Thread");
		$this->waitForPageToLoad("30000");
		$this->type("subject", "Message1 in a bottle");
		$this->type("body", "ninetynine of them on Charlie's wall - also, ZONGO");
		$this->clickAndWait("submit");

		$this->createAndGoto('ProjectB');
		$this->clickAndWait("link=Forums");
		$this->clickAndWait("link=open-discussion");
		$this->click("link=Start New Thread");
		$this->waitForPageToLoad("30000");
		$this->type("subject", "Message2");
		$this->type("body", "Forum post in project B");
		$this->clickAndWait("submit");

		// Create a document

		$this->gotoProject('ProjectA');
		$this->clickAndWait("link=Docs");
		$this->clickAndWait("addItemDocmanMenu");
		// ugly hack until we fix behavior in docman when no folders exist. We need to click twice on the link
		$this->clickAndWait("addItemDocmanMenu");
		$this->click("id=tab-new-document");
		$this->type("title", "Doc1 Vladimir");
		$this->type("//input[@name='description']", "Main website (the needle) - also, ZONGO");
		$this->click("//input[@name='type' and @value='pasteurl']");
		$this->type("file_url", "http://fusionforge.org/");
		$this->clickAndWait("submit");

		// Create some news

		$this->gotoProject('ProjectA');
		$this->clickAndWait("link=News");
		$this->clickAndWait("link=Submit");
		$this->type("summary", "News1 daily planet");
		$this->type("details", "Clark Kent's newspaper - also, ZONGO");
		$this->clickAndWait("submit");

		// Check global activity

		$this->open(ROOT.'/plugins/globalactivity/');
		$this->assertTrue($this->isTextPresent("Bug1"));
		$this->assertTrue($this->isTextPresent("Task1"));
		$this->assertTrue($this->isTextPresent("Message1"));
		$this->assertTrue($this->isTextPresent("Document http://fusionforge.org/"));
		$this->assertTrue($this->isTextPresent("News1"));

		// Also check anonymously

		$this->logout();
		$this->open(ROOT.'/plugins/globalactivity/');
		$this->assertTrue($this->isTextPresent("Bug1"));
		$this->assertTrue($this->isTextPresent("Task1"));
		$this->assertTrue($this->isTextPresent("Message1"));
		$this->assertFalse($this->isTextPresent("Document http://fusionforge.org/"));
		$this->assertTrue($this->isTextPresent("News1"));

		// Check SOAP

		$soapclient = new SoapClient(WSDL_URL);
		$this->assertNotNull($soapclient);
		
		$userid = FORGE_ADMIN_USERNAME;
		$passwd = FORGE_ADMIN_PASSWORD;
		
		$response = $soapclient->login($userid, $passwd);
		$session = $response;
		
		$response = $soapclient->globalactivity_getActivity($session,time()-3600,time(),array());
		$found = False;
		foreach ($response as $data) {
			if ($data->description == 'Welcome to developers-discussion') {
				$found = True;
				break;
			}
		}
		$this->assertTrue($found);

		$response = $soapclient->globalactivity_getActivity($session,time()-3600,time(),array('forumpost'));
		$found = False;
		foreach ($response as $data) {
			if ($data->description == 'Welcome to developers-discussion') {
				$found = True;
				break;
			}
		}
		$this->assertTrue($found);
		$found = False;
		foreach ($response as $data) {
			if ($data->description == 'Message1 in a bottle') {
				$found = True;
				break;
			}
		}
		$this->assertTrue($found);
		$found = False;
		foreach ($response as $data) {
			if ($data->description == 'Message2') {
				$found = True;
				break;
			}
		}
		$this->assertTrue($found);

		// Now restrict to ProjectA only
		$response = $soapclient->globalactivity_getActivityForProject($session,time()-3600,time(),7,array('forumpost'));
		$found = False;
		foreach ($response as $data) {
			if ($data->description == 'Welcome to developers-discussion') {
				$found = True;
				break;
			}
		}
		$this->assertTrue($found);
		$found = False;
		foreach ($response as $data) {
			if ($data->description == 'Message1 in a bottle') {
				$found = True;
				break;
			}
		}
		$this->assertTrue($found);
		$found = False;
		foreach ($response as $data) {
			if ($data->description == 'Message2') {
				$found = True;
				break;
			}
		}
		$this->assertFalse($found);

		$response = $soapclient->globalactivity_getActivity($session,time()-3600,time(),array('scmsvn'));
		$found = False;
		foreach ($response as $data) {
			if ($data->description == 'Welcome to developers-discussion') {
				$found = True;
				break;
			}
		}
		$this->assertFalse($found);

		// Now change permissions

		$this->login(FORGE_ADMIN_USERNAME);
		$this->gotoProject('ProjectA');
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Users and permissions");
		$this->clickAndWait ("//td/form/div[contains(.,'Anonymous')]/../div/input[@value='Edit Permissions']") ;
		$this->select("//select[contains(@name,'data[project_read]')]", "label=Visible");
		$this->select("//tr/td[.='Bugs']/../td/select[contains(@name,'data[tracker]')]", "label=No Access");
		$this->select("//tr/td[.='Patches']/../td/select[contains(@name,'data[tracker]')]", "label=No Access");
		$this->select("//tr/td[.='To Do']/../td/select[contains(@name,'data[pm]')]", "label=No Access");
		$this->select("//tr/td[.='Next Release']/../td/select[contains(@name,'data[pm]')]", "label=No Access");
		$this->select("//tr/td[.='open-discussion']/../td/select[contains(@name,'data[forum]')]", "label=No Access");
		$this->select("//tr/td[.='developers-discussion']/../td/select[contains(@name,'data[forum]')]", "label=No Access");
		$this->select("//select[contains(@name,'data[docman]')]", "label=Read only");
		$this->clickAndWait ("//input[@value='Submit']") ;

		// Recheck perms on anonymous global activity page

		$this->logout();
		$this->open(ROOT.'/plugins/globalactivity/');
		$this->assertFalse($this->isTextPresent("Bug1"));
		$this->assertFalse($this->isTextPresent("Task1"));
		$this->assertFalse($this->isTextPresent("Message1"));
		$this->assertTrue($this->isTextPresent("Document http://fusionforge.org/"));
		$this->assertTrue($this->isTextPresent("News1"));

	}
}
