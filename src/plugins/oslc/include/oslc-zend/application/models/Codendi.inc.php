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

require_once('ChangeRequests.php');



        // will contain an array of fields read in the oslc:ChangeRequest
        $resource = null;

        // we use simplexml PHP library which supports namespaces

        /*******Sample CR*****************************************
         * 
         * <?xml version="1.0"?>
         * <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
         *   <oslc_cm:ChangeRequest xmlns:oslc_cm="http://open-services.net/xmlns/cm/1.0/">
         *       <dc:title xmlns:dc="http://purl.org/dc/terms/">Provide import</dc:title>
         *       <dc:identifier xmlns:dc="http://purl.org/dc/terms/">1234</dc:identifier>
         *       <dc:type xmlns:dc="http://purl.org/dc/terms/">http://myserver/mycmapp/types/Enhancement</dc:type>
         *       <dc:description xmlns:dc="http://purl.org/dc/terms/">Implement the system's import capabilities.</dc:description>
         *       <dc:subject xmlns:dc="http://purl.org/dc/terms/">import</dc:subject>
         *       <dc:creator xmlns:dc="http://purl.org/dc/terms/">mailto:aadams@someemail.com</dc:creator>
         *       <dc:modified xmlns:dc="http://purl.org/dc/terms/">2008-09-16T08:42:11.265Z</dc:modified>
         *   </oslc_cm:ChangeRequest>
         * </rdf:RDF>
         *           
         */

/*        $dc_attr = array("title", "identifier", "description","creator","modified","created");
        $fusionforgebt_attr = array("status","priority", "assigned_to");

        $xml = simplexml_load_string($xmlstr);

		$namespace = $xml->getNamespaces(true);

*/
// Represents a base of changerequests loaded from Codendi DB
class ChangeRequestsCodendiDb extends ChangeRequests {
    function __construct($art_arr, $fields='') {
        parent::__construct();
        $changerequestsdata = $this->convert_artifacts_array($art_arr, $fields);
        foreach ($changerequestsdata as $identifier => $data) {
            $this->_data[$identifier] = ChangeRequest::Create('Codendi');
            $this->_data[$identifier] = $data;
        }
    }
	
    /* duplicated from Codendi tracker SOAP API
     * 
     * TODO Add code that maps Codendi tracker fields to ontologies (dc, oslc, etc) 
     * 
     */
    protected static function convert_artifacts_array($at_arr, $fields_string) {
        $CodendiCR_attr = array('artifact_id','group_artifact_id','status_id','priority','submitted_by','assigned_to','open_date','close_date',
            'summary','details','assigned_unixname','assigned_realname','assigned_email','submitted_unixname','submitted_realname','submitted_email',
            'status_name','last_modified_date');

        $return = array();

        if (is_array($at_arr) && count($at_arr) > 0) {
            for ($i=0; $i <count($at_arr); $i++) {
                // Retrieving the artifact details
                //**checks whether there is any artifact details exists for this object, if not continue with next loop
                if(count($at_arr[$i]) < 1) { continue; }
                $identifier = $at_arr[$i]->data_array['artifact_id'];

                // If specific fields were requested using a query
                // we only return the requested fields data in the change request.
                if (strlen($fields_string) > 0) {
                    $fields = explode(",", $fields_string);
                }

                if(isset($fields) && is_array($fields) && count($fields) > 0){
                    foreach ($fields as $field) {
                        switch ($field) {
                            case 'dc:identifier': 
                                $return[$identifier]['identifier'] = $identifier;
                                break;
                            case 'dc:title': 
                                $return[$identifier]['title'] = $at_arr[$i]->data_array['summary'];
                                break;
                            case 'dc:description': 
                                $return[$identifier]['description'] = $at_arr[$i]->data_array['details'];
                                break;
                            case 'dc:creator': 
                                $return[$identifier]['creator'] = $at_arr[$i]->data_array['submitted_realname'];
                                break;
                            case 'helios_bt:status': 
                                $return[$identifier]['helios_bt:status'] = $at_arr[$i]->data_array['status_name'];
                                break;
                            case 'helios_bt:priority': 
                                $return[$identifier]['helios_bt:priority'] = $at_arr[$i]->data_array['priority'];
                                break;
                            case 'helios_bt:assigned_to': 
                                $return[$identifier]['helios_bt:assigned_to'] = $at_arr[$i]->data_array['assigned_realname'];
                                break;
                            case 'dc:modified': 
                                $return[$identifier]['modified'] = $at_arr[$i]->data_array['last_modified_date'];
                                break;
                            case 'dc:created': 
                                $return[$identifier]['created'] = $at_arr[$i]->data_array['open_date'];
                                break;
                            default: 
                                throw new ConflictException("The attribute specified ".$field." cannot be found!");
                        }
                    }
                } else {
                    //return the by default set of Change request fields.
                    $return[$identifier]=array(
                        'identifier'=>$identifier,
                        'title'=>$at_arr[$i]->data_array['summary'],
                        'description'=>$at_arr[$i]->data_array['details'],
                        'helios_bt:status'=>$at_arr[$i]->data_array['status_name'],
                        'helios_bt:priority'=>$at_arr[$i]->data_array['priority'],
                        'creator' => $at_arr[$i]->data_array['submitted_realname'],
                        'helios_bt:assigned_to' => $at_arr[$i]->data_array['assigned_realname'],
                        'modified' => $at_arr[$i]->data_array['last_modified_date'],
                        'created' => $at_arr[$i]->data_array['open_date']
                    );
                }
            }
        }
        return $return;
    }
}

?>
