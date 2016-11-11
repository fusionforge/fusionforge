<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013-2016, Franck Villaume - TrivialDev
 *
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fusionforge. If not, see <http://www.gnu.org/licenses/>.
 */

require_once $gfcommon.'widget/WidgetLayout.class.php';
require_once $gfcommon.'widget/Widget.class.php';
require_once $gfcommon.'include/preplugins.php';

/**
 * WidgetLayoutManager
 *
 * Manage layouts for users, groups and homepage
 */
class WidgetLayoutManager {
	const OWNER_TYPE_USER  = 'u';
	/**
	 * Layout for project home
	 * @var string
	 */
	const OWNER_TYPE_GROUP = 'g';
	const OWNER_TYPE_HOME  = 'h';

	/**
	 * displayLayout
	 *
	 * Display the default layout for the "owner". It may be the home page, the project summary page or /my/ page.
	 *
	 * @param	int	$owner_id
	 * @param	string	$owner_type
	 */
	function displayLayout($owner_id, $owner_type) {
		$sql = "SELECT * from owner_layouts where owner_id=$1 and owner_type=$2";
		$res = db_query_params($sql, array($owner_id, $owner_type));
		if($res && db_numrows($res)<1) {
			if($owner_type == self::OWNER_TYPE_USER) {
				$this->createDefaultLayoutForUser($owner_id);
				$this->displayLayout($owner_id,$owner_type);
			} elseif ($owner_type == self::OWNER_TYPE_GROUP) {
				$this->createDefaultLayoutForProject($owner_id, 1);
				$this->displayLayout($owner_id,$owner_type);
			} elseif ($owner_type == self::OWNER_TYPE_HOME) {
				$this->createDefaultLayoutForForge($owner_id);
				$this->displayLayout($owner_id, $owner_type);
			}
		} else {
			$sql = "SELECT l.*
				FROM layouts AS l INNER JOIN owner_layouts AS o ON(l.id = o.layout_id)
				WHERE o.owner_type = $1
				AND o.owner_id = $2
				AND o.is_default = 1
				";
			$req = db_query_params($sql, array($owner_type ,$owner_id));
			if ($data = db_fetch_array($req)) {
				$readonly = !$this->_currentUserCanUpdateLayout($owner_id, $owner_type);
				$layout = new WidgetLayout($data['id'], $data['name'], $data['description'], $data['scope']);
				$sql = 'SELECT * FROM layouts_rows WHERE layout_id = $1 ORDER BY rank';
				$req_rows = db_query_params($sql,array($layout->id));
				while ($data = db_fetch_array($req_rows)) {
					$row = new WidgetLayout_Row($data['id'], $data['rank']);
					$sql = 'SELECT * FROM layouts_rows_columns WHERE layout_row_id = $1';
					$req_cols = db_query_params($sql,array($row->id));
					while ($data = db_fetch_array($req_cols)) {
						$col = new WidgetLayout_Row_Column($data['id'], $data['width']);
						$sql = "SELECT * FROM layouts_contents WHERE owner_type = $1  AND owner_id = $2 AND column_id = $3 ORDER BY rank";
						$req_content = db_query_params($sql,array($owner_type, $owner_id, $col->id));
						while ($data = db_fetch_array($req_content)) {
							$c = Widget::getInstance($data['name']);
							if ($c && $c->isAvailable()) {
								$c->loadContent($data['content_id']);
								$col->add($c, $data['is_minimized'], $data['display_preferences']);
							}
							unset($c);
						}
						$row->add($col);
						unset($col);
					}
					$layout->add($row);
					unset($row);
				}
				$layout->display($readonly, $owner_id, $owner_type);
			}
		}
	}

