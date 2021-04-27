<?php
/**
 * Copyright (C) 2012 Roland Mas
 * Copyright (C) 2015  Inria (Sylvain Beucler)
 * Copyright 2019, Franck Villaume - TrivialDev
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

class ScmGitSmartHTTPTest extends FForge_SeleniumTestCase
{
	public $fixture = 'projecta';

	function testScmGitSmartHTTP()
	{
		$this->loadAndCacheFixture();

		$this->activatePlugin('scmgit');

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->clickAndWait("//input[@name='scmengine[]' and @value='scmgit']");
		$this->clickAndWait("submit");

		// Create repositories
		$this->waitSystasks();

		// Get the address of the repo
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->clickAndWait("jquery#tabber-gitsmarthttp");
		$p = $this->getText("//kbd[contains(.,'git clone http') and contains(.,'".FORGE_ADMIN_USERNAME."@')]");
		$p = preg_replace(",^git clone ,", "", $p);
		$p = preg_replace(",@,", ":".FORGE_ADMIN_PASSWORD."@", $p);

		// Create a local clone, add stuff, push it to the repo
		$t = exec("mktemp -d /tmp/gitTest.XXXXXX");
		$this->runCommandTimeout($t, "git clone --quiet $p", "GIT_SSL_NO_VERIFY=true");

		system("echo 'this is a simple text' > $t/projecta/mytext.txt");
		system("cd $t/projecta && git add mytext.txt && git commit --quiet -a -m'Adding file'", $ret);
		system("echo 'another simple text' >> $t/projecta/mytext.txt");
		system("cd $t/projecta && git commit --quiet -a -m'Modifying file'", $ret);
		$this->assertEquals(0, $ret);

		$this->runCommandTimeout("$t/projecta", "git push --quiet --all", "GIT_SSL_NO_VERIFY=true");

		// Check that the changes appear in gitweb
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->clickAndWait("link=Browse main git repository");
		$this->selectFrame("id=scmgit_iframe");
		$this->assertElementPresent("//.[@class='page_footer']");
		$this->assertTextPresent("projecta.git");
		$this->clickAndWait("link=projecta.git");
		$this->assertTextPresent("Modifying file");
		$this->assertTextPresent("Adding file");
		$this->selectFrame("relative=top");

		// Check gitweb directly
		$this->openWithOneRetry("https://scm.".HOST.ROOT."/anonscm/gitweb/?p=projecta/projecta.git");
		$this->assertElementPresent("//.[@class='page_footer']");
		$this->assertTextPresent("projecta.git");
		$this->clickAndWait("link=projecta.git");
		$this->assertTextPresent("Modifying file");
		$this->assertTextPresent("Adding file");

		// Disable anonymous access to gitweb
		$this->openWithOneRetry(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->waitForPageToLoad();
		$this->assertTrue($this->isTextPresent("Project Information"));
		$this->clickAndWait("link=Users and permissions");
		$this->waitForPageToLoad();
		$this->assertTrue($this->isTextPresent("Current Project Members"));
		$this->clickAndWait("//tr/td/form/div[contains(.,'Anonymous')]/../../../td/form/div/input[contains(@value,'Unlink Role')]");
		$this->waitForPageToLoad();
		$this->assertTrue($this->isTextPresent("Role unlinked successfully"));

		// Update repositories
		$this->waitSystasks();

		// Check that gitweb now fails
		$this->openWithOneRetry("https://scm.".HOST.ROOT."/anonscm/gitweb/?p=projecta/projecta.git");
		$this->assertElementPresent("//.[@class='page_footer']");
		$this->assertTextNotPresent("projecta.git");

		// Now try to use the authenticated gitweb
		$this->openWithOneRetry("https://".FORGE_ADMIN_USERNAME.":".FORGE_ADMIN_PASSWORD."@scm.".HOST.ROOT."/authscm/".FORGE_ADMIN_USERNAME."/gitweb/?p=projecta/projecta.git");
		$this->assertElementPresent("//.[@class='page_footer']");
		$this->assertTextPresent("projecta.git");

		// Also check via the standard page
		$this->openWithOneRetry(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->clickAndWait("link=Browse main git repository");
		$this->selectFrame("id=scmgit_iframe");
		$this->assertElementPresent("//.[@class='page_footer']");
		$this->assertTextPresent("projecta.git");
		$this->clickAndWait("link=projecta.git");
		$this->assertTextPresent("Modifying file");
		$this->assertTextPresent("Adding file");
		$this->selectFrame("relative=top");

		// Set up a different user
		$this->createUser ('otheruser') ;
		$this->createAndGoto ('projectb');
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Users and permissions");
		$this->type ("//form[contains(@action,'users.php')]//input[@name='form_unix_name' and @type='text']", "otheruser") ;
		$this->select($this->byXPath("//input[@value='Add Member']/../fieldset/select[@name='role_id']"))->selectOptionByLabel("Admin");
		$this->clickAndWait ("//input[@value='Add Member']") ;
		$this->assertTrue($this->isTextPresent("otheruser Lastname"));
		// TODO: need to check line below. Xpath wrong ?
		//$this->assertTrue($this->isElementPresent("//tr/td/a[.='otheruser Lastname']/../../td/div[contains(.,'Admin')]")) ;
		$this->clickAndWait("//tr/td/form/div[contains(.,'Anonymous')]/../../../td/form/div/input[contains(@value,'Unlink Role')]");
		$this->assertTrue($this->isTextPresent("Role unlinked successfully"));

		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->clickAndWait("//input[@name='scmengine[]' and @value='scmgit']");
		$this->clickAndWait("submit");

		// Create repositories
		$this->waitSystasks();

		// Try with a different user
		$this->openWithOneRetry("https://otheruser:".FORGE_OTHER_PASSWORD."@scm.".HOST.ROOT."/authscm/otheruser/gitweb/?p=projecta/projecta.git");
		$this->assertElementPresent("//.[@class='page_footer']");
		$this->assertTextNotPresent("projecta.git");

		// Test accessing admin's URL with otheruser's credentials (and asserting we get a 401)
		// …Selenium doesn't allow checking HTTP return codes, so use a file_get_contents() hack
		// First make sure that the hack works
		$opts = array(
			'ssl'=>array(
				'verify_peer'=>false,
				'verify_peer_name'=>false,
			)
		);
		$context = stream_context_create($opts);
		$f = @file_get_contents("https://otheruser:".FORGE_OTHER_PASSWORD."@scm.".HOST.ROOT."/authscm/otheruser/gitweb/", "r", $context);
		$this->assertTrue(is_string($f));
		$this->assertEquals(1, preg_match('/projectb.git/',$f));
		// Then make sure we detect a failure
		$f = @file_get_contents("https://otheruser:".FORGE_OTHER_PASSWORD."@scm.".HOST.ROOT."/authscm/".FORGE_ADMIN_USERNAME."/gitweb/projecta/", "r", $context);
		$this->assertFalse($f);
		system("rm -fr $t");
	}
}
