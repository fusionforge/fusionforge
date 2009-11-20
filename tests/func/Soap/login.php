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

class SoapLoginProcess extends PHPUnit_Framework_TestCase
{

	function setUp()
	{

	  //	  print_r("setup\n");

	  $this->loggedIn = FALSE;
	  $this->session = NULL;
	  //print_r("session :".$this->session);

	  $this->assertRegExp('/^http.?:\/\//', WSDL_URL);

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

// Name: version
// Binding: GForgeAPIBinding
// Input:
//   use: encoded
//   message: versionRequest
//   parts:
// Output:
//   use: encoded
//   message: versionResponse
//   parts:
//     versionResponse: xsd:string

	function testVersion()
	{
	  $version = $this->soapclient->version();
	  
	  $this->assertEquals('4.8.1', $version);

	}

	function testGETFUNCTIONS()
	{
	  $response = $this->soapclient->__getFunctions();
	  //	  print_r($response);
	}

// Name: login
// Binding: GForgeAPIBinding
// Input:
//   use: encoded
//   message: loginRequest
//   parts:
//     userid: xsd:string
//     passwd: xsd:string
// Output:
//   use: encoded
//   message: loginResponse
//   parts:
//     loginResponse: xsd:string

	function testLoginNonExistantUser()
	{
	  $userid = 'coin';
	  
	  try {
	    
	    $response = $this->soapclient->login($userid, 'pan');
	    
	  }
	  catch (SoapFault $expected) {

	    $this->assertEquals("Unable to log in with userid of ".$userid, $expected->faultstring);

		//	    print_r($response);
            
	    return;
	  }
 
	  $this->fail('An expected exception has not been raised.');
	}

	function testLoginWrongPwd()
	{
	  $userid = EXISTING_USER;
	  
	  try {
	    
	    $response = $this->soapclient->login($userid, 'xxxxxx');
	    
	  }
	  catch (SoapFault $expected) {

	    $this->assertEquals("Unable to log in with userid of ".$userid, $expected->faultstring);

	    //	    print_r($response);
            
	    return;
	  }
 
	  $this->fail('An expected exception has not been raised.');
	}

	function testLoginSuccesful()
	{
	  $userid = EXISTING_USER;
	  $passwd = PASSWD_OF_EXISTING_USER;

	  $response = $this->login($userid, $passwd);

	  $this->assertNotNull($response);

	}


// Name: logout
// Binding: GForgeAPIBinding
// Input:
//   use: encoded
//   message: logoutRequest
//   parts:
//     session_ser: xsd:string
// Output:
//   use: encoded
//   message: logoutResponse
//   parts:
//     logoutResponse: xsd:string

        /**
	 * @depends testLoginSuccesful
	 */
	/*	function testLogout()
	{

	  print_r($this->loggedIn);

	  $this->assertNotNull($this->loggedIn);

	  $response = $this->soapclient->logout('coin');

	  print_r('logout response :'. $response);

	}
	*/
}
?>
