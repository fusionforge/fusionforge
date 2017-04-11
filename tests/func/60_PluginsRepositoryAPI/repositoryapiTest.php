<?php
/**
 * Copyright 2016-2017, Roland Mas
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

class RepositoryAPI extends FForge_SeleniumTestCase
{
	public $fixture = 'projecta';

	function testRepositoryAPI()
	{
		$this->loadAndCacheFixture();

		$this->activatePlugin('repositoryapi');
		$this->activatePlugin('scmgit');

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->check("//input[@name='scmengine[]' and @value='scmgit']");
		$this->clickAndWait("submit");

		$this->type("//input[@name='repo_name']", "other-repo");
		$this->type("//input[@name='description']", "Description for second repository");
		$this->clickAndWait("//input[@value='Submit']");
		$this->assertTextPresent("New repository other-repo registered");

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->assertTextPresent("other-repo");

		$this->assertTextPresent("Anonymous Access");
		$this->clickAndWait("link=Request a personal repository");
		$this->assertTextPresent("You have now requested a personal Git repository");

		$this->createProject('ProjectB','scmsvn');

		// Run the cronjob to create repositories
		$this->waitSystasks();

		sleep(2);
		$t0 = time();

		// Get the address of the Git repo
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$p = $this->getText("//tt[contains(.,'git clone http') and contains(.,'".FORGE_ADMIN_USERNAME."@') and contains(.,'projecta.git')]");
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

		// Push a second batch of changes after a while
		sleep(2);
		system("echo 'yet another simple text' >> $t/projecta/mytext.txt");
		system("cd $t/projecta && git commit --quiet -a -m'Modifying file again'", $ret);
		$this->assertEquals(0, $ret);
		$this->runCommandTimeout("$t/projecta", "git push --quiet --all", "GIT_SSL_NO_VERIFY=true");
		system("cd $t/projecta && git commit --quiet -a -m'Modifying file again'", $ret);

		// Push a third batch of changes, on a branch
		system("cd $t/projecta && git checkout -b testbranch", $ret);
		system("echo 'text on a branch' >> $t/projecta/mytext.txt");
		system("cd $t/projecta && git commit --quiet -a -m'Modifying file on the branch'", $ret);
		$this->assertEquals(0, $ret);
		$this->runCommandTimeout("$t/projecta", "git push --quiet --all", "GIT_SSL_NO_VERIFY=true");

		// Get the address of the personal repo
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$p = $this->getText("//tt[contains(.,'git clone http') and contains(.,'".FORGE_ADMIN_USERNAME."@') and contains(.,'".FORGE_ADMIN_USERNAME.".git')]");
		$p = preg_replace(",^git clone ,", "", $p);
		$p = preg_replace(",@,", ":".FORGE_ADMIN_PASSWORD."@", $p);

		// Create a local clone, add stuff, push it to the repo
		$t = exec("mktemp -d /tmp/gitTest.XXXXXX");
		$this->runCommandTimeout($t, "git clone --quiet $p", "GIT_SSL_NO_VERIFY=true");

		system("echo 'text in personal repo' > $t/".FORGE_ADMIN_USERNAME."/mytext.txt");
		system("cd $t/".FORGE_ADMIN_USERNAME." && git add mytext.txt && git commit --quiet -a -m'Adding file'", $ret);
		$this->assertEquals(0, $ret);
		$this->runCommandTimeout("$t/".FORGE_ADMIN_USERNAME, "git push --quiet --all", "GIT_SSL_NO_VERIFY=true");


		// Get the address of the SVN repo
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectB");
		$this->clickAndWait("link=SCM");
		$p = $this->getText("//tt[contains(.,'svn checkout --username ".FORGE_ADMIN_USERNAME." http')]");
		$p = preg_replace(",^svn checkout --username ".FORGE_ADMIN_USERNAME." ,", "", $p);

		// Create a local clone, add stuff, push it to the repo
		$t = exec("mktemp -d /tmp/svnTest.XXXXXX");
		$auth = "--username ".FORGE_ADMIN_USERNAME." --password ".FORGE_ADMIN_PASSWORD;
		$globalopts = "--trust-server-cert --non-interactive";
		$this->runCommandTimeout($t, "svn checkout $globalopts $auth $p projectb");
		sleep(2);
		$this->runCommand("echo 'this is a simple text' > $t/projectb/mytext.txt");
		$this->runCommandTimeout("$t/projectb", "svn add mytext.txt");
		$this->runCommandTimeout("$t/projectb", "svn commit $globalopts $auth -m'Adding file'");
		sleep(2);
		$this->runCommand("echo 'another simple text' >> $t/projectb/mytext.txt");
		$this->runCommandTimeout("$t/projectb", "svn commit $globalopts $auth -m'Modifying file'");
		sleep(2);


		// Collect activities
		$this->cron_for_plugin("parse_scm_repo_activities.php", "repositoryapi");


		// Check SOAP
		$soapclient = new SoapClient(WSDL_URL,
		array('cache_wsdl' => WSDL_CACHE_NONE,
		'trace' => true));
		$this->assertNotNull($soapclient);
		
		$userid = FORGE_ADMIN_USERNAME;
		$passwd = FORGE_ADMIN_PASSWORD;
		
		$response = $soapclient->login($userid, $passwd);
		$session = $response;
		$this->assertNotEquals($session,"");

		// Get repository list as admin
		$response = $soapclient->repositoryapi_repositoryList($session,100,0);
		$repos = array();
		foreach ($response as $data) {
			$repos[$data->repository_id] = $data;
		}
		$this->assertTrue(array_key_exists('projecta/git/projecta',$repos));
		$this->assertEquals(3,count($repos['projecta/git/projecta']->repository_urls));
		$this->assertTrue(array_key_exists('projecta/git/other-repo',$repos));
		$this->assertTrue(array_key_exists('projecta/git/users/admin',$repos));

		$this->assertTrue(array_key_exists('projectb/svn/projectb',$repos));
		$this->assertEquals(4,count($repos['projectb/svn/projectb']->repository_urls));

		// Get repository list as anonymous
		$response = $soapclient->repositoryapi_repositoryList('',100,0);
		$repos = array();
		foreach ($response as $data) {
			$repos[$data->repository_id] = $data;
		}
		$this->assertTrue(array_key_exists('projecta/git/projecta',$repos));
		$this->assertEquals(1,count($repos['projecta/git/projecta']->repository_urls));
		$this->assertTrue(array_key_exists('projecta/git/other-repo',$repos));
		$this->assertTrue(array_key_exists('projecta/git/users/admin',$repos));

		$this->assertTrue(array_key_exists('projectb/svn/projectb',$repos));
		$this->assertEquals(2,count($repos['projectb/svn/projectb']->repository_urls));

		// Get repository info as admin
		$response = $soapclient->repositoryapi_repositoryInfo($session,'projecta/git/projecta');
		$this->assertNotEquals(NULL,$response);
		$this->assertEquals(3,count($response->repository_urls));
		
		$response = $soapclient->repositoryapi_repositoryInfo($session,'projectb/svn/projectb');
		$this->assertNotEquals(NULL,$response);
		$this->assertEquals(4,count($response->repository_urls));

		// Get activities for repositories		
		$response = $soapclient->repositoryapi_repositoryActivity($session,$t0,time(),0,0);
		$this->assertNotEquals(NULL,$response);
		$this->assertEquals(5,count($response->activities));
		// Check limit/offset
		$response = $soapclient->repositoryapi_repositoryActivity($session,$t0,time(),2,0);
		$this->assertNotEquals(NULL,$response);
		$this->assertEquals(2,count($response->activities));
		$response = $soapclient->repositoryapi_repositoryActivity($session,$t0,time(),0,2);
		$this->assertNotEquals(NULL,$response);
		$this->assertEquals(3,count($response->activities));
		// Check time range
		sleep(15);
		$response = $soapclient->repositoryapi_repositoryActivity($session,time()-10,time(),0,0);
		$this->assertNotEquals(NULL,$response);
		$this->assertEquals(0,count($response->activities));
	}
}
