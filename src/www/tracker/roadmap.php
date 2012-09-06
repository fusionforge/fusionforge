<?php
/**
 * FusionForge trackers
 *
 * Copyright 2011, Alcatel-Lucent
 * Copyright 2012, Franck Villaume - TrivialDev
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

/**
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The Roadmap ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'tracker/include/ArtifactTypeFactoryHtml.class.php';
require_once $gfcommon.'tracker/ArtifactFactory.class.php';
require_once $gfcommon.'tracker/Roadmap.class.php';
require_once $gfcommon.'tracker/RoadmapFactory.class.php';

// Templates ------------
$templates[0]['display_graph'] = 1;
$templates[0]['begin_tracker'] = '<table>'."\n";
$templates[0]['end_tracker'] = '</table>'."\n";
$templates[0]['separator'] = '<br/>'."\n";
$templates[0]['begin_release'] = '<fieldset><legend>%s<a href="%s">%s</a>%s</legend>'."\n";
$templates[0]['end_release'] = '</fieldset>'."\n";
$templates[0]['begin_ticket'] = '<tr>'."\n";
$templates[0]['ticket_icon'] = '<td class="align-center">%s</td>';
$templates[0]['ticket_id'] = '<td style="white-space: nowrap;"><a href="%s">[#%s]</a></td>';
$templates[0]['ticket_summary'] = '<td><span title="%s">%s</span></td>'."\n";
$templates[0]['end_ticket'] = '</tr>'."\n";

$templates[1]['display_graph'] = 0;
$templates[1]['begin_tracker'] = '<ul>'."\n";
$templates[1]['end_tracker'] = '</ul>'."\n";
$templates[1]['separator'] = '<br/>'."\n";
$templates[1]['begin_release'] = '<h2>%s<a href="%s">%s</a></h2>'."\n";
$templates[1]['end_release'] = "\n";
$templates[1]['begin_ticket'] = '<li>'."\n";
$templates[1]['ticket_icon'] = '';
$templates[1]['ticket_id'] = '<a href="%s">[#%s]</a> ';
$templates[1]['ticket_summary'] = '%2$s'."\n";
$templates[1]['end_ticket'] = '</li>'."\n";

//----------------------

function local_exit($msg='') {
	global $atfh;
	global $ajax;
	global $error_msg;
	
	if ($ajax) {
		echo $error_msg."\n";
		echo $msg;
	}
	else {
		$atfh->header(array('title' => _('Roadmap'), 'modal' => 1));
		echo $msg;
		$atfh->footer();
	}
	exit;
}

function nrange($col1, $col2, $nb) {
	if ($col1 == $col2) {
		$range = array_fill(0, $nb, $col1);
	}
	else {
		$nb = $nb - 1;
		
		if ($col2 < $col1) {
			$range[0] = $col1;
			$range[1] = $col2;
		}
		else {
			$range[0] = $col2;
			$range[1] = $col1;
		}

		for ($i = 2; $i <= $nb; $i++) {
			$range[$i] = $range[1] + round(($range[0] - $range[1]) * sqrt(($i - 1) / $nb));
		}
		$range[] = array_shift($range);
		
		if ($col2 < $col1) {
			$range = array_reverse($range);
		}
	}

	return $range;
}

function color_gradient($colors, $nb_colors) {
	if (! is_array($colors)) {
		return array();
	}
	elseif (count($colors) < 2 || $nb_colors <= 2) {
		return $colors;
	}
	
	$colors[0] = preg_replace('/^#/', '', $colors[0]);
	$colors[1] = preg_replace('/^#/', '', $colors[1]);
	
	$colors_start = array_map('hexdec', str_split($colors[0], 2));
	$colors_end = array_map('hexdec', str_split($colors[1], 2));
	$format_func = create_function('$val', 'return sprintf("%02s", $val);');
	
	$colors_comp = array();
	for($i = 0; $i < 3; $i++) {
		$colors_comp[] = array_map($format_func,
				array_map('dechex', nrange($colors_start[$i], $colors_end[$i], $nb_colors)));
	}
	for($i = 0; $i < $nb_colors; $i++) {
		$grad[] = $colors_comp[0][$i].$colors_comp[1][$i].$colors_comp[2][$i];
	}
	
	return $grad;
}

html_use_jquery();

if (!forge_get_config('use_tracker')) {
	exit_disabled('home');
}

$group_id = getIntFromRequest('group_id');

$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_no_group();
}
if ($group->isError()) {
	if($group->isPermissionDeniedError()) {
		exit_permission_denied($group->getErrorMessage(),'tracker');
	} else {
		exit_error($group->getErrorMessage(), 'tracker');
	}
}

$atfh = new ArtifactTypeFactoryHtml($group);
if (!$atfh || !is_object($atfh) || $atfh->isError()) {
	exit_error(_('Error: could Not Get ArtifactTypeFactoryHtml'), 'tracker');
}

$template = getIntFromRequest('template', 0);
$ajax = getIntFromRequest('ajax', 0);

$roadmap_name = getStringFromRequest('roadmap_name');
$roadmap_id = getIntFromRequest('roadmap_id', 0);
$selected_release = getStringFromRequest('release');
$nb_release = getIntFromRequest('nb_release', 2);
$display_graph = getIntFromRequest('display_graph', 2);

$roadmap_factory = new RoadmapFactory($group);
$roadmaps = $roadmap_factory->getRoadmaps(true);
if (empty($roadmaps)) {
	$information = _('No roadmap available');
	local_exit('<p>'._('The roadmap provides a short view on the trackers by viewing all tickets related to a release.').'</p>'.
		'<p>'.sprintf(_('If you have project administrator rights, you can easily <a href="%s">create roadmaps</a>.'),
				'/tracker/admin/index.php?group_id='.$group_id.'&admin_roadmap=1&new_roadmap=submit').'</p>');
}

$selected_roadmap = null;

if ($roadmap_name) {
	$selected_roadmap = $roadmap_factory->getRoadmapByName($roadmap_name, true);
	if (! is_object($selected_roadmap)) {
		$error_msg .= sprintf(_('Error: roadmap %s is not available'), $roadmap_name);
		local_exit();
	}
	else {
		$roadmap_id = $selected_roadmap->getID();
	}
}
elseif($roadmap_id) {
	$selected_roadmap = $roadmap_factory->getRoadmapByID($roadmap_id, true);
	if (! is_object($selected_roadmap)) {
		$error_msg .= sprintf(_('Error: roadmap %s is not available'), 'ID='.$roadmap_id);
		local_exit();
	}
	else {
		$roadmap_name = $selected_roadmap->getName();
	}
}
else {
	$roadmap_id = $roadmap_factory->getDefault();
	if ($roadmap_id) {
		$selected_roadmap = new Roadmap($group, $roadmap_id);
		if (! is_object($selected_roadmap)) {
			$error_msg .= sprintf(_('Error: roadmap %s is not available'), 'ID='.$roadmap_id);
			local_exit();
		}
	}
	else {
		$selected_roadmap = $roadmaps[0];
		if (! is_object($selected_roadmap)) {
			local_exit(_('No roadmap available'));
		}
		$roadmap_id = $selected_roadmap->getID();
	}
	$roadmap_name = $selected_roadmap->getName();
}
$release_order = array_reverse($selected_roadmap->getReleases());
if ($selected_release && ! in_array($selected_release, $release_order)) {
	$error_msg .= sprintf(_('Error: release %s is not available'), $selected_release);
	local_exit();
}

$rmap = array();

// For the graph
$rel_states_colors = array();
$rel_states = array();
$total_rel_states = array();
$rel_art_states_colors = array();
$rel_art_states = array();
$total_rel_art_states = array();
$graph_class = 0;

$at_arr = $atfh->getArtifactTypes();
$artifact_type_list = $selected_roadmap->getList();

if (!$at_arr || count($at_arr) < 1) {
	local_exit('<p class="information">'._('No trackers have been set up.').'</p>');
} else {
	
	foreach ($at_arr as $artifact_type) {
		if (!is_object($artifact_type)) {
			//just skip it
		} elseif ($artifact_type->isError()) {
			echo $artifact_type->getErrorMessage();
		} else {

			$artifact_type_id = $artifact_type->getID();

			if (! array_key_exists($artifact_type_id, $artifact_type_list) || ! $artifact_type_list[$artifact_type_id]) {
				// This tracker is not used for the roadmap
				continue;
			}
			$field_id = $artifact_type_list[$artifact_type_id];

			$ath = new ArtifactTypeHtml($group, $artifact_type_id);

			if (!forge_check_perm ('tracker', $artifact_type_id, 'read')) {
				continue;
			}

			$artifact_type_name = $artifact_type->getName();
			$uses_custom_status = $artifact_type->usesCustomStatuses();

			// Get all states for this artifact_type; probably useless to do it
			if ($display_graph && $templates[$template]['display_graph']) {
				if ($uses_custom_status) {
					$rel_art_states_colors[$artifact_type_name] = array();
					$arr_status = $artifact_type->getExtraFieldElements($artifact_type->getCustomStatusField());
					foreach ($arr_status as $status) {
						@$red_green_count[$status['status_id']]++;
						$rel_art_states_colors[$artifact_type_name][$status['element_name']] = 0;
					}
				}
				$res = $artifact_type->getStatuses();
				while ($row = db_fetch_array($res)) {
					$rel_states_colors[$row['id']] = 0;
					if (! $uses_custom_status) {
						@$red_green_count[$status['id']]++;
						$rel_art_states_colors[$artifact_type_name][$row['status_name']] = 0;
					}
				}
				
				// Set color radiant for custom states graph
				$nb_colors = count(array_keys($rel_art_states_colors[$artifact_type_name]));
				if (count($red_green_count) && array_key_exists('1', $red_green_count) && array_key_exists('2', $red_green_count) &&
					($red_green_count['1'] + $red_green_count['2']) == $nb_colors) {
					$colors_gradient = array_merge(color_gradient(array('ff0000', 'ffc1bf'), $red_green_count['1']),
												   color_gradient(array('c1ffbf', '00ff00'), $red_green_count['2']));
				}
				else {
					$colors = array('ff0000', '00ff00');
					$colors_gradient = color_gradient($colors, $nb_colors);
				}
				$i = 0;
				foreach ($rel_art_states_colors[$artifact_type_name] as $state => $color) {
					$rel_art_states_colors[$artifact_type_name][$state] = '#'.$colors_gradient[$i];
					$i++;
				}
				
				// Set color radiant for open/closed states graph
				$nb_colors = count(array_keys($rel_states_colors));
				$colors = array('dd0000', '00dd00');
				$colors_gradient = color_gradient($colors, $nb_colors);
				$i = 0;
				foreach ($rel_states_colors as $state => $color) {
					$rel_states_colors[$state] = '#'.$colors_gradient[$i];
					$i++;
				}
			}

			$af = new ArtifactFactory($ath);
			if (!$af || !is_object($af)) {
				exit_error(_('Could Not Get Factory'), 'tracker');
			} elseif ($af->isError()) {
				exit_error($af->getErrorMessage(), 'tracker');
			}

			if ($selected_release) {
				$release_filter = array($selected_release);
			}
			elseif ($nb_release) {
				$release_filter = array_slice($release_order, 0, $nb_release);
			}
			else {
				$release_filter = false;
			}
			$art_arr = $af->getArtifactsByReleases($field_id, $release_filter);

			if (!$art_arr && $af->isError()) {
				exit_error($af->getErrorMessage(), 'tracker');
			}

			if (! count($art_arr)) {
				//echo _('None');
				continue;
			}

			foreach($art_arr as $artifact) {
				$extra_data = $artifact->getExtraFieldDataText();
				$release_value = $extra_data[$field_id]['value'];

				if ($selected_release && $release_value != $selected_release) {
					continue;
				}
				
				$custom_status_name = $artifact->getCustomStatusName();
				$artifact_id = $artifact->getID();

				@$rmap[$release_value][$artifact_type_name] .= $templates[$template]['begin_ticket'];

				// Icon
				if ($artifact->getStatusID() == 1) {
					$icon = html_image('ic/ticket-open.png','','',array('alt' => $custom_status_name, 'title' => $custom_status_name));
				}
				else {
					$icon = html_image('ic/ticket-closed.png','','',array('alt' => $custom_status_name, 'title' => $custom_status_name));
				}
				$rmap[$release_value][$artifact_type_name] .= sprintf($templates[$template]['ticket_icon'], $icon);

				// Artifact id
				$rmap[$release_value][$artifact_type_name] .= sprintf($templates[$template]['ticket_id'],
						dirname(getStringFromServer('PHP_SELF')).'/?func=detail&amp;aid='.$artifact_id .
							'&amp;group_id='. $group_id .'&amp;atid='.$ath->getID(),
						$artifact_id);

				// Summary
				$rmap[$release_value][$artifact_type_name] .= sprintf($templates[$template]['ticket_summary'],
						htmlentities($artifact->getDetails(), ENT_COMPAT, 'UTF-8'),
						$artifact->getSummary());

				$rmap[$release_value][$artifact_type_name] .= $templates[$template]['end_ticket'];

				// Graph
				@$rel_states[$release_value][$artifact->getStatusID()]['count']++;
				$rel_states[$release_value][$artifact->getStatusID()]['name'] = $artifact->getStatusName();
				@$total_rel_states[$release_value]++;
				@$rel_art_states[$release_value][$artifact_type_name][$custom_status_name]['count']++;
				$rel_art_states[$release_value][$artifact_type_name][$custom_status_name]['name'] = $custom_status_name;
				@$total_rel_art_states[$release_value][$artifact_type_name]++;
			}
		}
	}
	
	if (! $ajax) {
		$atfh->header(array('title' => _('Roadmap'), 'modal' => 1));

		// Start selection tools box
		echo '<div id="div_options">';
		echo '<fieldset><legend>'._('Display options').'</legend>';
		echo '<form action="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'" method="post">'."\n";
		echo '<table class="fullwidth"><tr>'."\n";

		if (! empty($roadmaps) && ! $selected_release) {
			echo '<td class="align-center">' . _("Roadmap: ") . '<select name="roadmap" id="roadmap">'."\n";
			foreach ($roadmaps as $roadmap) {
				echo '<option value="'.$roadmap->getID().'"'.($roadmap->getID() == $roadmap_id ? ' selected="selected"' : '').' >'.$roadmap->getName().'</option>'."\n";
			}
			echo '</select></td>'."\n";
		}

		if (! $selected_release) {
			echo '<td class="align-center">'._('Number of release(s) to display: ');
			echo '<select name="nb_release" id="nb_release" >';
			foreach (array(1, 2, 4, 8) as $nb) {
				echo '<option value="'.$nb.'"'.($nb_release == $nb ? ' selected="selected"' : '').' >'.$nb.'</option>';
			}
			echo '<option value="0"'.(! $nb_release ? ' selected="selected"' : '').' >'._('All').'</option>';
			echo '</select></td>';
		}
		else {
			echo sprintf('<td><a href="%s" >'._('Return to last release(s)').'</a></td>'."\n",
					getStringFromServer('PHP_SELF').'?group_id='.$group_id.($roadmap_id ? '&amp;roadmap_id='.$roadmap_id : ''));
		}

		echo '<td class="align-center">' . _("Display graphs: ") . '<select name="display_graph" id="display_graph">'."\n";
		if (! $selected_release) {
			echo '<option value="2"'.($display_graph == 2 ? ' selected="selected"' : '').' >'._('Only last').'</option>'."\n";
		}
		echo '<option value="1"'.($display_graph == 1 ? ' selected="selected"' : '').' >'._('All').'</option>'."\n";
		echo '<option value="0"'.($display_graph == 0 ? ' selected="selected"' : '').' >'._('None').'</option>'."\n";
		echo '</select></td>'."\n";

		// End selection tools box
		echo '<td id="noscript" class="align-center"><input type="submit" name="submit" value="'._('Submit').'" /></td>';
		echo '</tr></table></form></fieldset></div>'."\n";
		echo '<br/>';
	}

	echo '<div id="div_roadmap">'."\n";

	if (empty($release_order)) {
		echo '<p><strong>'._('No release available').'</strong></p>';
	}
	
	for ($i = 0; $i < count($release_order); $i++) {
		$release_value = $release_order[$i];

		if ($selected_release && $release_value != $selected_release) {
			continue;
		}

		if (! $selected_release && $nb_release && $i >= $nb_release) {
			break;
		}

		echo '<div id="release_'.$i.'" >'."\n";
		echo sprintf($templates[$template]['begin_release'],
				$roadmap_name._(': '),
				getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;roadmap_id='.$roadmap_id.'&amp;release='.urlencode($release_value),
				$release_value,
				' '.html_image("ic/file-txt.png",'','',array("title"=>_('Display as text'), "onclick"=>"getReleaseTxt('".addslashes($release_value)."')")));

		// Graph
		if ($display_graph && $templates[$template]['display_graph']) {
			$graph = '';
			$legend = '';
			if (array_key_exists($release_value, $rel_states) && is_array($rel_states[$release_value])) {
				foreach ($rel_states_colors as $state => $color) {
					if (array_key_exists($state, $rel_states[$release_value])) {
						$data = $rel_states[$release_value][$state];
						$percent = round($data['count'] / $total_rel_states[$release_value] * 100);
						$graph  .= '<td style="background: '.$color.'; width: '.$percent.'%;">&#160;</td>';
						$legend .= '<td style="white-space: nowrap; width: '.$percent.'%;"><i>'.$data['name'].': '.$data['count'].' ('.$percent.'%)</i></td>';
					}
				}
			}
			if ($graph){
				?>
				<div class="graph<?php echo $graph_class ?>" <?php echo ($graph_class && $display_graph == 2 ? 'style="display: none"' : '') ?>>
				<table class="fullwidth">
				<tr>
					<td class="align-center">
					<table class="progress halfwidth">
					<tbody>
						<tr><?php echo $graph; ?></tr>
					</tbody>
					</table>
					<table class="halfwidth">
						<tr class="align-center"><?php echo $legend ?></tr>
					</table>
					</td>
				</tr>
				</table>
				</div>
				<?php
			}
		}

		echo ''."\n";

		if (! array_key_exists($release_value, $rmap)) {
			echo '<p>'._('No data for this release').'</p>'."\n";
		}
		else {
			foreach ($rmap[$release_value] as $artifact_type_name => $ticket_list) {
				echo '<h3>'.$artifact_type_name.'</h3>'."\n";

				// Graph
				if ($display_graph && $templates[$template]['display_graph']) {
					$graph = '';
					$legend = '';
					if (array_key_exists($release_value, $rel_art_states) && is_array($rel_art_states[$release_value]) &&
						array_key_exists($artifact_type_name, $rel_art_states[$release_value]) && is_array($rel_art_states[$release_value][$artifact_type_name])) {
						$total = 0;
						foreach ($rel_art_states_colors[$artifact_type_name] as $state => $color) {
							if (array_key_exists($state, $rel_art_states[$release_value][$artifact_type_name])) {
								$data = $rel_art_states[$release_value][$artifact_type_name][$state];
								$percent = round($data['count'] / $total_rel_art_states[$release_value][$artifact_type_name] * 100);
								$total += $data['count'];
								$graph  .= '<td style="background: '.$color.'; width: '.$percent.'%;">&#160;</td>';
								$legend .= '<td style="white-space: nowrap; width: '.$percent.'%;"><i>'.$data['name'].': '.$data['count'].' ('.$percent.'%)</i></td>';
							}
						}
						// Display unknown statuses if exists
						if ($total != $total_rel_art_states[$release_value][$artifact_type_name]) {
							$percent = round((1 - $total / $total_rel_art_states[$release_value][$artifact_type_name]) * 100);
							$graph  .= '<td style="background: #aaaaaa; width: '.$percent.'%;">&#160;</td>';
							$legend .= '<td style="white-space: nowrap; width: '.$percent.'%;"><i>'._('Unknown').': '.($total_rel_art_states[$release_value][$artifact_type_name] - $total).' ('.$percent.'%)</i></td>';
						}
					}

					if ($graph) {
						?>
						<div class="graph<?php echo $graph_class ?>" <?php echo ($graph_class && $display_graph == 2 ? 'style="display: none"' : '') ?>>
						<table class="fullwidth">
						<tr>
							<td class="align-center">
							<table class="progress halfwidth">
							<tbody>
								<tr><?php echo $graph; ?></tr>
							</tbody>
							</table>
							<table class="halfwidth">
								<tr class="align-center"><?php echo $legend ?></tr>
							</table>
							</td>
						</tr>
						</table>
						</div>
						<?php
					}
				}

				echo $templates[$template]['begin_tracker'];
				echo $ticket_list;
				echo $templates[$template]['end_tracker'];
				echo $templates[$template]['separator'];
			}
		}
		//echo '<hr/>';
		echo $templates[$template]['end_release'];
		echo '</div><br/>'."\n";

		$graph_class = 1;
	}
	echo '</div>'."\n";

	if (!$ajax) {
		?>
		<script type="text/javascript">/* <![CDATA[ */
			<?php
			if (! $selected_release) {
			?>
				jQuery('#roadmap').change(function() {
					updatePage();
				});
				
				jQuery('#nb_release').change(function() {
					updatePage();
				});
			<?php
			}

			?>
			jQuery('#display_graph').change(function() {
				var select_val = jQuery('#display_graph').val();
				var divs = document.getElementsByTagName('div');
				for (var i = 0; i < divs.length; i++) {
					if (divs[i].className == 'graph0') {
					  if (select_val == 1 || select_val == 2) {
						  divs[i].style.display = 'inline';
					  }
					  else {
						  divs[i].style.display = 'none';
					  }
					}
					if (divs[i].className == 'graph1') {
					  if (select_val == 1) {
						  divs[i].style.display = 'inline';
					  }
					  else {
						  divs[i].style.display = 'none';
					  }
					}
				}
			});

			function updatePage() {
				jQuery('#div_roadmap').empty();
				jQuery('#div_roadmap').append('<div class="align-center"><img src="<?php echo $gfwww ?>/images/ajax-loader.gif" /></div>');
				jQuery.ajax({
					type: 'POST',
					url: 'roadmap.php',
					data: {
						group_id: <?php echo $group_id ?>,
						roadmap_id: jQuery('#roadmap').val(),
						nb_release: jQuery('#nb_release').val(),
						display_graph: jQuery('#display_graph').val(),
						ajax: 1
					},
					success: function(rep) {
						jQuery('#div_roadmap').empty();
						jQuery('#div_roadmap').append(rep);
					}
				});
			}

			function getReleaseTxt(release) {
				var selected_roadmap = $('#roadmap').val();
				var selected_nb_release = $('#nb_release').val();
				var selected_display_graph = $('#display_graph').val();
				jQuery.ajax({
					type: 'POST',
					url: 'roadmap.php',
					data: '<?php echo 'group_id='.$group_id.'&roadmap_id='.$roadmap_id ?>&ajax=1&template=1&release='+release,
					success: function(rep) {
						jQuery('#div_options').empty();
						jQuery('#div_options').append('<a href="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id ?>&roadmap_id='+selected_roadmap+'&nb_release='+selected_nb_release+'&display_graph='+selected_display_graph+'" ><?php echo _('Return to last release(s)') ?></a>');
						jQuery('#div_roadmap').empty();
						jQuery('#div_roadmap').append(rep);
					}
				});
			}

			function hideFormButton() {
				var element = document.getElementById('noscript');
				if (element) element.style.display = 'none';
			}
			hideFormButton();
		/* ]]> */</script>
		<?php
	}
}

if (! $ajax) {
	$atfh->footer();
}

?>
