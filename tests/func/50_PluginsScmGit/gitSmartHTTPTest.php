<?php
/**
 * Copyright (C) 2012 Roland Mas
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

class ScmGitSmartHTTPTest extends FForge_SeleniumTestCase
{
	function testScmGitSmartHTTP()
	{
		$this->changeConfig("[core]\nuse_ssl = no\n");

		$this->activatePlugin('scmgit');
		$this->populateStandardTemplate('empty');
		$this->init();

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->click("//input[@name='scmengine[]' and @value='scmgit']");
		$this->clickAndWait("submit");
	    
		// Create repositories
		$this->waitSystasks();

		// Get the address of the repo
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$p = $this->getText("//tt[contains(.,'git clone http') and contains(.,'".FORGE_ADMIN_USERNAME."@')]");
		$p = preg_replace(",^git clone ,", "", $p);
		$p = preg_replace(",@,", ":".FORGE_ADMIN_PASSWORD."@", $p);
		$timeout = "timeout 15s";

		// Create a local clone, add stuff, push it to the repo
		$t = exec("mktemp -d /tmp/gitTest.XXXXXX");
		system("cd $t && GIT_SSL_NO_VERIFY=true $timeout git clone --quiet $p", $ret);
		if ($ret >= 120) {
			system("cd $t && GIT_SSL_NO_VERIFY=true $timeout git clone --quiet $p", $ret);
		}
		$this->assertEquals(0, $ret);

		system("echo 'this is a simple text' > $t/projecta/mytext.txt");
		system("cd $t/projecta && $timeout git add mytext.txt && $timeout git commit --quiet -a -m'Adding file'", $ret);
		system("echo 'another simple text' >> $t/projecta/mytext.txt");
		system("cd $t/projecta && git commit --quiet -a -m'Modifying file'", $ret);
		$this->assertEquals(0, $ret);

		system("cd $t/projecta && GIT_SSL_NO_VERIFY=true $timeout git push --quiet --all", $ret);
		if ($ret >= 120) {
			system("cd $t/projecta && GIT_SSL_NO_VERIFY=true $timeout git push --quiet --all", $ret);
		}
		$this->assertEquals(0, $ret);

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

        // Check gitweb directly
        $this->open("http://scm.".HOST.ROOT."/anonscm/gitweb/?p=projecta/projecta.git");
		$this->assertElementPresent("//.[@class='page_footer']");
		$this->assertTextPresent("projecta.git");
		$this->clickAndWait("link=projecta.git");
		$this->assertTextPresent("Modifying file");
		$this->assertTextPresent("Adding file");

        // Disable anonymous access to gitweb
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->click("link=Admin");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Project Information"));
		$this->click("link=Users and permissions");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Current Project Members"));
		$this->click("//tr/td/form/div[contains(.,'Anonymous')]/../../../td/form/div/input[contains(@value,'Unlink Role')]");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Role unlinked successfully"));

		// Update repositories
		$this->waitSystasks();

        // Check that gitweb now fails
        $this->open("http://scm.".HOST.ROOT."/anonscm/gitweb/?p=projecta/projecta.git");
		$this->assertElementPresent("//.[@class='page_footer']");
		$this->assertTextNotPresent("projecta.git");

        // Now try to use the authenticated gitweb
        $this->open("http://".FORGE_ADMIN_USERNAME.":".FORGE_ADMIN_PASSWORD."@scm.".HOST.ROOT."/authscm/".FORGE_ADMIN_USERNAME."/gitweb/projecta/");
		$this->assertElementPresent("//.[@class='page_footer']");
		$this->assertTextPresent("projecta.git");
		$this->selectFrame("relative=top");

		// Also check via the standard page
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

        // Set up a different user
        $this->createUser ('otheruser') ;
        $this->createAndGoto ('projectb');
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Users and permissions");
		$this->type ("//form[contains(@action,'users.php')]//input[@name='form_unix_name' and @type='text']", "otheruser") ;
		$this->select("//input[@value='Add Member']/../select[@name='role_id']", "label=Admin");
		$this->clickAndWait ("//input[@value='Add Member']") ;
		$this->assertTrue($this->isTextPresent("otheruser Lastname"));
		$this->assertTrue($this->isElementPresent("//tr/td/a[.='otheruser Lastname']/../../td/div[contains(.,'Admin')]")) ;
		$this->clickAndWait("//tr/td/form/div[contains(.,'Anonymous')]/../../../td/form/div/input[contains(@value,'Unlink Role')]");
		$this->assertTrue($this->isTextPresent("Role unlinked successfully"));

		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->click("//input[@name='scmengine[]' and @value='scmgit']");
		$this->clickAndWait("submit");

		// Create repositories
		$this->waitSystasks();

        // Try with a different user
        $this->open("http://otheruser:".FORGE_OTHER_PASSWORD."@scm.".HOST.ROOT."/authscm/otheruser/gitweb/projecta/");
		$this->assertElementPresent("//.[@class='page_footer']");
		$this->assertTextNotPresent("projecta.git");

        // Test accessing admin's URL with otheruser's credentials (and asserting we get a 401)
        // …Selenium doesn't allow checking HTTP return codes, so use a file_get_contents() hack
        // First make sure that the hack works
        $f = @file_get_contents("http://otheruser:".FORGE_OTHER_PASSWORD."@scm.".HOST.ROOT."/authscm/otheruser/gitweb/projecta/", "r");
        $this->assertTrue(is_string($f));
        $this->assertEquals(1, preg_match('/projectb.git/',$f));
        // Then make sure we detect a failure
        $f = @file_get_contents("http://otheruser:".FORGE_OTHER_PASSWORD."@scm.".HOST.ROOT."/authscm/".FORGE_ADMIN_USERNAME."/gitweb/projecta/", "r");
        $this->assertFalse($f);

		system("rm -fr $t");
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
