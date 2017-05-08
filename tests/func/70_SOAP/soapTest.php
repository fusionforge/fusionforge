<?php
/**
 * Copyright (C) 2014 Roland Mas
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

class SoapTest extends FForge_SeleniumTestCase
{
	public $fixture = 'projecta';

	function setUp() {
		parent::setUp();

		$this->session = NULL;
		$this->soapclient = NULL;

		$this->soapclient = new SoapClient(WSDL_URL,
					array('cache_wsdl' => WSDL_CACHE_NONE,
						'stream_context' => stream_context_create(array('ssl' => array('verify_peer' => false,
														'verify_peer_name' => false,
														'allow_self_signed' => true)))));

		$this->assertNotNull($this->soapclient);
	}

	function testSoap()
	{
		$this->loadAndCacheFixture();

		$userid = FORGE_ADMIN_USERNAME;
		$passwd = FORGE_ADMIN_PASSWORD;

		// Check version number
		$version = $this->soapclient->version();
		$this->assertRegexp('/^[-0-9.+a-z~]*$/', $version);

		// Check login
		$response = $this->soapclient->login($userid, $passwd);
		$this->assertNotNull($response);
		$this->session = $response;

		// Check logging in with nonexisting user
		try {
			$response = $this->soapclient->login('coin', 'pan');
			$this->fail('An expected exception has not been raised.');
		}
		catch (SoapFault $expected) {
			$this->assertEquals("Unable to log in with username of coin", $expected->faultstring);
		}

		// Check logging in with wrong password
		try {
			$response = $this->soapclient->login(FORGE_ADMIN_USERNAME, 'pan');
			$this->fail('An expected exception has not been raised.');
		}
		catch (SoapFault $expected) {
			$this->assertEquals("Unable to log in with username of ".FORGE_ADMIN_USERNAME, $expected->faultstring);
		}

		// Get list of groups with empty parameters
		try {
			$response = $this->soapclient->getGroupsByName($this->session);
			$this->fail('An expected exception has not been raised. Got response :'.$response);
		}
		catch (SoapFault $expected) {
			$this->assertTrue( strpos($expected->faultstring, 'Could Not Get Projects by Name') === 0);
		}

		// Get one group
		$response = $this->soapclient->getGroupsByName($this->session,array('projecta'));
		$group = $response[0];
		$this->assertEquals('projecta', $group->unix_group_name);
		$projecta = $group;

		// Get several groups
		$response = $this->soapclient->getGroupsByName($this->session,array('tmpl', 'projecta'));
		foreach ($response as $group) {
			$response2 = $this->soapclient->getGroups($this->session, array($group->group_id));
			$group2 = $response2[0];
			$this->assertEquals($group->group_id, $group2->group_id);
			$this->assertEquals($group->unix_group_name, $group2->unix_group_name);
		}

		// Check trackers
		$trackers = $this->soapclient->getArtifactTypes($this->session, $projecta->group_id);
		$found = false;
		foreach ($trackers as $t) {
			if ($t->name == 'Bugs') {
				$found = true;
				$tracker = $t;
			}
		}
		$this->assertTrue($found, "Trackers 'Bugs' not found");

		$response = $this->soapclient->addArtifact($this->session, $projecta->group_id, $tracker->group_artifact_id, 1, 3, 100, "Bug submitted by SOAP", "Bug details are not really relevant here", array());

		$this->switchUser(FORGE_ADMIN_USERNAME);
		$this->gotoProject('ProjectA');
		$this->clickAndWait("link=Tracker");
		$this->clickAndWait("link=Bugs");
		$this->assertTrue($this->isTextPresent("Bug submitted by SOAP"));
		$this->clickAndWait("link=Bug submitted by SOAP");
		$this->type("summary", 'Bug summary edited via web interface');
		$this->clickAndWait("submit");

		$bugs = $this->soapclient->getArtifacts($this->session, $projecta->group_id, $tracker->group_artifact_id, 0, 0);
		$bug = $bugs[0];
		$this->assertEquals('Bug summary edited via web interface', $bug->summary);
	}
}
