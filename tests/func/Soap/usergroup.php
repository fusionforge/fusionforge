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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once 'PHPUnit/Framework/TestCase.php';

class SoapUserGroupProcess extends PHPUnit_Framework_TestCase
{
  
	function setUp()
	{
	  //	  print_r("setup\n");
	  $this->session = NULL;
	  $this->soapclient = NULL;
	
	  //	  try {

	  // see comments in SoapLoginProcess:setup() for details about this
	  $ip = gethostbyname(HOST);
	  if ($ip != HOST) 
	    {

		$this->soapclient = new SoapClient(WSDL_URL,
						   array('cache_wsdl' => WSDL_CACHE_NONE,
							 'trace' => true));
		 
		//	  } catch (SoapFault $fault) {
		//	    $fault->faultstring;
		//	    print_r($fault);
		//	  }
		//	  print_r($this->soapclient);
	    }
	}

	function tearDown()
	{
	   if ($this->session) {
	     $response = $this->soapclient->logout($this->session);
	     //	     print($response);
	   }
	 }



	// performs a login and returns a session "cookie"
	function login($userid, $passwd)
	{
	        $this->assertNotNull($this->soapclient);
		$response = $this->soapclient->login($userid, $passwd);

		if ($response) {

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

		$this->login(EXISTING_USER, PASSWD_OF_EXISTING_USER);

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
		$this->login(EXISTING_USER, PASSWD_OF_EXISTING_USER);

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


	// /**
	//  * @depends testLoginSuccesful
	//  */
	// function testGetPublicProjectNamesNotLoggedIn()
	// {

	// 	//	  print_r($this->loggedIn);
	// 	$this->assertNotNull($this->loggedIn);

	// 	$response = $this->soapclient->getPublicProjectNames();

	// 		  print_r($response);
	// 	$this->assertContains("newsadmin", $response);
	// 	$this->assertContains("siteadmin", $response);

	// }

	/**
	 * @depends testLoginSuccesful
	 */
	// function testGetPublicProjectNamesLoggedIn()
	// {

	// 	$this->login(EXISTING_USER, PASSWD_OF_EXISTING_USER);
	// 	$this->assertNotNull($this->loggedIn);
	// 	$this->assertNotNull($this->session);

	// 	//	  print_r($this->loggedIn);
	// 	$this->assertNotNull($this->loggedIn);

	// 	$response = $this->soapclient->getPublicProjectNames($this->session);
	// 	$this->assertContains("newsadmin", $response);
	// 	$this->assertContains("siteadmin", $response);

	// 	//print_r($response);
	// }

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
		$this->login(EXISTING_USER, PASSWD_OF_EXISTING_USER);

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
	function testGetUsersByNameBug63()
	{
	  $this->login(EXISTING_USER, PASSWD_OF_EXISTING_USER);

	  $this->assertNotNull($this->session);

	  // corner case, but a dangerous ? one : the way the SOAP
	  // server works allow to trick it in returning several
	  // values at a time : this one may be fixed some day and we'd then
	  $users = array('admin", "None' => array( 'count' => 2, 
						   'user_names' => array('admin', 'None')));

	  foreach (array_keys($users) as $user_name) {
	    $response = $this->soapclient->getUsersByName($this->session,array($user_name));

	    $this->assertEquals($users[$user_name]['count'], count($response));

	    foreach ($response as $user) {
	      //	      print_r($user);
	      $this->assertContains($user->user_name, $users[$user_name]['user_names']);
	    }
	  }
	}

	/**
	 * @depends testLoginSuccesful
	 */
	function testGetUsersByName()
	{
		$this->login(EXISTING_USER, PASSWD_OF_EXISTING_USER);

		$this->assertNotNull($this->session);

		$users = array('admin'=>'admin',
			       'None'=>'None', 
			       EXISTING_USER => EXISTING_USER);

		foreach (array_keys($users) as $user_name) {
			//	    print_r($user_name);
			$response = $this->soapclient->getUsersByName($this->session,array($user_name));

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
