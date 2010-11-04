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

// TODO: support the XML format for error messages (see http://open-services.net/bin/view/Main/CmRestApiV1)

class ErrorController extends Zend_Controller_Action
{

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext($this->getRequest()->getActionName(), 'xml')->initContext();
        $contextSwitch->addActionContext($this->getRequest()->getActionName(), 'json')->initContext();
        
        switch ($errors->type) { 
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                break;
            default:
                // application error 
                //$this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                //print_r(get_class($errors->exception));
                switch(get_class($errors->exception))
                {
                	case 'ForbiddenException':
                	case 'NotAcceptableForCRCollectionException':
                	case 'BadRequestException':
                	case 'NotFoundException':
                	case 'ConflictException':
                	case 'UnsupportedMediaTypeException':
                	case 'NotAcceptableForSingleCRException':
                	case 'GoneException':
                	case 'PreconditionFailedException':
                		//$this->_forward('res-not-found');
                		$this->getResponse()->setHttpResponseCode($errors->exception->getCode());
                		break;
                		
                	default:
                		$this->getResponse()->setHttpResponseCode(500);
                		break;
                }
              
                break;
        }
        
        if($errors->exception->getCode()==0)
        {
        	$return_code = 500;
        }
        else
        {
        	$return_code = $errors->exception->getCode();
        }
        
        $this->view->code = $return_code;
        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;
        
        $this->getResponse()->setHeader('Content-Type', 'text/html');
    }

    public function resnotfoundAction()
    {
    	$this->getResponse()->setHttpResponseCode(404);
	    $this->_forward('Default');
    }

    public function unknownaccepttypeAction()
    {
    	$this->view->error_message = 'Unknown "Accept:" content-type';
      $this->getResponse()->setHttpResponseCode(415);
      $this->_forward('Default');
    }

    public function defaultAction()
    {
	    $this->getResponse()->setHeader('Content-Type', 'text/html');
    }

}

