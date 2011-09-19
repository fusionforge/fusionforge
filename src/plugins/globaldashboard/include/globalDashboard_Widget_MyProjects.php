<?php
/**
* Copyright 2011, Sabri LABBENE - Institut Télécom
*
* This file is part of FusionForge. FusionForge is free software;
* you can redistribute it and/or modify it under the terms of the
* GNU General Public License as published by the Free Software
* Foundation; either version 2 of the Licence, or (at your option)
* any later version.
*
* FusionForge is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with FusionForge; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

require_once 'globaldashboard/common/manage_accounts_db_utils.php';
require_once 'globaldashboard/include/globalDashboardConstants.php';
require_once 'common/widget/Widget.class.php';
require_once 'common/widget/WidgetLayoutManager.class.php';


class globalDashboard_Widget_MyProjects extends Widget {

	function __construct($owner_type, $plugin) {
		$this->plugin = $plugin;
		$this->Widget('plugin_globalDashboard_MyProjects');
	}

	function getTitle() {
		return _("Projects on remote Software Forges");
	}

	function getCategory() {
		return _('Global Dashboard Plugin');
	}

	function getDescription() {
		return _("Lists user projects hosted on remote forge systems");
	}

	function getContent() {
		global $HTML;
		$user = session_get_user();
		$MyProjects = $this->getUserRemoteProjects($user->getID());

		$html='';
		if(is_array($MyProjects)) {
			$tablearr = array(_('My remote projects'),'');
			$html .= $HTML->listTableTop($tablearr);

			foreach ($MyProjects as $remote_account_projs) {
				/*include_once("arc/ARC2.php");
				require_once('plugins/extsubproj/include/Graphite.php');

				$parser = ARC2::getRDFParser();
				//$parser->parse('https://vm2.localdomain/projects/coinsuper/');
				$parser->parse($url);
				//print_r($parser);
				$triples = $parser->getTriples();
				//print_r($triples);
				$turtle = $parser->toTurtle($triples);
				$datauri = $parser->toDataURI($turtle);


				$graph = new Graphite();
				//$graph->setDebug(1);
				$graph->ns( "doap", "http://usefulinc.com/ns/doap#" );
				$graph->load( $datauri );
				//print $graph->resource('https://vm2.localdomain/projects/coinsuper/')->dumpText();
				$projname = $graph->resource( $url )->get( "doap:name" );
			*/
/* 				$html = $html . '
				<tr>
				<td>
					<!--<a href="http://'.$proj->getHomePage().'">'.$proj->getUnixName().'</a>
				</td>
				</tr>'; */
				foreach ($remote_account_projs as $remote_proj) {
					$html = $html . '
					<tr>
						<td>
							'.print_r($remote_proj).'
						</td>
					</tr>';
				}
				//print_r($proj);
				
			}
			$html .= $HTML->listTableBottom();
		}
		return $html;
	}

	/**
	 * 
	 * Fetches user remote projects from all forges
	 * 
	 * @param integer $user_id
	 * 
	 * @return array $projects
	 */
	function getUserRemoteProjects($user_id){
		$projects = array();
		$accounts = getDBStoredRemoteAccountsByUserId($user_id);
		if (count($accounts) > 0) {
			foreach ($accounts as $account) {
				$fetch_method = $this->getProjectsFetchMethodForAccount($account['account_id']);
				switch ($fetch_method) {
					case USER_PROJECTS_FETCH_METHOD_SOAP:
						$soap_client = new SoapClient($account['forge_account_soap_wsdl_uri'], array('trace'=>false, 'exceptions'=>false));
						$session_ser = $soap_client->__soapCall("login", array("userid" => $account['forge_account_login_name'], "passwd" => $account['forge_account_password']));
						$result = $soap_client->__soapCall("userGetGroups", array("session_ser" => $session_ser, "user_id" => $user_id));
						if (!is_a($result, "SoapFault")) {
							$projects[] = soapToArray($result);
						}
						break;
					case USER_PROJECTS_FETCH_METHOD_OSLC:
						break;
					default:
						break;
				}
			}
		}
		return $projects;
	}
	
	/**
	 * 
	 * Converts an stdClass object to an array.
	 * @param stdClass $result
	 * 
	 * @return array $array
	 */
	function soapToArray(stdClass $result) {
		$result = (array)$result;
		foreach($result as $key => $value){
			if(is_object($value)&&get_class($value)==='stdClass'){
				$result[$key] = self::soapToArray($value);
			}
		}
		return $result;
	}
	
	/**
	 * 
	 * Returns fetch method of remote projects related to an account
	 * 
	 * @param integer $account_id
	 * 
	 * @return integer $method value corresponding to fetch method
 	 */
	function getProjectsFetchMethodForAccount($account_id) {
		return getDBFetchMethod($account_id, 'projects');
	} 
	
	/**
	*
	* Returns fetch method of remote artifacts related to an account
	*
	* @param integer $account_id
	*
	* @return integer $method value corresponding to fetch method
	*/
	function getProjectsFetchMethodForAccount($account_id) {
		return getDBFetchMethod($account_id, 'artifacts');
	}
}

?>