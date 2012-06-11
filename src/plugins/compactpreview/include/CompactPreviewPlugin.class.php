<?php

/**
 * CompactPreviewPlugin Class
 *
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class CompactPreviewPlugin extends Plugin {
	
	public function __construct($id=0) {
		$this->Plugin($id) ;
		$this->name = "compactpreview";
		$this->text = "CompactPreview!"; // To show in the tabs, use...
		$this->_addHook("user_link_with_tooltip"); // override the way user links are done (for user compact preview support)
		$this->_addHook("project_link_with_tooltip");
		$this->_addHook("javascript_file"); // Add js files for oslc plugin
		$this->_addHook("javascript"); // Add js initialization code
		$this->_addHook("cssfile");
		$this->_addHook("script_accepted_types");
		$this->_addHook("content_negociated_user_home");
		$this->_addHook("content_negociated_project_home");
		
	}
	
	// hook methods
	
	/**
	 * override util_display_user() with modified version to display compact preview popup on user links
	 * @param array $params hook params (return in $params['user_link'])
	 */
	function user_link_with_tooltip (&$params) {
		// 
		require_once dirname( __FILE__ ) . '/CompactResource.class.php';
		$cR = CompactResource::createCompactResource($params);
		$params['user_link'] = $cR->getResourceLink();
	}
	
	/**
	 * override util_make_link_g() with modified version to display compact preview popup on project links
	 * @param array $params hook params (return in $params['user_link'])
	 */
	function project_link_with_tooltip (&$params) {
		require_once dirname( __FILE__ ) . '/CompactResource.class.php';
		$cR = CompactResource::createCompactResource($params);
		$params['group_link'] = $cR->getResourceLink();
	}
	
	function javascript_file (&$params) {
		// The userTooltip.js script is used by the compact preview feature (see content_negociated_user_home)
		html_use_jquery();
		// provides support for the popup for compact preview
		use_javascript('/plugins/'.$this->name.'/scripts/oslcTooltip.js');
	}

	function javascript (&$params) {
		// make sure jquery won't conflict with prototype
		$params['return'] = 'jQuery.noConflict();';
	}

	function cssfile (&$params) {
		use_stylesheet('/plugins/'.$this->name.'/css/oslcTooltipStyle.css');
	}
	
	/**
	 * Declaration of which content-negociation alternatives are provided by this plugin
	 * @param unknown_type $params
	 */
	function script_accepted_types (&$params) {
		$script = $params['script'];
		if ($script == 'user_home' || $script == 'project_home') {
			// we do support content-negociation on /users and /project with the following accept header values
			// for OSLC compact-preview
			$params['accepted_types'][] = 'application/x-oslc-compact+xml';
			// for fusionforge compact preview
			$params['accepted_types'][] = 'application/x-fusionforge-compact+html';
		}
	}
	
	function display_user_html_compact_preview($username, $title = false) {
		global $gfwww;
		
		require_once $gfwww.'include/user_profile.php';
		
		$user_obj = user_get_object_by_name($username);

		$user_real_name = $user_obj->getRealName();
		$user_id = $user_obj->getID();
		
		$html = '<html>
						<head>
						<title>User: '. $user_real_name .' (Identifier: '. $user_id .')</title>
						</head>
						<body>';

		$html .= user_personal_information($user_obj, true, $title);
		$html .= '</body>
						</html>';
		
		return $html;
				
	}
	
	function display_project_html_compact_preview($project, $title) {
		
		$project_obj = group_get_object_by_name($project);
		
		$public_name = $project_obj->getPublicName();
		$unix_name = $project_obj->getUnixName();
		$id = $project_obj->getID();
		$home_page = util_make_url ("/projects/$unix_name/");
		$description = $project_obj->getDescription();
		$start_date = $project_obj->getStartDate();
		$status = $project_obj->getStatus();
		switch ($status){
			case 'A':
				$project_status = 'Active';
				break;
			case 'H':
				$project_status = 'Hold';
				break;
			case 'P':
				$project_status = 'Pending';
				break;
			case 'I':
				$project_status = 'Incomplete';
				break;
			default:
				break;
		}
		if ($project_obj->isPublic()) {
			$public = 'Yes';
		} else {
			$public = 'No';
		}
		
		$html='<html>
		<head>
		<title>Project: '. $public_name .' ('. $unix_name .')</title>
		</head>
		<body>
			<table>
		
				<tr>
					<td colspan="2"><i>'. $title .'</i></td>
				</tr>
				<tr>
					'/* <td rowspan="8"><img src="/plugins/compactpreview/images/userTooltip/oslc.png" />
					</td>*/.'
					<td><b>Project name:</b>
					
					
					
					 '. $public_name .'</td>
				</tr>
				<tr>
					<td><b>Project short name:</b>
					
					
					
					 '. $unix_name .'</td>
				</tr>
				<tr>
					<td><b>Identifier:</b>
					
					
					
					  '. $id .'</td>
				</tr>
				<tr>
					<td><b>Started since:</b>
					
					
					
					 '. date(_('Y-m-d H:i'), $start_date) .'</td>
				</tr>
				<tr>
					<td><b>Status:</b>
					
					
					
					  '. $project_status .'</td>
				</tr>
				<tr>
					<td><b>Is Public:</b>
					
					
					
					  '. $public .'</td>
				</tr>
				<tr>
					<td><b>Description:</b>
					
					
					
					  '. $description .'</td>
				</tr>
				<tr>
					<td><small><b>Home Page:</b> <a href="'. $home_page .'">'. $home_page .'
						</a> </small></td>
				</tr>
		
			</table>
		</body>
		</html>';
	
		return $html;
				
	}
	
	function content_negociated_user_home (&$params) {

		$username = $params['username'];
		$accept = $params['accept'];
		$param['content'] = false;
		
		switch ($accept) {
			// if want some OSLC compact-preview, provide the document pointing to the compact-preview
			case 'application/x-oslc-compact+xml' : {
				$params['content_type'] = 'application/x-oslc-compact+xml';
				$params['content'] = '<?xml version="1.0"?>
					<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:oslc="http://open-services.net/ns/core#">
					  <oslc:Compact rdf:about="/plugins/oslc/compact/user/'. $username .'">
					    <dcterms:title>'. $username . '</dcterms:title>
					    <oslc:shortTitle>'. $username . '</oslc:shortTitle>
					    <oslc:smallPreview>
					      <oslc:Preview>
					        <oslc:document rdf:ressource="/plugins/'.$this->name.'/user.php?user='. $username .'"/>
					        <oslc:hintWidth>500px</oslc:hintWidth>
					        <oslc:hintHeight>150px</oslc:hintHeight>
					      </oslc:Preview>
					    </oslc:smallPreview>
					  </oslc:Compact>
					</rdf:RDF>';
				break;
			}
			// if want direct compact-preview rendering
			case 'application/x-fusionforge-compact+html' : {
				$params['content_type'] = 'text/html';
				$title = _('Compact preview of local user');
				$params['content'] = $this->display_user_html_compact_preview($username, $title);
				break;
			}
		}
	}
	
	function content_negociated_project_home (&$params) {

		$projectname = $params['groupname'];
		$accept = $params['accept'];
		switch ($accept) {
			// if want some OSLC compact-preview, provide the document pointing to the compact-preview
			case 'application/x-oslc-compact+xml' : {
				$params['content_type'] = 'application/x-oslc-compact+xml';
				$params['content'] = '<?xml version="1.0"?>
					<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:oslc="http://open-services.net/ns/core#">
  						<oslc:Compact rdf:about="/plugins/oslc/compact/project/'. $projectname .'">
    						<dcterms:title>'. $projectname . '</dcterms:title>
    						<oslc:shortTitle>'. $projectname . '</oslc:shortTitle>
    						<oslc:smallPreview>
      							<oslc:Preview>
        							<oslc:document rdf:ressource="/plugins/'.$this->name.'/project.php?project='. $projectname .'"/>
        							<oslc:hintWidth>500px</oslc:hintWidth>
        							<oslc:hintHeight>150px</oslc:hintHeight>
      							</oslc:Preview>
    						</oslc:smallPreview>
  						</oslc:Compact>
					</rdf:RDF>';
				break;
			}
			// if want direct compact-preview rendering
			case 'application/x-fusionforge-compact+html' : {
				$params['content_type'] = 'text/html';
				$title = _('Compact preview of local project');
				$params['content'] = $this->display_project_html_compact_preview($projectname, $title);
				break;
			}
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