	/**
	 * _currentUserCanUpdateLayout
	 *
	 * @param	int	$owner_id
	 * @param	string	$owner_type
	 * @return	boolean	true if the user can update the layout (add/remove widget, collapse, set preferences, ...)
	 */
	function _currentUserCanUpdateLayout($owner_id, $owner_type) {
		$readonly = true;
		$request =& HTTPRequest::instance();
		switch ($owner_type) {
			case self::OWNER_TYPE_USER:
				if (user_getid() == $owner_id) { //Current user can only update its own /my/ page
					$readonly = false;
				}
				break;
			case self::OWNER_TYPE_GROUP:
				if (forge_check_perm('project_admin', $owner_id, NULL)) { //Only project admin
					$readonly = false;
				}
				break;
			case self::OWNER_TYPE_HOME:
				if (forge_check_global_perm('forge_admin')) { //Only site admin
					$readonly = false;
				}
				break;
			default:
				break;
		}
		return !$readonly;
	}
	/**
	 * createDefaultLayoutForUser
	 *
	 * Create the first layout for the user and add some initial widgets:
	 * - MyArtifacts
	 * - MyProjects
	 * - MyBookmarks
	 * - MySurveys
	 * - MyMonitoredFP
	 * - MyMonitoredForums
	 * - and widgets of plugins if they want to listen to the event default_widgets_for_new_owner
	 *
	 * @param	int	$owner_id The id of the newly created user
	 */
	function createDefaultLayoutForUser($owner_id) {
		$owner_type = self::OWNER_TYPE_USER;
		db_begin();
		$success = true;
		$sql = "INSERT INTO owner_layouts(layout_id, is_default, owner_id, owner_type) VALUES (1, 1, $1, $2)";
		if (db_query_params($sql, array($owner_id, $owner_type))) {

			$sql = "INSERT INTO layouts_contents(owner_id, owner_type, layout_id, column_id, name, rank) VALUES ";

			$args[] = "($1, $2, 1, 1, 'myprojects', 0)";
			$args[] = "($1, $2, 1, 1, 'mybookmarks', 1)";
			$args[] = "($1, $2, 1, 1, 'mymonitoredforums', 2)";
			$args[] = "($1, $2, 1, 1, 'mysurveys', 4)";
			$args[] = "($1, $2, 1, 2, 'myartifacts', 0)";
			$args[] = "($1, $2, 1, 2, 'mymonitoredfp', 1)";

			foreach($args as $a) {
				if (!db_query_params($sql.$a,array($owner_id,$owner_type))) {
					$success = false;
					break;
				}
			}

			/*  $em =& EventManager::instance();
			    $widgets = array();
			    $em->processEvent('default_widgets_for_new_owner', array('widgets' => &$widgets, 'owner_type' => $owner_type));
			    foreach($widgets as $widget) {
			    $sql .= ",($13, $14, 1, $15, $16, $17)";
			    }*/
		} else
			$success = false;
		if (!$success) {
			$success = db_error();
			db_rollback();
			exit_error(sprintf(_('DB Error: %s'), $success), 'widgets');
		}
		db_commit();
	}

	function createDefaultLayoutForForge($owner_id) {
		db_begin();
		$success = true;
		$sql = "INSERT INTO owner_layouts (owner_id, owner_type, layout_id, is_default) values ($1, $2, $3, $4)";
		if (db_query_params($sql, array($owner_id, self::OWNER_TYPE_HOME, 1, 1))) {

			$sql = "INSERT INTO layouts_contents(owner_id, owner_type, layout_id, column_id, name, rank) VALUES ";

			$args[] = "($1, $2, 1, 2, 'hometagcloud', 0)";
			$args[] = "($1, $2, 1, 2, 'homestats', 1)";
			$args[] = "($1, $2, 1, 2, 'homeversion', 2)";
			$args[] = "($1, $2, 1, 1, 'homewelcome', 0)";
			$args[] = "($1, $2, 1, 1, 'homelatestnews', 1)";

			foreach($args as $a) {
				if (!db_query_params($sql.$a,array(0, self::OWNER_TYPE_HOME))) {
					$success = false;
					break;
				}
			}
		} else
			$success = false;
		if (!$success) {
			$success = db_error();
			db_rollback();
			exit_error(sprintf(_('DB Error: %s'), $success), 'widgets');
		}
		db_commit();
	}

