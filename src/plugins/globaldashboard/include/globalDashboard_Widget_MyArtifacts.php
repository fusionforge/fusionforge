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

require_once 'common/widget/Widget.class.php';
require_once 'common/widget/WidgetLayoutManager.class.php';

class globalDashboard_Widget_MyArtifacts extends Widget {

	function __construct($owner_type, $plugin) {
		$this->plugin = $plugin;
		parent::__construct('plugin_globalDashboard_MyArtifacts');
	}

	function getTitle() {
		return _('User artifacts from other remote Forges');
	}

	function getCategory() {
		return _('Global Dashboard Plugin');
	}

	function getDescription() {
		return _('Displays user artifacts that lives in remote Tracking systems');
	}

	function getContent() {
		global $HTML;

		$MyProjects = $this->plugin->getMyProjects();

		$html='';
		if(is_array($MyProjects)) {
			$tablearr = array(_('My Projects'),'');
			$html .= $HTML->listTableTop($tablearr);

			foreach ($MyProjects as $url) {
				include_once 'arc/ARC2.php';
				require_once 'plugins/extsubproj/include/Graphite.php';

				$parser = ARC2::getRDFParser();
				//$parser->parse('https://vm2.localdomain/projects/coinsuper/');
				$parser->parse($url);
				//print_r($parser);
				$triples = $parser->getTriples();
				//print_r($triples);
				$turtle = $parser->toTurtle($triples);
				$datauri = $parser->toDataURI($turtle);

				/*
				 $graph = new Graphite();
				//$graph->setDebug(1);
				$graph->ns( "doap", "http://usefulinc.com/ns/doap#" );
				$graph->load( $datauri );
				//print $graph->resource('https://vm2.localdomain/projects/coinsuper/')->dumpText();
				$projname = $graph->resource( $url )->get( "doap:name" );

				$html = $html . '
				<tr>
				<td><a href="'.$url.'">'.$projname.'</a>
				</td>
				</tr>'; */
			}
			$html .= $HTML->listTableBottom();
		}
		return $html;
	}
}
