<?php 
/**
 * Copyright (c) Institut TELECOM, 2010. All Rights Reserved.
 *
 * Originally written by Sabri LABBENE, 201O
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('OSLCConnector.php');
require_once(dirname(__FILE__) . '/../models/Codendi.inc.php');

require_once ('pre.php');
require_once ('session.php');

require_once ('common/tracker/Tracker.class.php');
require_once ('common/tracker/TrackerFactory.class.php');
require_once ('common/tracker/Tracker_Artifact.class.php');
require_once ('common/tracker/Tracker_ArtifactFactory.class.php');
require_once ('common/tracker/Tracker_FormElementFactory.class.php');


class CodendiOSLCConnector extends OSLCConnector {

	
    /**
     * Filter parameters provided in the REST GET request to check whether mandatory ones are set.
     * 
     * @param array $params
     * @return array 
     */
    public function filterRequestParams($params) {
        // Process the args provided by Zend REST
        if (is_array($params)) {
            if(!isset($params['project'])) {
                throw new Exception('Missing project id !');
            } elseif(!isset($params['tracker'])) {
                throw new Exception('Missing tracker id resource for project '.$params['project'].' !');
            } else {
                return $params;
            }
        }
    }
    
    /**
     * Checks whether a change request exists inside Codendi trackers. 
     * @param int $id change request id.
     * @return bool
     */
    public function checkChangeRequestExists($changerequest_id) {
        $af = Tracker_ArtifactFactory::instance();
        $artifact = $af->getArtifactById($changerequest_id);
        if (!$artifact) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Constructs the model from Codendi DB by fetching the requested changeRequests.
     *
     * @param array $params contains 'project' + 'tracker'
     */
    public function fetchChangeRequests($params) {
        $artifacts = array();

        // what the DB will be queried on
        $group_id = null;
        $tracker_id = null;

        if (isset($params['project'])) {
            $group_id = $params['project'];
        }
        if (isset($params['tracker'])) {
            $tracker_id = $params['tracker'];
        }
        if (isset($params['bug'])) {
            $changerequest_id = $params['bug'];
            $af = Tracker_ArtifactFactory::instance();
            $changeRequest = $af->getArtifactById($changerequest_id);
            $this->changerequests = new ChangeRequestsFusionForgeDb($changeRequest);
        }

        $project = new Project($group_id);
        if (!$project->usesService('tracker')) {
            throw new Exception('Error : Tracker service is not used for this project.', 'fetchChangeRequests');
        }

        $tf = TrackerFactory::instance();
        if (!$tf) {
            throw new Exception('Error : Could Not Get TrackerFactory', 'fetchChangeRequests');
        } 
        
        $tracker = $tf->getTrackerById($tracker_id);
        
        if ($tracker == null) {
            throw new Exception('Error : Could Not Get Tracker', 'fetchChangeRequests');
        } else {
            $af = Tracker_ArtifactFactory::instance();
            $artifacts = $af->getArtifactsByTrackerId($tracker_id);
        }

        // instanciate the model from the returned artifacts
            if(isset($params['fields'])){
                $this->changerequests = new ChangeRequestsCodendiDb($artifacts, $params['fields']);
            } else {
                $this->changerequests = new ChangeRequestsCodendiDb($artifacts);
            }
    }

    public function fetchChangeRequest($changerequest_id, $uri, $requested_fields=array()) {
        $af = Tracker_ArtifactFactory::instance();
        $artifact = $af->getArtifactById($changerequest_id);

        if(!$artifact){
            throw new NotFoundException('Error : Change Request not found', 'fetchChangeRequest' );
        }
        $art = array($artifact);

        $changerequest = new ChangeRequestsCodendiDb($art, $requested_fields);

        return $this->prepareChangeRequest($changerequest[$changerequest_id], $uri);
    }

    /**
     * Updates an existant Codendi ChangeRequest in the tracker DataBase.
     * @param int $identifier id of the ChangeRequest within Codendi tracker
     * @param ChangeRequest the change request given as input for the PUT request.
     * @param array $props array of the properties that PUT request is going to change 
     * @return boolean true if success, false otherwise.
     */
    public function updateChangeRequest($changerequest_id, $group_id, $tracker_id, $changerequest, $props) {
        $user = UserManager::instance()->getCurrentUser();
        $pm = ProjectManager::instance();
        $project = $pm->getProject($group_id);
        if (! $project || ! is_object($project)) {
            throw new Exception('Error : Could Not Get Group','updateChangeRequest');
        } elseif ($project->isError()) {
            throw new Exception('Error : ' . $project->getErrorMessage(),'updateChangeRequest');
        }
        if ( ! checkRestrictedAccess($project)) {
            throw new Exception('Error, Restricted user: permission denied.', 'updateChangeRequest');
        }

        $tf = TrackerFactory::instance();
        $tracker = $tf->getTrackerById($tracker_id);

        if ($tracker == null) {
            throw new Exception('Error : Could not get Tracker.', 'updateChangeRequest');
        } elseif ($tracker->getGroupId() != $group_id) {
            throw new Exception('Error : Could not get Tracker.', 'updateChangeRequest');
        }

        $af = Tracker_ArtifactFactory::instance();
        if ($artifact = $af->getArtifactById($changerequest_id)) {
            if ($artifact->getTrackerId() != $tracker_id) {
                throw new Exception('Error : Could not get Artifact.', 'updateChangeRequest');
            }
        }

        // Get the artifact data using its ID. 
		$art_obj =& artifact_get_object($identifier);
		$art = $art_obj->data_array;

		$cm_request = $changerequest->container;

		$terms = array('dc:','helios_bt:');
		foreach($props as &$prop) {
			$prop = str_replace($terms,"",$prop);
			//echo $prop;
		}
		
		// Check all the mandatory fields for an artifact update request.
		
		// dc:title ===> summary 
		if(in_array('title',$props))
		{
			if(isset($cm_request['title']))
			{
				$art['summary'] = $cm_request['title'];			// mandatory
			}
			else
			{
				throw new BadRequestException("dc:title mentioned in the request query not found in request body!");
			}
		}
		
		// dc:description ===> details 
		if(in_array('description',$props))
		{
			if(isset($cm_request['description']))
			{
				$art['details'] = $cm_request['description'];			// mandatory
			}
			else
			{
				throw new BadRequestException("dc:decription mentioned in the request query not found in request body!");
			}
		}
		
		// helios_bt:priority ===> priority 
		if(in_array('priority',$props))
		{
			if(isset($cm_request['priority']))
			{
				$art['priority'] = $cm_request['priority'];			// mandatory
			}
			else
			{
				throw new BadRequestException("helios_bt:priority mentioned in the request query not found in request body!");
			}
		}
		
		// helios_bt:status ===> status 
		if(in_array('status',$props))
		{
			if(isset($cm_request['status']))
			{
				$art['status_id'] = self::$status_arr[$cm_request['status']];			// mandatory
			}
			else
			{
				throw new BadRequestException("helios_bt:status mentioned in the request query not found in request body!");
			}
		}
		
		//helios_bt:assigned_to ====> assigned_to
		if(in_array('assigned_to', $props))
		{
			if(isset($cm_request['assigned_to']))
			{
				$art['assigned_to'] = $cm_request['assigned_to'];
			}
			else 
			{
				throw new BadRequestException("helios_bt:assigned_to mentionned in the request query not found in request body!");
			}
		}
		
		
		$canned_response=100;
		
		// We assume that we don't change the artifact type (bug, task, etc)
		// in PUT request. 
		$new_artifact_type_id = $art_obj->ArtifactType->getID();
		
		//TODO: figure out if a follow up is in OSLC specs and if it is the case include it.
		$follow_up_msg = '';
		
		if(!$art_obj->update($art['priority'],$art['status_id'],$art['assigned_to'],$art['summary'],$canned_response,$follow_up_msg,$new_artifact_type_id,array(),$art['details']))
		{
			throw new Exception($art_obj->getErrorMessage());
		}
	}
	
/**
 * updateArtifact - update the artifact $artifact_id in tracker $tracker_id of the project $group_id with given values
 *
 * @param string $sessionKey  the session hash associated with the session opened by the person who calls the service
 * @param int    $group_id    the ID of the group we want to update the artifact
 * @param int    $tracker_id  the ID of the tracker we want to update the artifact
 * @param int    $artifact_id the ID of the artifact to update
 * @param array{SOAPArtifactFieldValue} $value the fields value to update
 * @param string $comment     the comment associated with the modification, or null if no follow-up comment.
 *
 * @return int The artifact id if update was fine,
 *              or a soap fault if :
 *              - group_id does not match with a valid project, 
 *              - tracker_id does not match with a valid tracker,
 *              - artifact_id does not match with a valid artifact,
 *              - the given values are breaking a field dependency rule
 *              - the artifact modification failed.
 */
function updateArtifact($sessionKey, $group_id, $tracker_id, $artifact_id, $value, $comment) {
    if (session_continue($sessionKey)) {
        $user = UserManager::instance()->getCurrentUser();
        $pm = ProjectManager::instance();
        $project = $pm->getProject($group_id);
        if (! $project || ! is_object($project)) {
            return new SoapFault(get_group_fault,'Could Not Get Group','updateArtifact');
        } elseif ($project->isError()) {
            return new SoapFault(get_group_fault, $project->getErrorMessage(),'updateArtifact');
        }
        if ( ! checkRestrictedAccess($project)) {
            return new SoapFault(get_group_fault, 'Restricted user: permission denied.', 'updateArtifact');
        }
        
        $tf = TrackerFactory::instance();
        $tracker = $tf->getTrackerById($tracker_id);
        if ($tracker == null) {
            return new SoapFault(get_tracker_fault, 'Could not get Tracker.', 'updateArtifact');
        } elseif ($tracker->getGroupId() != $group_id) {
            return new SoapFault(get_tracker_fault, 'Could not get Tracker.', 'updateArtifact');
        }
        
        $af = Tracker_ArtifactFactory::instance();
        if ($artifact = $af->getArtifactById($artifact_id)) {
            if ($artifact->getTrackerId() != $tracker_id) {
                return new SoapFault(get_tracker_fault, 'Could not get Artifact.', 'updateArtifact');
            }
            
            //Check Field Dependencies
            // TODO : implement it
            /*require_once('common/tracker/ArtifactRulesManager.class.php');
            $arm =& new ArtifactRulesManager();
            if (!$arm->validate($ath->getID(), $data, $art_field_fact)) {
                return new SoapFault(invalid_field_dependency_fault, 'Invalid Field Dependency', 'updateArtifact');
            }*/
            
            $fef = Tracker_FormElementFactory::instance();
            
            $fields_data = array();
            foreach ($value as $field_value) {
                // field are identified by name, we need to retrieve the field id
                if ($field_value->field_name) {
                    
                    $field = $fef->getUsedFieldByName($tracker_id, $field_value->field_name);
                    if ($field) {
                        
                        $field_data = $field->getFieldData($field_value->field_value);
                        if ($field_data != null) {
                            // $field_value is an object: SOAP must cast it in ArtifactFieldValue
                            if (isset($fields_data[$field->getId()])) {
                                if ( ! is_array($fields_data[$field->getId()]) ) {
                                    $fields_data[$field->getId()] = array($fields_data[$field->getId()]);
                                }
                                $fields_data[$field->getId()][] = $field_data;
                            } else {
                                $fields_data[$field->getId()] = $field_data;
                            }
                        } else {
                            return new SoapFault(update_artifact_fault, 'Unknown value ' . $field_value->field_value . ' for field: '.$field_value->field_name ,'addArtifact');
                        }
                    } else {
                        return new SoapFault(update_artifact_fault, 'Unknown field: '.$field_value->field_name ,'addArtifact');
                    }
                }
            }
            
            if ($artifact->createNewChangeset($fields_data, $comment, $user, null)) {
                return $artifact_id;
            } else {
                $response = new Response();
                if ($response->feedbackHasErrors()) {
                    return new SoapFault(update_artifact_fault, $response->getRawFeedback(),'updateArtifact');
                } else {
                    return new SoapFault(update_artifact_fault, 'Unknown error','updateArtifact');
                }
            }
        } else {
            return new SoapFault(get_tracker_fault, 'Could not get Artifact.', 'updateArtifact');
        }
        
    } else {
        return new SoapFault(invalid_session_fault,'Invalid Session ','updateArtifact');
    }
}
}
?>