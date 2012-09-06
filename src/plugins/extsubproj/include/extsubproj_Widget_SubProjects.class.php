<?php
/**
 * Copyright 2011, Olivier Berger - Institut Telecom
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
require_once 'common/widget/Widget.class.php';
require_once 'common/widget/WidgetLayoutManager.class.php';


class extsubproj_Widget_SubProjects extends Widget {
	function extsubproj_Widget_SubProjects($owner_type, $plugin) {
		$this->plugin = $plugin;
		$this->Widget('plugin_extsubproj_project_subprojects');
	}

	function getTitle() {
		return _("Project external subprojects");
	}

	function getCategory() {
		return _('Plugin (project)');
	}

	function getDescription() {
		return _("Displays links to external subprojects of the project");
	}
	
	function getContent() {
		global $pluginExtSubProj;
		global $group_id;
		global $HTML;
		
		$subProjects = $this->plugin->getSubProjects($group_id);
		
		$html='';
		if(is_array($subProjects)) {
			$tablearr = array(_('Sub projects'),'');
			$html .= $HTML->listTableTop($tablearr);
		
			foreach ($subProjects as $url) {
				
				include_once 'arc/ARC2.php';
				require_once 'plugins/extsubproj/include/Graphite.php';
				
				$reader = ARC2::getComponent('Reader');
				
				$parser = ARC2::getRDFParser();

				$reader->setAcceptHeader('Accept: application/rdf+xml');
				$parser->setReader($reader);
				
				//$parser->parse('https://vm2.localdomain/projects/coinsuper/');
				$parser->parse($url);
				
				if(! $parser->reader->errors) {
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
				}
				else {
					/*
					foreach ($parser->reader->errors as $error) {
						$html .= $error;
					}
					*/
					$projname = $url;
				}
				
				//@TODO: check plugin compactpreview is installed through the right functions...
				require_once 'plugins/compactpreview/include/CompactResource.class.php';
				$params = array('name' => $projname,
								'url' => $url);
				
				$cR = new OslcGroupCompactResource($params);
				
 				$html = $html . '
 				<tr>
 					<td>';
//<a href="'.$url.'">'.$projname.'</a>
				$html .= $cR->getResourceLink();
				$html = $html . '</td>
 				</tr>';
			}
		
			$html .= $HTML->listTableBottom();
		}
		
		return $html;
	}
}
