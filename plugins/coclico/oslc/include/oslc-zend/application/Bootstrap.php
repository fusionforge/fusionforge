<?php

/**
 * This file is (c) Copyright 2009 by Madhumita DHAR, Institut
 * TELECOM
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * This program has been developed in the frame of the HELIOS
 * project with financial support of its funders.
 *
 */

  /* $Id$ */

// This is a bit of black zend magic... don't ask : 
// we don't know and borrowed it from somewhere ;-)

// The next stop will be in controllers/CmController.php

// TODO : document what was necessary here vs regular zend_rest app

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	/* Not necessary it seems
	protected function _initAutoload(){
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Default_',
            'basePath'  => dirname(__FILE__),
        ));
	
        return $autoloader;
	} 
	*/
	
	protected function _initRestRoute() {
	  // This may be necessary if needing to override the _initRequest()
	  //	  	$this->bootstrap('Request');
	  	$this->bootstrap('FrontController');
		
	  	$front = Zend_Controller_Front::getInstance();
		
		/* This seems only needed if REST only for specific modules 
		$cmRoute = new Zend_Rest_Route($front, array(), array('default' => array('cm')));
		$front->getRouter()->addRoute('rest', $cmRoute);
		*/
		$restRoute = new Zend_Rest_Route($front);
		$front->getRouter()->addRoute('default', $restRoute);
	} 
	
	/* Seems not necessary to override it from the defaults at the moment
	protected function _initRequest(){
        $this->bootstrap('FrontController');
        $front = $this->getResource('FrontController');
	$request = $front->getRequest();
    	if (null === $front->getRequest()) {
            $request = new Zend_Controller_Request_Http();
            $front->setRequest($request);
        }
    	return $request;        
	}*/


}

?>
