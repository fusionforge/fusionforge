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

class SoftwareHeritage extends FForge_SeleniumTestCase
{
	public $fixture = 'projecta';

	function testSoftwareHeritage()
	{
		$this->loadAndCacheFixture();

		$this->activatePlugin('softwareheritage');
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
		$response = $soapclient->softwareheritage_repositoryList($session);
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
		$response = $soapclient->softwareheritage_repositoryList('');
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
	}
}
