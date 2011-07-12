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

class SoapLoginProcess extends PHPUnit_Framework_TestCase
{

	function setUp()
	{

	  //	  print_r("setup\n");

	  $this->session = NULL;
	  $this->soapclient = NULL;

	  //try {

	  // This is to check that the SoapClient instanciation will
	  // work. There's aparently a different behaviour if
	  // resolving the hostname doesn't work under phpunit. If
	  // this fails, the hostname and IP address should be
	  // different and the WSDL retrieval should work
	  $ip = gethostbyname(HOST);
	  if ($ip != HOST)
	    {

	      // Instantiate the SOAP client with WSDL
	      $this->soapclient = new SoapClient(WSDL_URL,
					     array('cache_wsdl' => WSDL_CACHE_NONE,
						   'trace' => true));

	  //	  } catch (SoapFault $fault) {
	  //	    $fault->faultstring;
	  //	    print_r($fault);
	  //	  }
	      //	      print_r($this->soapclient);
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
	  $this->assertNotNull($this->soapclient);

	  $version = $this->soapclient->version();

	  $this->assertEquals('4.8.50', $version);

	}

        /**
	 * @depends testVersion
	 */
	function testGETFUNCTIONS()
	{
	  $this->assertNotNull($this->soapclient);
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

        /**
	 * @depends testVersion
	 */
	function testLoginNonExistantUser()
	{
	  $this->assertNotNull($this->soapclient);

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

        /**
	 * @depends testVersion
	 */
	function testLoginWrongPwd()
	{
	  $this->assertNotNull($this->soapclient);

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

        /**
	 * @depends testVersion
	 */
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



	  $response = $this->soapclient->logout('coin');

	  print_r('logout response :'. $response);

	}
	*/
}
?>