	/**
	 * createDefaultLayoutForProject
	 *
	 * Create the first layout for a new project, based on its parent template.
	 * Add some widgets based also on its parent configuration and on its service configuration.
	 *
	 * @param	int	$group_id  the id of the newly created project
	 * @param	int	$template_id  the id of the project template
	 */
	function createDefaultLayoutForProject($group_id, $template_id) {
		$pm = ProjectManager::instance();
		$project = $pm->getProject($group_id);
		$sql = "INSERT INTO owner_layouts(layout_id, is_default, owner_id, owner_type)
			SELECT layout_id, is_default, $1, owner_type
			FROM owner_layouts
			WHERE owner_type = $2
			AND owner_id = $3
			";
		if (db_query_params($sql,array($group_id, self::OWNER_TYPE_GROUP,$template_id))) {
			$sql = "SELECT layout_id, column_id, name, rank, is_minimized, is_removed, display_preferences, content_id
				FROM layouts_contents
				WHERE owner_type = $1
				AND owner_id = $2
				";
			if ($req = db_query_params($sql,array( self::OWNER_TYPE_GROUP,$template_id))) {
				while($data = db_fetch_array($req)) {
					$w = Widget::getInstance($data['name']);
					if ($w) {
						$w->setOwner($template_id, self::OWNER_TYPE_GROUP);
						if ($w->canBeUsedByProject($project)) {
							$content_id = $w->cloneContent($w->content_id, $group_id, self::OWNER_TYPE_GROUP);
							$sql = "INSERT INTO layouts_contents(owner_id, owner_type, content_id, layout_id, column_id, name, rank, is_minimized, is_removed, display_preferences)
								VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10);
							";
							db_query_params($sql, array($group_id , self::OWNER_TYPE_GROUP , $content_id ,  $data['layout_id'] ,  $data['column_id'] ,  $data['name'] ,  $data['rank'] ,  $data['is_minimized'] ,  $data['is_removed'] ,  $data['display_preferences'] ));
							echo db_error();
						}
					}
				}
			}
		}
		echo db_error();
	}

	/**
	 * displayAvailableWidgets - Display all widgets that the user can add to the layout
	 *
	 * @param	int	$owner_id
	 * @param	string	$owner_type
	 * @param	int	$layout_id
	 */
	function displayAvailableWidgets($owner_id, $owner_type, $layout_id) {
		global $HTML;
		// select already used widgets
		$used_widgets = array();
		$sql = "SELECT *
			FROM layouts_contents
			WHERE owner_type = $1
			AND owner_id = $2
			AND layout_id = $3
			AND content_id = 0 AND column_id <> 0";
		$res = db_query_params($sql,array($owner_type,$owner_id,$layout_id));
		while($data = db_fetch_array($res)) {
			$used_widgets[] = $data['name'];
		}
		// build & display contextual toolbar
		$url = '/widgets/widgets.php?owner='.HTTPRequest::instance()->get('owner').
			'&layout_id='.HTTPRequest::instance()->get('layout_id');
		$elementsLi = array();
		$elementsLi[0]['content'] = util_make_link($url, _('Add widgets'));
		$elementsLi[1]['content'] = util_make_link($url.'&update=layout', _('Customize Layout'));
		$update_layout = (HTTPRequest::instance()->get('update') == 'layout');
		if ($update_layout) {
			// customized selected
			$elementsLi[1]['attrs'] = array('class' => 'current');
			$action = 'layout';
		} else {
			// add selected, or default when first displayed
			$elementsLi[0]['attrs'] = array('class' => 'current');
			$action = 'widget';
		}
		echo $HTML->html_list($elementsLi, array('class' => 'widget_toolbar'));
		echo $HTML->openForm(array('action' => '/widgets/updatelayout.php?owner='.$owner_type.$owner_id.'&action='.$action.'&layout_id='.$layout_id, 'method' => 'post'));
		if ($update_layout) {
			?>
			<script type='text/javascript'>//<![CDATA[
				var controllerLayoutBuilder;
				jQuery(document).ready(function() {
					controllerLayoutBuilder = new LayoutBuilderController({
						buttonAddRow:		jQuery('.layout-manager-row-add'),
						buttonAddColumn:	jQuery('.layout-manager-column-add'),
						buttonRemoveColumn:	jQuery('.layout-manager-column-remove')
					});
					jQuery('#save').click(function(){
						if (jQuery('#layout_custom').is(':checked')) {
							var form = jQuery('#layout-manager').parents('form').first();
							jQuery('#layout-manager').find('.layout-manager-row').each(function(i, e) {
								jQuery('<input>', {
									type: 'hidden',
									name: 'new_layout[]',
									value: jQuery(e).find('.layout-manager-column input[type=number]').map(function(){ return this.value;}).get().join(',')
									}).appendTo(form);
							});
						}
					});
					jQuery('.layout-manager-chooser').each(function(i, e) {
						jQuery(e).find('input[type=radio]').change(function() {
							jQuery('.layout-manager-chooser').each(function(i, e) {
								jQuery(e).removeClass('layout-manager-chooser_selected');
							});
							jQuery(e).addClass('layout-manager-chooser_selected');
						});
					});
				});
			//]]></script>
			<?php
			$sql = "SELECT * FROM layouts WHERE scope='S' ORDER BY id ";
			$req_layouts = db_query_params($sql,array());
			echo $HTML->listTableTop();
			$is_custom = true;
			while ($data = db_fetch_array($req_layouts)) {
				$checked = $layout_id == $data['id'] ? 'checked="checked"' : '';
				$is_custom = $is_custom && !$checked;
				echo '<tr class="layout-manager-chooser '. ($checked ? 'layout-manager-chooser_selected' : '') .'" ><td>';
				echo '<input type="radio" name="layout_id" value="'. $data['id'] .'" id="layout_'. $data['id'] .'" '. $checked .'/>';
				echo '</td><td>';
				echo html_e('label', array('for' => 'layout_'. $data['id']), html_image('layout/'. strtolower(preg_replace('/(\W+)/', '-', $data['name'])) .'.png'));
				echo '</td><td>';
				echo html_e('label', array('for' => 'layout_'. $data['id']), html_e('strong', array(), $data['name']).html_e('br').$data['description']);
				echo '</td></tr>';
			}
			/* Custom layout are not available yet */
			$checked = $is_custom ? 'checked="checked"' : '';
			echo '<tr class="layout-manager-chooser '. ($checked ? 'layout-manager-chooser_selected' : '') .'"><td>';
			echo '<input type="radio" name="layout_id" value="-1" id="layout_custom" '. $checked .'/>';
			echo '</td><td>';
			echo html_e('label', array('for' => 'layout_custom'), html_image('layout/custom.png', '', '', array('style' => 'vertical-align:top;float:left;')));
			echo '</td><td>';
			echo html_e('label', array('for' => 'layout_custom'), html_e('strong', array(), _('Custom')).html_e('br')._('Define your own layout')._(':'));
			echo '<table id="layout-manager">
				<tr>
				<td>
				<div class="layout-manager-row-add">+</div>';
			$sql = 'SELECT * FROM layouts_rows WHERE layout_id = $1 ORDER BY rank';
			$req_rows = db_query_params($sql,array($layout_id));
			while ($data = db_fetch_array($req_rows)) {
				echo '<table class="layout-manager-row">
					<tr>
					<td class="layout-manager-column-add">+</td>';
				$sql = 'SELECT * FROM layouts_rows_columns WHERE layout_row_id = $1';
				$req_cols = db_query_params($sql,array($data['id']));
				while ($data = db_fetch_array($req_cols)) {
					echo '<td class="layout-manager-column">
						<div class="layout-manager-column-remove">x</div>
						<div class="layout-manager-column-width">
						<input type="number" value="'. $data['width'] .'" size="1" maxlength="3" />%
						</div>
						</td>
						<td class="layout-manager-column-add">+</td>';
				}
				echo '  </tr>
					</table>';
				echo html_e('div', array('class' => 'layout-manager-row-add'), '+');
			}
			echo '    </td>
				</tr>
				</table>';
			echo '</td></tr>';
			echo $HTML->listTableBottom();
			echo html_e('input', array('type' => 'submit', 'id' => 'save', 'value' => _('Submit')));
		} else {
			// display the widget selection form
			$after = '';
			echo '<table>
				<tbody>
				<tr class="top">
				<td>';
			$after .= $this->_displayWidgetsSelectionForm(sprintf(_("%s Widgets"),  forge_get_config('forge_name')), Widget::getCodendiWidgets($owner_type), $used_widgets);
			echo '</td>
				<td id="widget-content-categ">'. $after .'</td>
				</tr>
				</tbody>
				</table>';
		}
		echo $HTML->closeForm();
	}

	function updateLayout($owner_id, $owner_type, $layout, $custom_layout) {
		$sql = "SELECT l.*
			FROM layouts AS l INNER JOIN owner_layouts AS o ON(l.id = o.layout_id)
			WHERE o.owner_type = $1
			AND o.owner_id = $2
			AND o.is_default = 1
			";
		$req = db_query_params($sql, array($owner_type, $owner_id));
		if ($data = db_fetch_array($req)) {
			if ($this->_currentUserCanUpdateLayout($owner_id, $owner_type)) {
				$old_scope = $data['scope'];
				$old_layout_id = $data['id'];
				$new_layout_id = null;
				if ($layout == '-1' && is_array($custom_layout)) {
					//Create a new layout based on the custom layout structure defined by the user
					$rows = array();
					foreach($custom_layout as $widths) {
						$row = array();
						$cols = explode(',', $widths);
						foreach($cols as $col) {
							if ($width = (int)$col) {
								$row[] = $width;
							}
						}
						if (count($row)) {
							$rows[] = $row;
						}
					}
					//If the structure contains at least one column, create a new layout
					if (count($rows)) {
						$sql = "INSERT INTO layouts(name, description, scope)
							VALUES ('custom', '', 'P')";
						if ($res = db_query_params($sql, array())) {
							if ($new_layout_id = db_insertid($res, 'layouts', 'id')) {
								//Create rows & columns
								$rank = 0;
								foreach($rows as $cols) {
									$sql = "INSERT INTO layouts_rows(layout_id, rank)
										VALUES ($1,$2)";
									if ($res = db_query_params($sql, array($new_layout_id, $rank++))) {
										if ($row_id = db_insertid($res,'layouts_rows', 'id')) {
											foreach($cols as $width) {
												$sql = "INSERT INTO layouts_rows_columns(layout_row_id, width)
													VALUES ($1,$2)";
												db_query_params($sql, array($row_id, $width));
											}
										}
									}
								}
							}
						}
					}
				} else {
					$new_layout_id = $layout;
				}

				if ($new_layout_id) {
					//Retrieve columns of old layout
					$old = $this->_retrieveStructureOfLayout($old_layout_id);

					//Retrieve columns of new layout
					$new = $this->_retrieveStructureOfLayout($new_layout_id);

					// Switch content from old columns to new columns
					$last_new_col_id = null;
					reset($new['columns']);
					foreach($old['columns'] as $old_col) {
						if (list(,$new_col) = each($new['columns'])) {
							$last_new_col_id = $new_col['id'];
						}
						$sql = "UPDATE layouts_contents
							SET layout_id  = $1
							, column_id  =$2
							WHERE owner_type =$3
							AND owner_id   =$4
							AND layout_id  =$5
							AND column_id  =$6;";
						db_query_params($sql,array($new_layout_id,$last_new_col_id,$owner_type,$owner_id,$old_layout_id,$old_col['id']));
					}
					$sql = "UPDATE owner_layouts
						SET layout_id  = $1
						WHERE owner_type = $2
						AND owner_id   = $3
						AND layout_id  = $4";
					db_query_params($sql, array($new_layout_id, $owner_type, $owner_id, $old_layout_id));

					//If the old layout is custom remove it
					if ($old_scope != 'S') {
						$structure = $this->_retrieveStructureOfLayout($old_layout_id);
						foreach($structure['rows'] as $row) {
							$sql = "DELETE FROM layouts_rows
								WHERE id  = $1";
							db_query_params($sql, array($row['id']));
							$sql = "DELETE FROM layouts_rows_columns
								WHERE layout_row_id  = $1";
							db_query_params($sql, array($row['id']));
						}
						$sql = "DELETE FROM layouts
							WHERE id  = $1";
						db_query_params($sql, array($old_layout_id));
					}

				}
			}
		}
		$this->feedback();
	}

	function _retrieveStructureOfLayout($layout_id) {
		$structure = array('rows' => array(), 'columns' => array());
		$sql = 'SELECT * FROM layouts_rows WHERE layout_id = $1 ORDER BY rank';
		$req_rows = db_query_params($sql,array($layout_id));
		while ($row = db_fetch_array($req_rows)) {
			$structure['rows'][] = $row;
			$sql = 'SELECT * FROM layouts_rows_columns WHERE layout_row_id =$1 ORDER BY id';
			$req_cols = db_query_params($sql,array($row['id']));
			while ($col = db_fetch_array($req_cols)) {
				$structure['columns'][] = $col;
			}
		}
		return $structure;
	}

	/**
	 * _displayWidgetsSelectionForm - displays a widget selection form
	 *
	 * @param	title		$title
	 * @param	widgets		$widgets
	 * @param	used_widgets	$used_widgets
	 * @return	string
	 */
	function _displayWidgetsSelectionForm($title, $widgets, $used_widgets) {
		$hp = Codendi_HTMLPurifier::instance();
		$additionnal_html = '';
		if (count($widgets)) {
			$categs = $this->getCategories($widgets);
			$widget_rows = array();
			if (count($categs)) {
				// display the categories selector in left panel
				foreach($categs as $c => $ws) {
					$widget_rows[$c] = util_make_link('#widget-categ-'.$c, html_e('span', array(), str_replace('_',' ', $hp->purify($c, CODENDI_PURIFIER_CONVERT_HTML))), array('class' => 'widget-categ-switcher', 'id' => 'widget-categ-switcher-'.$c, 'onClick' => 'jQuery(\'.widget-categ-class-void\').hide();jQuery(\'.widget-categ-switcher\').removeClass(\'selected\');jQuery(\'#widget-categ-'. $c .'\').show();jQuery(\'#widget-categ-switcher-'. $c .'\').addClass(\'selected\')'), true);
				}
				uksort($widget_rows, 'strnatcasecmp');
				echo html_ao('ul', array('id' => 'widget-categories'));
				foreach($widget_rows as $row) {
					echo html_e('li', array(), $row, false);
				}
				echo html_ac(html_ap() - 1);
				foreach($categs as $c => $ws) {
					$i = 0;
					$widget_rows = array();
					// display widgets of the category
					foreach($ws as $widget_name => $widget) {
						$row = html_e('div', array('class' => 'widget-preview '. $widget->getPreviewCssClass()),
								html_e('h3', array(), $widget->getTitle()).
								html_e('p', array(), $widget->getDescription()).
								$widget->getInstallPreferences());
						$row .= '<div style="text-align:right; border-bottom:1px solid #ddd; padding-bottom:10px; margin-bottom:20px;">';
						if ($widget->isUnique() && in_array($widget_name, $used_widgets)) {
							$row .= html_e('em', array(), _('Already used'));
						} else {
							$row .= html_e('input', array('type' => 'submit', 'name' => 'name['. $widget_name .'][add]', 'value' => _('Add')));
						}
						$row .= '</div>';
						$widget_rows[$widget->getTitle()] = $row;
					}
					uksort($widget_rows, 'strnatcasecmp');
					$additionnal_html .= '<div id="widget-categ-'. $c .'" class="widget-categ-class-void hide" ><h2 class="boxtitle">'. str_replace('_',' ',$hp->purify($c, CODENDI_PURIFIER_CONVERT_HTML)) .'</h2>';
					foreach($widget_rows as $row) {
						$additionnal_html .= $row;
					}
					$additionnal_html .= '</div>';
				}
			}
		}
		return $additionnal_html;
	}

	/**
	 * getCategories - sort the widgets in their different categories
	 *
	 * @param	array	$widgets
	 * @return	array	(category => widgets)
	 */
	function getCategories($widgets) {
		$categ = array();
		foreach($widgets as $widget_name) {
			if ($widget = Widget::getInstance($widget_name)) {
				if ($widget->isAvailable()) {
					$category = str_replace(' ', '_', $widget->getCategory());
					$cs = explode(',', $category);
					foreach($cs as $c) {
						if ($c = trim($c)) {
							if (!isset($categ[$c])) {
								$categ[$c] = array();
							}
							$categ[$c][$widget_name] = $widget;
						}
					}
				}
			}
		}
		return $categ;
	}
	/**
	 * addWidget
	 *
	 * @param	int	$owner_id
	 * @param	string	$owner_type
	 * @param	int	$layout_id
	 * @param	string	$name
	 * @param	object	$widget
	 * @param	object	$request
	 */
	function addWidget($owner_id, $owner_type, $layout_id, $name, &$widget, &$request) {
		//Search for the right column. (The first used)
		$sql = "SELECT u.column_id AS id
			FROM layouts_contents AS u
			LEFT JOIN (SELECT r.rank AS rank, c.id as id
					FROM layouts_rows AS r INNER JOIN layouts_rows_columns AS c
					ON (c.layout_row_id = r.id)
					WHERE r.layout_id = $1) AS col
			ON (u.column_id = col.id)
			WHERE u.owner_type = $2
			AND u.owner_id = $3
			AND u.layout_id = $4
			AND u.column_id <> 0
			ORDER BY col.rank, col.id";
		$res = db_query_params($sql,array($layout_id,$owner_type,$owner_id,$layout_id));
		echo db_error();
		$column_id = db_result($res, 0, 'id');
		if (!$column_id) {
			$sql = "SELECT r.rank AS rank, c.id as id
				FROM layouts_rows AS r
				INNER JOIN layouts_rows_columns AS c
				ON (c.layout_row_id = r.id)
				WHERE r.layout_id = $1
				ORDER BY rank, id";
			$res = db_query_params($sql,array($layout_id));
			$column_id = db_result($res, 0, 'id');
		}

		//content_id
		if ($widget->isUnique()) {
			//unique widgets do not have content_id
			$content_id = 0;
		} else {
			$content_id = $widget->create($request);
		}

		//See if it already exists but not used
		$sql = "SELECT column_id FROM layouts_contents
			WHERE owner_type =$1
			AND owner_id = $2
			AND layout_id = $3
			AND name = $4";
		$res = db_query_params($sql,array($owner_type,$owner_id,$layout_id, $name));
		echo db_error();
		if (db_numrows($res) && !$widget->isUnique() && db_result($res, 0, 'column_id') == 0) {
			//search for rank
			$sql = "SELECT min(rank) - 1 AS rank FROM layouts_contents WHERE owner_type =$1 AND owner_id = $2 AND layout_id = $3 AND column_id = $4 ";
			$res = db_query_params($sql,array($owner_type, $owner_id, $layout_id,$column_id));
			echo db_error();
			$rank = db_result($res, 0, 'rank');

			//Update
			$sql = "UPDATE layouts_contents
				SET column_id = $1, rank = $2
				WHERE owner_type = $3
				AND owner_id = $4
				AND name = $5
				AND layout_id = $6";
			$res = db_query_params($sql,array($column_id,$rank,$owner_type, $owner_id,$name, $layout_id));
			echo db_error();
		} else {
			//Insert
			$sql = "INSERT INTO layouts_contents(owner_type, owner_id, layout_id, column_id, name, content_id, rank)
				SELECT R1.owner_type, R1.owner_id, R1.layout_id, R1.column_id, $1, $2, coalesce(R2.rank, 1) - 1
				FROM ( SELECT $3::character varying(1) AS owner_type, $4::integer AS owner_id, $5::integer AS layout_id, $6::integer AS column_id ) AS R1
				LEFT JOIN layouts_contents AS R2 USING ( owner_type, owner_id, layout_id, column_id )
				ORDER BY rank ASC
				LIMIT 1";
			db_query_params($sql,array($name,$content_id,$owner_type,$owner_id,$layout_id,$column_id));
			echo db_error();
		}
		$this->feedback();
	}

	protected function feedback() {
		global $feedback;
		$feedback .= _('Your dashboard has been updated.');
	}

	/**
	 * removeWidget
	 *
	 * @param	int	$owner_id
	 * @param	string	$owner_type
	 * @param	int	$layout_id
	 * @param	string	$name
	 * @param	int	$instance_id
	 * @param	object	$widget
	 */
	function removeWidget($owner_id, $owner_type, $layout_id, $name, $instance_id, &$widget) {
		$sql = "DELETE FROM layouts_contents WHERE owner_type =$1 AND owner_id = $2 AND layout_id = $3 AND name = $4 AND content_id = $5";
		db_query_params($sql,array($owner_type,$owner_id,$layout_id,$name,$instance_id));
		if (!db_error()) {
			$widget->destroy($instance_id);
		}
	}

	/**
	 * mimizeWidget
	 *
	 * @param	int	$owner_id
	 * @param	string	$owner_type
	 * @param	int	$layout_id
	 * @param	string	$name
	 * @param	int	$instance_id
	 */
	function mimizeWidget($owner_id, $owner_type, $layout_id, $name, $instance_id) {
		$sql = "UPDATE layouts_contents SET is_minimized = 1 WHERE owner_type = $1 AND owner_id = $2 AND layout_id = $3 AND name = $4 AND content_id = $5";
		db_query_params($sql,array($owner_type,$owner_id,$layout_id,$name,$instance_id));
		echo db_error();
	}

	/**
	 * maximizeWidget
	 *
	 * @param	int	$owner_id
	 * @param	string	$owner_type
	 * @param	int	$layout_id
	 * @param	string	$name
	 * @param	int	$instance_id
	 */
	function maximizeWidget($owner_id, $owner_type, $layout_id, $name, $instance_id) {
		$sql = "UPDATE layouts_contents SET is_minimized = 0 WHERE owner_type =$1 AND owner_id =$2 AND layout_id = $3 AND name = $4 AND content_id = $5";
		db_query_params($sql,array($owner_type,$owner_id,$layout_id,$name,$instance_id));
		echo db_error();
	}

	/**
	 * displayWidgetPreferences
	 *
	 * @param	int	$owner_id
	 * @param	string	$owner_type
	 * @param	int	$layout_id
	 * @param	string	$name
	 * @param	int	$instance_id
	 */
	function displayWidgetPreferences($owner_id, $owner_type, $layout_id, $name, $instance_id) {
		$sql = "UPDATE layouts_contents SET display_preferences = 1, is_minimized = 0 WHERE owner_type = $1 AND owner_id = $2 AND layout_id = $3 AND name = $4 AND content_id = $5";
		db_query_params($sql,array($owner_type,$owner_id,$layout_id,$name,$instance_id));
		echo db_error();
	}

	/**
	 * hideWidgetPreferences
	 *
	 * @param	int	$owner_id
	 * @param	string	$owner_type
	 * @param	int	$layout_id
	 * @param	string	$name
	 * @param	int	$instance_id
	 */
	function hideWidgetPreferences($owner_id, $owner_type, $layout_id, $name, $instance_id) {
		$sql = "UPDATE layouts_contents SET display_preferences = 0 WHERE owner_type = $1 AND owner_id = $2 AND layout_id = $3 AND name = $4 AND content_id = $5";
		db_query_params($sql,array($owner_type,$owner_id,$layout_id,$name,$instance_id));
		echo db_error();
	}

	/**
	 * reorderLayout
	 *
	 * @param	int	$owner_id
	 * @param	string	$owner_type
	 * @param	int	$layout_id
	 * @param	object	$request
	 */
	function reorderLayout($owner_id, $owner_type, $layout_id, &$request) {
		$keys = array_keys($_REQUEST);
		foreach($keys as $key) {
			if (preg_match('`widgetlayout_col_\d+`', $key)) {

				$split = explode('_', $key);
				$column_id = (int)$split[count($split)-1];

				$names = array();
				foreach($request->get($key) as $name) {
					list($name, $id) = explode('-', $name);
					$names[] = array($id, $name);
				}

				//Compute differences
				$originals = array();
				$sql = "SELECT * FROM layouts_contents WHERE owner_type = $1 AND owner_id = $2 AND column_id = $3 ORDER BY rank";
				$res = db_query_params($sql,array($owner_type, $owner_id, $column_id));
				echo db_error();
				while($data = db_fetch_array($res)) {
					$originals[] = array($data['content_id'], $data['name']);
				}

				//delete removed contents
				$deleted_names = $this->_array_diff_names($originals, $names);
				if (count($deleted_names)) {
					$_and = '';
					foreach($deleted_names as $id => $name) {
						if ($_and) {
							$_and .= ' OR ';
						} else {
							$_and .= ' AND (';
						}
						$_and .= " (name = '".$name[1]."' AND content_id = ". $name[0] .") ";
					}
					$_and .= ')';
					$sql = "UPDATE layouts_contents
						SET column_id = 0
						WHERE owner_type = $1
						AND owner_id = $2
						AND column_id = $3". $_and;
					$res = db_query_params($sql,array($owner_type, $owner_id, $column_id));
					echo db_error();
				}

				//Insert new contents
				$added_names = $this->_array_diff_names($names, $originals);
				if (count($added_names)) {
					$_and = '';
					foreach($added_names as $name) {
						if ($_and) {
							$_and .= ' OR ';
						} else {
							$_and .= ' AND (';
						}
						$_and .= " (name = '".$name[1]."' AND content_id = ". $name[0] .") ";
					}
					$_and .= ')';
					//old and new column must be part of the same layout
					$sql = 'UPDATE layouts_contents
						SET column_id = $1
						WHERE owner_type = $2
						AND owner_id = $3' . $_and ."
						AND layout_id = $4";
					$res = db_query_params($sql,array($column_id,$owner_type,$owner_id,$layout_id));
					echo db_error();
				}

				//Update ranks
				$rank = 0;
				$values = array();
				foreach($names as $name) {
					$sql = 'UPDATE layouts_contents SET rank = $1 WHERE owner_type =$2 AND owner_id = $3 AND column_id = $4 AND name = $5 AND content_id = $6';
					db_query_params($sql, array($rank++,$owner_type,$owner_id,$column_id,$name[1],$name[0]));
					echo db_error();
				}
			}
		}
	}

	/**
	 * compute the differences between two arrays
	 * @param array $tab1
	 * @param array $tab2
	 * @return array
	 */
	function _array_diff_names($tab1, $tab2) {
		$diff = array();
		foreach($tab1 as $e1) {
			$found = false;
			reset($tab2);
			while(!$found && list(,$e2) = each($tab2)) {
				$found = !count(array_diff($e1, $e2));
			}
			if (!$found) {
				$diff[] = $e1;
			}
		}
		return $diff;
	}
}
