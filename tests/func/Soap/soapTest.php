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

require_once dirname(dirname(__FILE__)).'/Testing/SeleniumForge.php';

class SoapTest extends FForge_SeleniumTestCase
{
    function setUp() {
        parent::setUp();

        $this->session = NULL;
        $this->soapclient = NULL;
        
        $this->soapclient = new SoapClient(WSDL_URL,
        array('cache_wsdl' => WSDL_CACHE_NONE,       
        'trace' => true));
        
        $this->assertNotNull($this->soapclient);
    }        
        
	function testSoap()
	{
		$this->populateStandardTemplate('empty');
		$this->init();

        $userid = FORGE_ADMIN_USERNAME;
        $passwd = FORGE_ADMIN_PASSWORD;

        // Check version number
        $version = $this->soapclient->version();
        $this->assertRegexp('/^[-0-9.+]*$/', $version);

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
            $this->assertEquals("Unable to log in with userid of coin", $expected->faultstring);
        }
        
        // Check logging in with wrong password
        try {
            $response = $this->soapclient->login(FORGE_ADMIN_USERNAME, 'pan');
            $this->fail('An expected exception has not been raised.');
        }
        catch (SoapFault $expected) {
            $this->assertEquals("Unable to log in with userid of ".FORGE_ADMIN_USERNAME, $expected->faultstring);
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
        
        // Get several groups
        $response = $this->soapclient->getGroupsByName($this->session,array('tmpl', 'projecta'));
        foreach ($response as $group) {
            $response2 = $this->soapclient->getGroups($this->session, array($group->group_id));
            $group2 = $response2[0];
            $this->assertEquals($group->group_id, $group2->group_id);
            $this->assertEquals($group->unix_group_name, $group2->unix_group_name);
        }
	}
}
?>
