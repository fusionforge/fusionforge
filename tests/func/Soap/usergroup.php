<?php
/*
 * Copyright (C) 2009 Olivier Berger, Institut TELECOM
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

require_once 'PHPUnit/Framework/TestCase.php';

class SoapUserGroupProcess extends PHPUnit_Framework_TestCase
{

	function setUp()
	{

		//	  print_r("setup\n");

		$this->loggedIn = FALSE;
		$this->session = NULL;
		//print_r("session :".$this->session);

		$this->assertRegExp('/http.?:\/\//', WSDL_URL);

		//	  try {

		$this->soapclient = new SoapClient(WSDL_URL,
		array('cache_wsdl' => WSDL_CACHE_NONE,
						   'trace' => true));
		 
		//	  } catch (SoapFault $fault) {
		//	    $fault->faultstring;
		//	    print_r($fault);
		//	  }
		//	  print_r($this->soapclient);

}

// function tearDown()
// {
//   //
	// }


	// performs a login and returns a session "cookie"
	function login($userid, $passwd)
	{
		$response = $this->soapclient->login($userid, $passwd);

		if ($response) {
			$this->loggedIn = TRUE;

			$this->session = $response;

			//	    print_r($this->session);

		}

		return $response;
	}

	function testLoginSuccesful()
	{
		$userid = EXISTING_USER;
		$passwd = PASSWD_OF_EXISTING_USER;

		$response = $this->login($userid, $passwd);

		$this->assertNotNull($response);

	}


	// Name: getGroups
	// Binding: GForgeAPIBinding
	// Style: rpc
	// Input:
	//   use: encoded
	//   message: getGroupsRequest
	//   parts:
	//     session_ser: xsd:string
	//     group_ids: tns:ArrayOfint
	// Output:
	//   use: encoded
	//   message: getGroupsResponse
	//   parts:
	//     return: tns:ArrayOfGroup

	// Name: getGroupsByName
	// Binding: GForgeAPIBinding
	// Input:
	//   use: encoded
	//   message: getGroupsByNameRequest
	//   parts:
	//     session_ser: xsd:string
	//     group_names: tns:ArrayOfstring
	// Output:
	//   use: encoded
	//   message: getGroupsByNameResponse
	//   parts:
	//     return: tns:ArrayOfGroup

	/**
	 * @depends testLoginSuccesful
	 */
	function testGetGroupsByNameEmpty()
	{

		//	  print_r($this->loggedIn);

		$this->login(EXISTING_USER, PASSWD_OF_EXISTING_USER);
		$this->assertNotNull($this->loggedIn);
		$this->assertNotNull($this->session);

		try {
			$response = $this->soapclient->getGroupsByName($this->session);
		}
		catch (SoapFault $expected) {
			//	    print_r($expected->faultstring);
			 
			// Use strpos instead of assertStringStartsWith (for PHPunit 3.3 compatibility)
			$this->assertTrue( strpos($expected->faultstring, 'Could Not Get Groups by Name') === 0);
			//$this->assertStringStartsWith('Could Not Get Groups by Name', $expected->faultstring);

			return;
		}

		$this->fail('An expected exception has not been raised. Got response :'.$response);
	}

	/**
	 * @depends testLoginSuccesful
	 */
	function testGetGroupsByName()
	{

		//	  print_r($this->loggedIn);

		$this->login(EXISTING_USER, PASSWD_OF_EXISTING_USER);
		$this->assertNotNull($this->loggedIn);
		$this->assertNotNull($this->session);

		$groups = array('template' => 'template',
			  'stats' => 'stats',
			  'peerrating' => 'peerrating',
			  'siteadmin' => 'siteadmin',
			  'newsadmin' => 'newsadmin'
			  );

			  // individual retrieval for each of the default groups
			  foreach (array_keys($groups) as $group_name) {
			  	$response = $this->soapclient->getGroupsByName($this->session,array($group_name));

			  	$group = $response[0];
			  	//	    print_r($group);
			  	$this->assertEquals($group_name, $group->unix_group_name);
			  }

			  // retrieval of a list of groups
			  $response = $this->soapclient->getGroupsByName($this->session, array_keys($groups));
			  foreach ($response as $group) {
			  	//	    print_r($group);
			  	$this->assertEquals($groups[$group->unix_group_name], $group->unix_group_name);

			  	$group_id = $group->group_id;

			  	// now verify that getGroups() returns the same
			  	$response = $this->soapclient->getGroups($this->session, array($group_id));
			  	$group2 = $response[0];
			  	//	    print_r($group);
			  	$this->assertEquals($group_id, $group2->group_id);
			  	$this->assertEquals($group->unix_group_name, $group2->unix_group_name);
			  }
			   
	}


	// Name: getPublicProjectNames
	// Binding: GForgeAPIBinding
	// Input:
	//   use: encoded
	//   message: getPublicProjectNamesRequest
	//   parts:
	//     session_ser: xsd:string
	// Output:
	//   use: encoded
	//   message: getPublicProjectNamesResponse
	//   parts:
	//     return: tns:ArrayOfstring


	/**
	 * @depends testLoginSuccesful
	 */
	function testGetPublicProjectNamesNotLoggedIn()
	{

		//	  print_r($this->loggedIn);
		$this->assertNotNull($this->loggedIn);

		$response = $this->soapclient->getPublicProjectNames();
		 
//		$this->assertContains("newsadmin", $response);
//		$this->assertContains("siteadmin", $response);

		//	  print_r($response);
	}

	/**
	 * @depends testLoginSuccesful
	 */
	function testGetPublicProjectNamesLoggedIn()
	{

		$this->login(EXISTING_USER, PASSWD_OF_EXISTING_USER);
		$this->assertNotNull($this->loggedIn);
		$this->assertNotNull($this->session);

		//	  print_r($this->loggedIn);
		$this->assertNotNull($this->loggedIn);

		$response = $this->soapclient->getPublicProjectNames($this->session);
//		$this->assertContains("newsadmin", $response);
//		$this->assertContains("siteadmin", $response);

		//print_r($response);
	}

	// Name: getUsers
	// Binding: GForgeAPIBinding
	// Style: rpc
	// Input:
	//   use: encoded
	//   message: getUsersRequest
	//   parts:
	//     session_ser: string
	//     user_ids: tns:ArrayOfint
	// Output:
	//   use: encoded
	//   message: getUsersResponse
	//   parts:
	//     userResponse: tns:ArrayOfUser


	// Name: getUsersByName
	// Binding: GForgeAPIBinding
	// Input:
	//   use: encoded
	//   message: getUsersByNameRequest
	//   parts:
	//     session_ser: string
	//     user_ids: tns:ArrayOfstring
	// Output:
	//   use: encoded
	//   message: getUsersByNameResponse
	//   parts:
	//     userResponse: tns:ArrayOfUser

	// Name: userGetGroups
	// Binding: GForgeAPIBinding
	// Style: rpc
	// Input:
	//   use: encoded
	//   message: userGetGroupsRequest
	//   parts:
	//     session_ser: string
	//     user_id: xsd:int
	// Output:
	//   use: encoded
	//   message: userGetGroupsResponse
	//   parts:
	//     groupResponse: tns:ArrayOfGroup


	/**
	 * @depends testLoginSuccesful
	 */
	function testGetUsersByNameEmpty()
	{

		//	  print_r($this->loggedIn);

		$this->login(EXISTING_USER, PASSWD_OF_EXISTING_USER);
		$this->assertNotNull($this->loggedIn);
		$this->assertNotNull($this->session);

		try {
			$response = $this->soapclient->getUsersByName($this->session);
		}
		catch (SoapFault $expected) {
			//	    print_r($expected->faultstring);

			// Use strpos instead of assertStringStartsWith (for PHPunit 3.3 compatibility)
			$this->assertTrue( strpos($expected->faultstring, 'Could Not Get Users By Name') === 0);
			//	  	$this->assertStringStartsWith('Could Not Get Users By Name', $expected->faultstring);

			return;
		}

		$this->fail('An expected exception has not been raised. Got response :'.$response);
	}

	/**
	 * @depends testLoginSuccesful
	 */
	function testGetUsersByName()
	{

		//	  print_r($this->loggedIn);

		$this->login(EXISTING_USER, PASSWD_OF_EXISTING_USER);
		$this->assertNotNull($this->loggedIn);
		$this->assertNotNull($this->session);

		$users = array('admin'=>'admin',
			 'None'=>'None', 
		EXISTING_USER => EXISTING_USER);

		foreach (array_keys($users) as $user_name) {
			//	    print_r($user_name);
			//	  $user_name='admin';
			$response = $this->soapclient->getUsersByName($this->session,array($user_name));
	  //	  $response = $this->soapclient->__soapCall('getUsersByName',
	  //						    array('session_ser' => $this->session,
	  //							  'user_ids' => array('admin')));

			$user = $response[0];
			// print_r($user);
			$this->assertEquals($user_name, $user->user_name);
		}

		$response = $this->soapclient->getUsersByName($this->session, array_keys($users));
		foreach ($response as $user) {
			// print_r($user);
			$this->assertEquals($users[$user->user_name], $user->user_name);

			$user_id = $user->user_id;

			// now verify that getUsers() returns the same
			$response = $this->soapclient->getUsers($this->session, array($user_id));
			$user2 = $response[0];
			// print_r($user2);
			$this->assertEquals($user_id, $user2->user_id);
			$this->assertEquals($user->user_name, $user2->user_name);

			//	    print_r($user->user_name);
			$response = $this->soapclient->userGetGroups($this->session, $user_id);

			if ($user->user_name == 'None') {
				$this->assertEquals(0,count($response));
			}

			if ($user->user_name == 'admin') {

				$adminGroups = array('template','stats','peerrating','siteadmin','newsadmin');

				foreach ($response as $group) {
					//		print_r($group->unix_group_name);

					$this->assertContains($group->unix_group_name, $adminGroups);
				}

			}
	   
		}
		 
	}
}
?>
