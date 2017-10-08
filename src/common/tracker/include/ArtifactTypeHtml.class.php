<?php
/**
 * FusionForge Generic Tracker facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems - Sourceforge
 * Copyright 2010 (c) Fusionforge Team
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014,2016-2017, Franck Villaume - TrivialDev
 * Copyright 2016-2017, Stéphane-Eymeric Bredthauer - TrivialDev
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

require_once $gfcommon.'tracker/ArtifactType.class.php';
require_once $gfcommon.'tracker/ArtifactExtraField.class.php';
require_once $gfcommon.'tracker/ArtifactExtraFieldElement.class.php';
require_once $gfcommon.'tracker/ArtifactWorkflow.class.php';
require_once $gfcommon.'tracker/EffortUnitFactory.class.php';
require_once $gfcommon.'include/utils_crossref.php';
require_once $gfcommon.'include/UserManager.class.php';
require_once $gfcommon.'widget/WidgetLayoutManager.class.php';

class ArtifactTypeHtml extends ArtifactType {

	function header($params) {
		global $HTML;
		if (!forge_get_config('use_tracker')) {
			exit_disabled();
		}
		global $group_id;
		$group_id = $this->Group->getID();

		//required by new site_project_header
		$params['group']   = $group_id;
		$params['toptab']  = 'tracker';
		$params['tabtext'] = $this->getName();

		$labels = array();
		$links  = array();
		$attr   = array();

		$labels[] = _("View Trackers");
		$links[]  = '/tracker/?group_id='.$group_id;
		$attr[]   = array('title' => _('Get the list of available trackers'));
		$labels[] = $this->getName();
		$links[]  = '/tracker/?func=browse&group_id='.$group_id.'&atid='. $this->getID();
		$attr[]   = array('title' => _('Browse this tracker.'));
		$labels[] = _('Export CSV');
		$links[]  = '/tracker/?func=csv&group_id='.$group_id.'&atid='. $this->getID();
		$attr[]   = array('title' => _('Download data from this tracker as csv file.'));
		if (forge_check_perm('tracker', $this->getID(),'submit')) {
			$labels[] = _('Submit New');
			$links[]  = '/tracker/?func=add&group_id='.$group_id.'&atid='. $this->getID();
			$attr[]   = array('title' => _('Add a new issue.'));
		}

		if (session_loggedin()) {
			$labels[] = _('Reporting');
			$links[]  = '/tracker/reporting/?group_id='.$group_id.'&atid='. $this->getID();
			$attr[]   = array('title' => _('Various graph about statistics.'));
			if ($this->isMonitoring()) {
				$labels[] = _('Stop Monitor');
				$links[]  = '/tracker/?group_id='.$group_id.'&atid='. $this->getID().'&func=monitor&stopmonitor=1';
				$attr[]   = array('title' => _('Remove this tracker from your monitoring.'));
			} else {
				$labels[] = _('Monitor');
				$links[]  = '/tracker/?group_id='.$group_id.'&atid='. $this->getID().'&func=monitor&startmonitor=1';
				$attr[]   = array('title' => _('Add this tracker from your monitoring.'));
			}

			if (forge_check_perm('tracker_admin', $group_id)) {
				$labels[] = _('Administration');
				$links[]  = '/tracker/admin/?group_id='.$group_id.'&atid='.$this->getID();
				$attr[]   = array('title' => _('Global administration for trackers. Create, clone, workflow, fields ...'));

				if (forge_get_config('use_tracker_widget_display')) {
					$sql = "SELECT l.* FROM layouts AS l INNER JOIN owner_layouts AS o ON(l.id = o.layout_id)
							WHERE o.owner_type = $1
							AND o.owner_id = $2
							AND o.is_default = 1";
					$res = db_query_params($sql,array('t', $this->getID()));
					if($res && db_numrows($res) < 1) {
						$lm = new WidgetLayoutManager();
						$lm->createDefaultLayoutForTracker($this->getID());
						$res = db_query_params($sql,array('t', $this->getID()));
					}
					$id = db_result($res, 0 , 'id');
					$url = '/widgets/widgets.php?owner=t'.$this->getID().'&layout_id='.$id;
					$labels[] = _('Add widgets');
					$labels[] = _('Customize Layout');
					$attr[] = array('title' => _('Customfields must be linked to a widget to be displayed. Use “Add widgets” to create new widget to link and organize your customfields.'));
					$attr[] = array('title' => _('General layout to display “Submit New” form or detailed view of an existing artifact can be customize. Use “Customize Layout” to that purpose.'));
					$links[] = $url;
					$links[] = $url.'&update=layout';
				}
			}
		}

		$params['submenu'] = $HTML->subMenu($labels, $links, $attr);
		site_project_header($params);

		if ($this) {
			plugin_hook('blocks', 'tracker_'.$this->getName());
		}
	}

	function footer($params = array()) {
		site_project_footer($params);
	}

	function adminHeader($params) {
		global $HTML;
		$this->header($params);
		$group_id= $this->Group->getID();
		$elementLi[] = array('content' => util_make_link('/tracker/admin/?group_id='.$group_id, _('New Tracker'), array('title'=>_('Create a new tracker.'))));
		$elementLi[] = array('content' => util_make_link('/tracker/admin/?group_id='.$group_id.'&atid='.$this->getID().'&update_type=1', _('Update Settings'), array('title'=>_('Set up preferences like expiration times, email addresses.'))));
		$elementLi[] = array('content' => util_make_link('/tracker/admin/?group_id='.$group_id.'&atid='.$this->getID().'&effort_units=1', _('Manage Effort Units'), array('title'=>_('Manage Effort Units for Effort custom extra field.'))));
		$elementLi[] = array('content' => util_make_link('/tracker/admin/?group_id='.$group_id.'&atid='.$this->getID().'&add_extrafield=1', _('Manage Custom Fields'), array('title'=>_('Add new boxes like Phases, Quality Metrics, Components, etc.  Once added they can be used with other selection boxes (for example, Categories or Groups) to describe and browse bugs or other artifact types.'))));
		$elementLi[] = array('content' => util_make_link('/tracker/admin/?group_id='.$group_id.'&atid='.$this->getID().'&workflow=1', _('Manage Workflow'), array('title'=>_('Edit tracker workflow.'))));
		$elementLi[] = array('content' => util_make_link('/tracker/admin/?group_id='.$group_id.'&atid='.$this->getID().'&customize_list=1', _('Customize List'), array('title'=>_('Customize display for the tracker.'))));
		$elementLi[] = array('content' => util_make_link('/tracker/admin/?group_id='.$group_id.'&atid='.$this->getID().'&add_canned=1', _('Manage Canned Responses'), array('title'=>_('Create/change generic response messages for the tracker.'))));
		$elementLi[] = array('content' => util_make_link('/tracker/admin/?group_id='.$group_id.'&atid='.$this->getID().'&clone_tracker=1', _('Apply Template Tracker'), array('title'=>_('Duplicate parameters and fields from a template trackers in this one.'))));
		$elementLi[] = array('content' => util_make_link('/tracker/admin/?group_id='.$group_id.'&atid='.$this->getID().'&delete=1', _('Delete'), array('title'=>_('Permanently delete this tracker.'))));
		echo $HTML->html_list($elementLi);
	}

	function adminFooter($params) {
		$this->footer($params);
	}

	function renderSubmitInstructions() {
		$msg = $this->getSubmitInstructions();
		return str_replace("\n","<br />", $msg);
	}

	function renderBrowseInstructions() {
		$msg = $this->getBrowseInstructions();
		return str_replace("\n","<br />", $msg);
	}

	/**
	 * renderExtraFields - ???
	 *
	 * @param	array	$selected
	 * @param	bool	$show_100		Display the specific '100' value. Default is false.
	 * @param	string	$text_100		Label displayed for the '100' value. Default is 'none'
	 * @param	bool	$show_any
	 * @param	string	$text_any
	 * @param	array	$types
	 * @param	bool	$status_show_100	Force display of the '100' value if needed. Default is false.
	 * @param	string	$mode			QUERY, DISPLAY, UPDATE, NEW
	 */
	function renderExtraFields($selected = array(),
                               $show_100 = false, $text_100 = 'none',
                               $show_any = false, $text_any = 'Any',
                               $types = array(),
                               $status_show_100 = false,
                               $mode = '') {
		global $HTML;
		global $ah;

		if ($mode == 'NEW') {
			$efarr = $this->getExtraFields($types, false, false);
		} else {
			$efarr = $this->getExtraFields($types);
		}
		//each two columns, we'll reset this and start a new row
		$template = $this->getRenderHTML($types, $mode);

		if ($mode=='QUERY') {
			$keys=array_keys($efarr);
			for ($k=0; $k<count($keys); $k++) {
				$i=$keys[$k];
				$type = $efarr[$i]['field_type'];
				if ($type == ARTIFACT_EXTRAFIELDTYPE_SELECT ||
						$type == ARTIFACT_EXTRAFIELDTYPE_CHECKBOX ||
						$type == ARTIFACT_EXTRAFIELDTYPE_RADIO ||
						$type == ARTIFACT_EXTRAFIELDTYPE_STATUS ||
						$type == ARTIFACT_EXTRAFIELDTYPE_MULTISELECT) {
					$efarr[$i]['field_type'] = ARTIFACT_EXTRAFIELDTYPE_MULTISELECT;
				} elseif ($type == ARTIFACT_EXTRAFIELDTYPE_USER ||
						$type == ARTIFACT_EXTRAFIELDTYPE_MULTIUSER) {
					$efarr[$i]['field_type'] = ARTIFACT_EXTRAFIELDTYPE_MULTIUSER;
				} elseif ($type == ARTIFACT_EXTRAFIELDTYPE_RELEASE ||
						$type == ARTIFACT_EXTRAFIELDTYPE_MULTIRELEASE) {
					$efarr[$i]['field_type'] = ARTIFACT_EXTRAFIELDTYPE_MULTIRELEASE;
				} elseif ($type == ARTIFACT_EXTRAFIELDTYPE_DATETIME ||
						$type == ARTIFACT_EXTRAFIELDTYPE_DATE) {
					$efarr[$i]['field_type'] = ARTIFACT_EXTRAFIELDTYPE_DATERANGE;
				} elseif ($type == ARTIFACT_EXTRAFIELDTYPE_EFFORT) {
					$efarr[$i]['field_type'] = ARTIFACT_EXTRAFIELDTYPE_EFFORTRANGE;
				} else {
					$efarr[$i]['field_type'] = ARTIFACT_EXTRAFIELDTYPE_TEXT;
				}
			}
		}

		// 'DISPLAY' mode is for rendering in 'read-only' mode (for detail view).
		if ($mode === 'DISPLAY') {
			$keys=array_keys($efarr);
			for ($k=0; $k<count($keys); $k++) {
				$post_name = '';
				$i=$keys[$k];

				if (!isset($selected[$efarr[$i]['extra_field_id']]))
					$selected[$efarr[$i]['extra_field_id']] = '';

				$value = $selected[$efarr[$i]['extra_field_id']];
				$type = $efarr[$i]['field_type'];

				if ($type == ARTIFACT_EXTRAFIELDTYPE_SELECT ||
					$type == ARTIFACT_EXTRAFIELDTYPE_CHECKBOX ||
					$type == ARTIFACT_EXTRAFIELDTYPE_RADIO ||
					$type == ARTIFACT_EXTRAFIELDTYPE_STATUS ||
					$type == ARTIFACT_EXTRAFIELDTYPE_MULTISELECT) {
					if ($value == 100) {
						$value = $efarr[$i]['show100label'];
					} else {
						$arr = $this->getExtraFieldElements($efarr[$i]['extra_field_id']);

						// Convert the values (ids) to names in the ids order.
						$new = array();
						for ($j=0; $j<count($arr); $j++) {
							if (is_array($value) && in_array($arr[$j]['element_id'], $value)) {
								$new[]= $arr[$j]['element_name'];
							} elseif ($arr[$j]['element_id'] === $value) {
								$new[] = $arr[$j]['element_name'];
							}
						}
						$value = join('<br />', $new);
					}
				} elseif ($type == ARTIFACT_EXTRAFIELDTYPE_TEXT ||
						$type == ARTIFACT_EXTRAFIELDTYPE_TEXTAREA) {
					$value = preg_replace('/((http|https|ftp):\/\/\S+)/',
								"<a href=\"\\1\" target=\"_blank\">\\1</a>", $value);
				} elseif ($type == ARTIFACT_EXTRAFIELDTYPE_RELATION || $type == ARTIFACT_EXTRAFIELDTYPE_PARENT) {
					// Convert artifact id to links.
					$value = preg_replace_callback('/\b(\d+)\b/', create_function('$matches', 'return _artifactid2url($matches[1], \'title\');'), $value);
				} elseif ($type == ARTIFACT_EXTRAFIELDTYPE_DATETIME && $value!='') {
					$value =  date('Y-m-d H:i', $value);
				} elseif ($type == ARTIFACT_EXTRAFIELDTYPE_EFFORT) {
					if (!isset($effortUnitSet)) {
						$effortUnitSet = New EffortUnitSet($this, $this->getEffortUnitSet());
						$effortUnitFactory = New EffortUnitFactory($effortUnitSet);
					}
					$value = $effortUnitFactory->encodedToString($value);
				}
				$template = str_replace('{$PostName:'.$efarr[$i]['field_name'].'}', $post_name, $template);
				$template = str_replace('{$'.$efarr[$i]['field_name'].'}', $value, $template);
			}
			echo $template;
			return ;
		}
		if ($mode == 'UPDATE' || $mode == 'NEW') {
			if ($mode == 'NEW') {
				$efInFormula = $this->getExtraFieldsInFormula($types, false, false);
				$efWithFormula = $this->getExtraFieldsWithFormula($types, false, false);
			} else {
				$efInFormula = $this->getExtraFieldsInFormula($types);
				$efWithFormula = $this->getExtraFieldsWithFormula($types);
			}
		}

		$keys = array_keys($efarr);
		for ($k = 0; $k < count($keys); $k++) {
			$i = $keys[$k];
			$post_name = '';

			$attrs = array();
			if (!empty($efarr[$i]['description'])) {
				$attrs['title'] = $efarr[$i]['description'];
			}
			if ($efarr[$i]['is_required'] == 1 && $mode != 'QUERY') {
				$attrs['required'] = 'required';
			}

			if ($mode == 'UPDATE' || $mode == 'NEW') {
				if (in_array($efarr[$i]['extra_field_id'], $efInFormula)) {
					$attrs['class'] = (empty($attrs['class']) ? '':$attrs['class'].' ').'in-formula';
				}
				if (in_array($efarr[$i]['extra_field_id'], $efWithFormula)) {
					$attrs['class'] = (empty($attrs['class']) ? '':$attrs['class'].' ').'with-formula readonly';
					$attrs['readonly'] = 'readonly';
				}
			}

			if (!isset($selected[$efarr[$i]['extra_field_id']]))
				$selected[$efarr[$i]['extra_field_id']] = '';

			if ($status_show_100) {
				$efarr[$i]['show100'] = $status_show_100;
			}

			$allowed=false;

			if ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_SELECT ||
					$efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_CHECKBOX ||
					$efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_RADIO ||
					$efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_MULTISELECT) {
				if (!is_null($efarr[$i]['parent']) && !empty($efarr[$i]['parent']) && $efarr[$i]['parent']!='100') {
					$aefParentId = $efarr[$i]['parent'];
					$selectedElmnts = (isset($selected[$aefParentId]) ? $selected[$aefParentId] : '');
					$aef = new ArtifactExtraField($this, $efarr[$i]['extra_field_id']);
					$allowed = $aef->getAllowedValues($selectedElmnts);
				}
			}

			if ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_SELECT) {
				$str = $this->renderSelect($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $efarr[$i]['show100'], $efarr[$i]['show100label'], $show_any, $text_any, $allowed, $attrs);

			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_CHECKBOX) {

				$str = $this->renderCheckbox($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $efarr[$i]['show100'], $efarr[$i]['show100label'], $allowed, $attrs);

			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_RADIO) {

				$str = $this->renderRadio($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $efarr[$i]['show100'], $efarr[$i]['show100label'], $show_any, $text_any, $allowed, $attrs);

			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_TEXT ||
					$efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_INTEGER) {

				if (!empty($efarr[$i]['pattern'])) {
					$attrs['pattern'] = $efarr[$i]['pattern'];
				}
				if ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_TEXT) {
					$str = $this->renderTextField($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $efarr[$i]['attribute1'], $efarr[$i]['attribute2'], $attrs);
				} else {
					$str = $this->renderIntegerField($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $efarr[$i]['attribute1'], $efarr[$i]['attribute2'], $attrs);
				}
				if ($mode == 'QUERY') {
					$post_name =  ' <i>'._('(%% for wildcards)').'</i>&nbsp;&nbsp;&nbsp;';
				}

			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_TEXTAREA) {

				$str = $this->renderTextArea($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $efarr[$i]['attribute1'], $efarr[$i]['attribute2'], $attrs);
				if ($mode == 'QUERY') {
					$post_name =  ' <i>'._('(%% for wildcards)').'</i>';
				}

			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_MULTISELECT) {

				$str = $this->renderMultiSelectBox($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $efarr[$i]['show100'], $efarr[$i]['show100label'], $allowed, $attrs);

			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
				// parent artifact can't be close if a child is still open
				if ($mode == 'UPDATE' &&
						$efarr[$i]['aggregation_rule'] == ARTIFACT_EXTRAFIELD_AGGREGATION_RULE_STATUS_CLOSE_RESTRICTED &&
						$ah->hasChildren()) {
					$children = $ah->getChildren();
					$childOpen = false;
					foreach ($children as $child) {
						if ($child['status_id'] == 1) {
							$childOpen = true;
							break;
						}
					}
					if ($childOpen) {
						$aef = new ArtifactExtraField($this, $efarr[$i]['extra_field_id']);
						$statusArr = $aef->getAvailableValues();
						$openStatus = array();
						foreach ($statusArr as $status) {
							if ($status['status_id']==1) {
								$openStatus[] = $status['element_id'];
							}
						}
						if ($allowed) {
							$allowed = array_intersect($allowed, $openStatus);
						} else {
							$allowed = $openStatus;
						}
					}
				}

				// Get the allowed values from the workflow.
				$atw = new ArtifactWorkflow($this, $efarr[$i]['extra_field_id']);

				// Special treatment for the initial step (Submit).
				// In this case, the initial value is the first value.
				if (isset($selected[$efarr[$i]['extra_field_id']]) && $selected[$efarr[$i]['extra_field_id']]) {
					$selected_node = $selected[$efarr[$i]['extra_field_id']];
				} else {
					$selected_node = 100;

				}

				$allowedWF = $atw->getNextNodes($selected_node);
				if ($allowed) {
					$allowed = array_intersect($allowed, $allowedWF);
				} else {
					$allowed = $allowedWF;
				}
				$allowed[] = $selected_node;
				$str = $this->renderSelect($efarr[$i]['extra_field_id'], $selected_node, $status_show_100, $text_100, $show_any, $text_any, $allowed, $attrs);

			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_RELATION) {
				$str = $this->renderRelationField($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $efarr[$i]['attribute1'], $efarr[$i]['attribute2'], $attrs);
				if ($mode == 'UPDATE' || $mode == 'NEW') {
					$post_name = $HTML->getEditFieldPic(_('Click to edit'), $alt = _('Click to edit'), array('onclick'=>"switch2edit(this, 'show$i', 'edit$i')"));
				}
			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_PARENT) {
				$str = $this->renderParentField($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $efarr[$i]['attribute1'], $efarr[$i]['attribute2'], $attrs);
				if ($mode == 'UPDATE' || $mode == 'NEW') {
					$post_name = $HTML->getEditFieldPic(_('Click to edit'), $alt = _('Click to edit'), array('onclick'=>"switch2edit(this, 'show$i', 'edit$i')"));
				}
			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_DATETIME) {
				$str = $this->renderDatetime($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $attrs);
			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_USER) {
				$str = $this->renderUserField($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $efarr[$i]['show100'], $efarr[$i]['show100label'], $show_any, $text_any,false, $attrs);
			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_MULTIUSER) {
				$str = $this->renderMultiUserField($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $efarr[$i]['show100'], $efarr[$i]['show100label'], $show_any, $text_any,false, $attrs);
			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_RELEASE) {
				$str = $this->renderReleaseField($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $efarr[$i]['show100'], $efarr[$i]['show100label'], $show_any, $text_any,false, $attrs);
			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_MULTIRELEASE) {
				$str = $this->renderMultiReleaseField($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $efarr[$i]['show100'], $efarr[$i]['show100label'], $show_any, $text_any,false, $attrs);
			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_DATERANGE) {
				if ($mode == 'QUERY') {
					$post_name =  ' <i>'._('(YYYY-MM-DD YYYY-MM-DD Format)').'</i>';
				}
				$str = $this->renderDateRange($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $attrs);
			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_EFFORT) {
				$str = $this->renderEffort($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $efarr[$i]['attribute1'], $efarr[$i]['attribute2'], $attrs);
			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_EFFORTRANGE) {
				$str = $this->renderEffortRange($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $efarr[$i]['attribute1'], $efarr[$i]['attribute2'], $attrs);
			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_SLA) {
				$str = $this->renderSLAField($efarr[$i]['extra_field_id'], $selected[$efarr[$i]['extra_field_id']], $attrs);
			}
			$template = str_replace('{$PostName:'.$efarr[$i]['field_name'].'}', $post_name, $template);
			$template = str_replace('{$'.$efarr[$i]['field_name'].'}', $str, $template);
		}
		if($template != NULL){
			if ($mode == 'UPDATE' || $mode == 'NEW' || $mode == 'QUERY') {
				echo $this->javascript();
			}
			echo $template;
		}
	}

	function renderRelatedTasks($group, $ah, $formid = null) {
		global $HTML;
		if (!$group->usesPM()) {
			return;
		}

		$return = '';

		$tasks = $ah->getRelatedTasks();
		$taskcount = db_numrows($tasks);

		if (forge_check_perm('tracker_admin', $ah->ArtifactType->Group->getID())) {
			$is_admin=true;
		} else {
			$is_admin=false;
		}
		$totalPercentage = 0;
		if ($taskcount > 0) {
			$title_arr = array();
			$title_arr[] = _('Task Id and Summary');
			$title_arr[] = _('Progress');
			$title_arr[] = _('Start Date');
			$title_arr[] = _('End Date');
			$title_arr[] = _('Status');
			(($is_admin) ? $title_arr[]=_('Remove Relation') : '');
			$return .= $HTML->listTableTop($title_arr);

			while ($taskinfo = db_fetch_array($tasks)) {
				$totalPercentage += $taskinfo['percent_complete'];
				$taskid    = $taskinfo['project_task_id'];
				$projectid = $taskinfo['group_project_id'];
				$groupid   = $taskinfo['group_id'];
				if (forge_check_perm('pm', $projectid, 'read')) {
					$summary   = util_unconvert_htmlspecialchars($taskinfo['summary']);
					$startdate = date(_('Y-m-d H:i'), $taskinfo['start_date']);
					$enddate   = date(_('Y-m-d H:i'), $taskinfo['end_date']);
					$status    = $taskinfo['status_name'];
					$return   .=  '<tr>
							<td>'.util_make_link('/pm/t_follow.php/'.$taskid, '[T'.$taskid.'] '.$summary).'</td>
							<td><div class="percentbar" style="width: 100px;">
								<div style="width:'.round($taskinfo['percent_complete']).'px;"></div></div></td>
							<td>'.$startdate.'</td>
							<td>'.$enddate.'</td>
							<td>'.$status.' ('.$taskinfo['percent_complete'].'%)</td>'.
						(($is_admin) ? '<td><input type="checkbox" '.(($formid) ? 'form="'.$formid.'"' : '').' name="remlink[]" value="'.$taskid.'" /></td>' : '').
						'</tr>';
				}
			}
			$return .=  $HTML->listTableBottom();

			$return .=  "\n<hr /><p style=\"text-align:right;\">";
			$return .=  _('Average completion rate')._(': ').(int)($totalPercentage/$taskcount).'%';
			$return .=  "</p>\n";
		} else {
			$return .=  $HTML->information(_('No related tasks'));
		}
		return $return;
	}

	function renderFiles($group_id, $ah) {
		global $HTML;
		$file_list =& $ah->getFiles();
		$count=count($file_list);
		$return = '';
		if ($count > 0) {

			$return .= '<strong>'._("Attachments")._(':').'</strong>'.'<br/>';
			$title_arr=array();
			$title_arr[] = _('Size');
			$title_arr[] = _('Name');
			$title_arr[] = _('Date');
			$title_arr[] = _('By');
			$title_arr[] = _('Download');
			if (forge_check_perm('tracker', $this->getID(), 'tech')) {
				$title_arr[] = '';
			}
			$return .= $HTML->listTableTop($title_arr);

			foreach ($file_list as $file) {
				$return .= '<tr>';
				$return .= '<td>'.human_readable_bytes($file->getSize()).'</td>';
				$return .= '<td>'.htmlspecialchars($file->getName()).'</td>';
				$return .= '<td>'.date(_('Y-m-d H:i'), $file->getDate()).'</td>';
				$return .= '<td>'.util_display_user($file->getSubmittedUnixName(), $file->getSubmittedBy(), $file->getSubmittedRealName()).'</td>';
				$return .= '<td>'.util_make_link('/tracker/download.php/'.$group_id.'/'. $this->getID().'/'. $ah->getID() .'/'.$file->getID().'/'.$file->getName(), htmlspecialchars($file->getName())).'</td>';
				if (forge_check_perm('tracker', $this->getID(), 'tech')) {
					$return .= '<td><input type="checkbox" name="delete_file[]" value="'. $file->getID() .'">'._('Delete').'</td>';
				}
				$return .= '</tr>';
			}

			$return .= $HTML->listTableBottom();
		} else {
			$return .= $HTML->information(_('No attached documents'));
		}
		return $return;
	}

	/**
	 * getRenderHTML
	 *
	 * @param	array	$types
	 * @param	string	$mode
	 * @return	string	HTML template.
	 */
	function getRenderHTML($types=array(), $mode='') {
		// Use template only for the browse (not for query or mass update)
		if (($mode === 'DISPLAY' || $mode === 'DETAIL' || $mode === 'UPDATE' || $mode == 'NEW')
			&& $this->data_array['custom_renderer']) {
			return preg_replace('/<!--(\S+.*?)-->/','{$\\1}', $this->data_array['custom_renderer']);
		} else {
			return $this->generateRenderHTML($types, $mode);
		}
	}

	/**
	 * generateRenderHTML
	 *
	 * @param	array	$types
	 * @param	string	$mode	Display mode (QUERY OR DISPLAY OR NEW)
	 * @return	string	HTML template.
	 */
	function generateRenderHTML($types=array(), $mode) {
		if ($mode == 'NEW') {
			$efarr = $this->getExtraFields($types, false, false);
		} else {
			$efarr = $this->getExtraFields($types);
		}
		//each two columns, we'll reset this and start a new row

		$return = '
			<!-- Start Extra Fields Rendering -->
			<tr>';
		$col_count=0;

		$keys=array_keys($efarr);
		$count=count($keys);
		if ($count == 0) return '';

		for ($k=0; $k<$count; $k++) {
			$i=$keys[$k];

			// Do not show the required star in query mode (creating/updating a query).
			$is_required = ($mode == 'QUERY' || $mode == 'DISPLAY') ?	0 : $efarr[$i]['is_required'];
			if ($mode == 'QUERY' && ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_DATETIME || $efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_DATE)) {
				$name = sprintf(_('%s range'), $efarr[$i]['field_name']).($is_required ? utils_requiredField() : '')._(': ');
			} else {
				$name = $efarr[$i]['field_name'].($is_required ? utils_requiredField() : '')._(': ');
			}
			$name = '<strong>'.$name.'</strong>';

			if ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_SELECT ||
				$efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_CHECKBOX ||
				$efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_RADIO ||
				$efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_MULTISELECT ||
				$efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_STATUS ||
				$efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_USER ||
				$efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_RELEASE	) {

				$return .= '
					<td class="halfwidth top">'.$name.'<br />{$'.$efarr[$i]['field_name'].'}</td>';

			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_TEXT ||
				$efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_INTEGER ||
				$efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_RELATION ||
				$efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_PARENT ||
				$efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_DATETIME ||
					$efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_EFFORT) {

				//text fields might be really wide, so need a row to themselves.
				if (($col_count == 1) && ($efarr[$i]['attribute1'] > 30)) {
					$colspan=2;
					$return .= '
					<td></td>
			</tr>
			<tr>';
				} else {
					$colspan=1;
				}
				$return .= '
					<td style="width:'.(50*$colspan).'%" colspan="'.$colspan.'" class="top">'.$name.'{$PostName:'.$efarr[$i]['field_name'].'}<br />{$'.$efarr[$i]['field_name'].'}</td>';

			} elseif ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_TEXTAREA) {

				//text areas might be really wide, so need a row to themselves.
				if (($col_count == 1) && ($efarr[$i]['attribute2'] > 30)) {
					$colspan=2;
					$return .= '
					<td></td>
			</tr>
			<tr>';
				} else {
					$colspan=1;
				}
				$return .= '
					<td style="width:'.(50*$colspan).'%" colspan="'.$colspan.'" class="top">'.$name.'{$PostName:'.$efarr[$i]['field_name'].'}<br />{$'.$efarr[$i]['field_name'].'}</td>';

			}

			$col_count++;
			//we've done two columns - if there are more to do, start a new row
			if (($col_count == 2) && ($k != $count-1)) {
				$col_count = 0;
				$return .= '
			</tr>
			<tr>';
			}
		}
		if ($col_count == 1) {
			$return .= '
					<td></td>';
		}
		$return .= '
			</tr>
			<!-- End Extra Fields Rendering -->';
		return $return;
	}

	/**
	 * renderSelect - this function builds pop up box with choices.
	 *
	 * @param	int		$extra_field_id	The ID of this field.
	 * @param	string		$checked	The item that should be checked
	 * @param	bool|string	$show_100	Whether to show the '100 row'
	 * @param	string		$text_100	What to call the '100 row'
	 * @param	bool		$show_any
	 * @param	string		$text_any
	 * @param	bool		$allowed
	 * @param	array		$attrs
	 * @return	string		HTML code for the box and choices
	 */
	function renderSelect($extra_field_id, $checked='xzxz', $show_100=false, $text_100='none', $show_any=false, $text_any='Any', $allowed=false, $attrs = array()) {
		if ($text_100 == 'none'){
			$text_100=_('None');
		}
		$arr = $this->getExtraFieldElements($extra_field_id);
		$aef = new ArtifactExtraField($this, $extra_field_id);
		$aefChildren = $aef->getChildren();
		if (!empty($aefChildren)) {
			$attrs['class'] = (empty($attrs['class']) ? '':$attrs['class'].' ').'with-depcy';
		}
		if ($aef->isAutoAssign())  {
			$attrs['class'] = (empty($attrs['class']) ? '':$attrs['class'].' ').'autoassign';
		}
		$vals = array();
		$texts = array();
		$opt_attrs = array();
		$attrs_100 = array();

		for ($i=0; $i<count($arr); $i++) {
			$vals[$i]=$arr[$i]['element_id'];
			$texts[$i]=$arr[$i]['element_name'];
			$opt_attrs[$i]=array();
			$aefe = new ArtifactExtraFieldElement($aef, $arr[$i]['element_id']);
			if (!empty($aefChildren)) {
				$cElmntArr = $aefe->getChildrenElements();
				if (!empty($cElmntArr))
				{
					$dependency = '';
					foreach ($cElmntArr as $key=>$cElmnt) {
						$childField = new ArtifactExtraField($this, $key);
						$dependency .= (empty($dependency) ? '':', ').'{"field":'.$key.', "elmnt": ['.implode(', ', $cElmnt).']}';
					}
					$dependency = '['.$dependency.']';
					$opt_attrs[$i]= array('data-dependency'=>$dependency);
				}
			}
			if ($aef->isAutoAssign()) {
				$autoAssignTo = UserManager::instance()->getUserById($aefe->getAutoAssignto());
				$opt_attrs[$i]=array_merge(isset($opt_attrs[$i]) ? $opt_attrs[$i] : array(), array('data-autoassignto'=>'{"id":'.$aefe->getAutoAssignto().', "name":"'.$autoAssignTo->getRealName().' ('.$autoAssignTo->getUnixName().')"}'));
			}
		}

		if ($show_100 && !empty($aefChildren)) {
			$attrs_100 = array('data-dependency'=>'{"fields": ['.implode(', ', $aefChildren).']}');
		}
		return html_build_select_box_from_arrays($vals, $texts,'extra_fields['.$extra_field_id.']', $checked, $show_100, $text_100, $show_any, $text_any, $allowed, $attrs, $opt_attrs, $attrs_100);
	}

	/**
	 * renderUserField - this function builds pop up box with users.
	 *
	 * @param	int		$extra_field_id	The ID of this field.
	 * @param	string		$checked	The item that should be checked
	 * @param	bool|string	$show_100	Whether to show the '100 row'
	 * @param	string		$text_100	What to call the '100 row'
	 * @param	bool		$show_any
	 * @param	string		$text_any
	 * @param	bool		$allowed
	 * @param	array		$attrs
	 * @return	string		HTML code for the box and choices
	 */
	function renderUserField($extra_field_id, $checked='xzxz', $show_100=false, $text_100='none', $show_any=false, $text_any='Any', $allowed=false, $attrs = array()) {
		if ($text_100 == 'none' || $text_100 == 'nobody'){
			$text_100=_('Nobody');
		}
		$arr = $this->getExtraFieldElements($extra_field_id);
		$selectedRolesId = array();
		for ($i=0; $i<count($arr); $i++) {
			$selectedRolesId[$i]=$arr[$i]['element_name'];
		}
		$roles = $this->getGroup()->getRoles();
		$userArray = array();
		foreach ($roles as $role) {
			if (in_array($role->getID(), $selectedRolesId)) {
				foreach ($role->getUsers() as $user) {
					$userArray[$user->getID()] = $user->getRealName().(($user->getStatus()=='S') ? ' '._('[SUSPENDED]') : '');
				}
			}
		}
		if (is_integer($checked) && !isset($userArray[$checked])) {
			$checkedUser = user_get_object($checked);
			$userArray[$checkedUser->getID()] = $checkedUser->getRealName().' '._('[DELETED]');
		}
		asort($userArray,SORT_FLAG_CASE | SORT_STRING);
		$keys = array_keys($userArray);
		$vals = array_values($userArray);
		return html_build_select_box_from_arrays($keys, $vals,'extra_fields['.$extra_field_id.']', $checked, $show_100, $text_100, $show_any, $text_any, $allowed, $attrs);
	}

	/**
	 * renderMultiUserField - this function builds pop up box with users.
	 *
	 * @param	int		$extra_field_id	The ID of this field.
	 * @param	string		$checked	The item that should be checked
	 * @param	bool|string	$show_100	Whether to show the '100 row'
	 * @param	string		$text_100	What to call the '100 row'
	 * @param	bool		$show_any
	 * @param	string		$text_any
	 * @param	bool		$allowed
	 * @param	array		$attrs
	 * @return	string		HTML code for the box and choices
	 */
	function renderMultiUserField($extra_field_id, $checked='xzxz', $show_100=false, $text_100='none', $show_any=false, $text_any='Any', $allowed=false, $attrs = array()) {
		if ($text_100 == 'none' || $text_100 == 'nobody'){
			$text_100=_('Nobody');
		}

		if (!$checked) {
			$checked=array();
		}
		if (!is_array($checked)) {
			$checked = explode(',', $checked);
		}

		$arr = $this->getExtraFieldElements($extra_field_id);
		$selectedRolesId = array();
		for ($i=0; $i<count($arr); $i++) {
			$selectedRolesId[$i]=$arr[$i]['element_name'];
		}
		$roles = $this->getGroup()->getRoles();
		$userArray = array();
		foreach ($roles as $role) {
			if (in_array($role->getID(), $selectedRolesId)) {
				foreach ($role->getUsers() as $user) {
					$userArray[$user->getID()] = $user->getRealName().(($user->getStatus()=='S') ? ' '._('[SUSPENDED]') : '');
				}
			}
		}
		if (is_integer($checked) && !isset($userArray[$checked])) {
			$checkedUser = user_get_object($checked);
			$userArray[$checkedUser->getID()] = $checkedUser->getRealName().' '._('[DELETED]');
		}
		asort($userArray,SORT_FLAG_CASE | SORT_STRING);
		$size = min(count($userArray)+1, 15);
		$keys = array_keys($userArray);
		$vals = array_values($userArray);

		return html_build_multiple_select_box_from_arrays($keys, $vals,'extra_fields['.$extra_field_id.'][]', $checked, $size, $show_100, $text_100, $allowed, $attrs);
	}

	/**
	 * renderReleaseField - this function builds 2 pop up boxes with packages & releases.
	 *
	 * @param	int		$extra_field_id	The ID of this field.
	 * @param	string		$checked	The item that should be checked
	 * @param	bool|string	$show_100	Whether to show the '100 row'
	 * @param	string		$text_100	What to call the '100 row'
	 * @param	bool		$show_any
	 * @param	string		$text_any
	 * @param	bool		$allowed
	 * @param	array		$attrs
	 * @return	string		HTML code for the box and choices
	 */
	function renderReleaseField($extra_field_id, $checked = 'xzxz', $show_100 = false, $text_100 = 'none', $show_any = false, $text_any = 'Any', $allowed = false, $attrs = array()) {
		if ($text_100 == 'none'){
			$text_100=_('None');
		}

		$releasesArray = array();
		$releasesAttrs =  array();
		$optGroup = array();

		$arr = $this->getExtraFieldElements($extra_field_id);
		$selectedPackagesId = array();
		for ($i=0; $i<count($arr); $i++) {
			$selectedPackagesId[$i]=$arr[$i]['element_name'];
		}

		$fpFactory = new FRSPackageFactory($this->getGroup());
		$packages = $fpFactory->getFRSs(true);
		uasort($packages, 'compareObjectName');
		foreach ($packages as $package) {
			$releases = $package->getReleases(false);
			uasort($releases, 'compareObjectName');
			foreach ($releases as $release) {
				$optGroup[] = $package->getName();
				$releasesArray[$release->getID()] = $release->getName();
			}
		}

		$keys = array_keys($releasesArray);
		$vals = array_values($releasesArray);
		return html_build_select_box_from_arrays($keys, $vals, 'extra_fields['.$extra_field_id.']', $checked, $show_100, $text_100, $show_any, $text_any, $allowed, $attrs, $releasesAttrs, array(), $optGroup);
	}

	/**
	 * renderMultiReleaseField - this function builds 2 pop up boxes with packages & releases.
	 *
	 * @param	int		$extra_field_id	The ID of this field.
	 * @param	string		$checked	The item that should be checked
	 * @param	bool|string	$show_100	Whether to show the '100 row'
	 * @param	string		$text_100	What to call the '100 row'
	 * @param	bool		$show_any
	 * @param	string		$text_any
	 * @param	bool		$allowed
	 * @param	array		$attrs
	 * @return	string		HTML code for the box and choices
	 */
	function renderMultiReleaseField($extra_field_id, $checked='xzxz', $show_100=false, $text_100='none', $show_any=false, $text_any='Any', $allowed=false, $attrs=array()) {
		if ($text_100 == 'none'){
			$text_100=_('None');
		}
		if (!$checked) {
			$checked=array();
		}
		if (!is_array($checked)) {
			$checked = explode(',', $checked);
		}

		$releasesArray = array();
		$releasesAttrs =  array();
		$optGroup = array();

		$arr = $this->getExtraFieldElements($extra_field_id);
		$selectedPackagesId = array();
		for ($i=0; $i<count($arr); $i++) {
			$selectedPackagesId[$i]=$arr[$i]['element_name'];
		}

		$packages = get_frs_packages($this->getGroup());
		uasort($packages, 'compareObjectName');
		foreach ($packages as $package) {
			if (in_array($package->getID(), $selectedPackagesId)) {
				$releases = $package->getReleases();
				uasort($releases, 'compareObjectName');
				foreach ($releases as $release) {
					$optGroup[] = $package->getName();
					$releasesArray[$release->getID()] = $release->getName();
				}
			}
		}
		$size = min(count($releasesArray) + count($optGroup) + 1, 15);
		$keys = array_keys($releasesArray);
		$vals = array_values($releasesArray);
		return html_build_multiple_select_box_from_arrays($keys, $vals,'extra_fields['.$extra_field_id.'][]', $checked, $size, $show_100, $text_100, $allowed, $attrs, $releasesAttrs, array(), $optGroup);
	}

	/**
	 * renderRadio - this function builds radio buttons.
	 *
	 * @param	int	$extra_field_id	The $int ID of this field.
	 * @param	string	$checked	The $string item that should be checked
	 * @param	bool	$show_100	Whether $string to show the '100 row'
	 * @param	string	$text_100	What $string to call the '100 row'
	 * @param	bool	$show_any
	 * @param	string	$text_any
	 * @param	bool	$allowed
	 * @param	array	$attrs		Array of other attributes
	 * @return	string	HTML code using radio buttons
	 */
	function renderRadio($extra_field_id, $checked='xzxz', $show_100=false, $text_100='none', $show_any=false, $text_any='Any', $allowed = false, $attrs = array()) {

		$arr = $this->getExtraFieldElements($extra_field_id);
		$aef = new ArtifactExtraField($this, $extra_field_id);
		$aefChildren = $aef->getChildren();
		if (!empty($aefChildren)) {
			$attrs['class'] = (empty($attrs['class']) ? '':$attrs['class'].' ').'with-depcy';
		}

		$vals = array();
		$texts = array();
		$radios_attrs = array();
		$attrs_100 = array();

		for ($i=0; $i<count($arr); $i++) {
			$vals[$i]=$arr[$i]['element_id'];
			$texts[$i]=$arr[$i]['element_name'];
			$aefe = new ArtifactExtraFieldElement($aef, $arr[$i]['element_id']);
			$dependency = '';
			if (!empty($aefChildren)) {
				$cElmntArr = $aefe->getChildrenElements();
				if (!empty($cElmntArr))
				{
					foreach ($cElmntArr as $key=>$cElmnt) {
						$childField = new ArtifactExtraField($this, $key);
						$dependency .= (empty($dependency) ? '':', ').'{"field":'.$key.', "elmnt": ['.implode(', ', $cElmnt).']}';
					}
					$dependency = '['.$dependency.']';
					$radios_attrs[$i]['data-dependency']=$dependency;
				}
			}
			if ($aef->isAutoAssign()) {
				$autoAssignTo = UserManager::instance()->getUserById($aefe->getAutoAssignto());
				$radios_attrs[$i]=array_merge(isset($radios_attrs[$i]) ? $radios_attrs[$i] : array(), array('data-autoassignto'=>'{"id":'.$aefe->getAutoAssignto().', "name":"'.$autoAssignTo->getRealName().' ('.$autoAssignTo->getUnixName().')"}'));
			}
		}
		if ($show_100 && !empty($aefChildren)) {
			$attrs_100 = array('data-dependency'=>'{"fields": ['.implode(', ', $aefChildren).']}');
		}
		return html_build_radio_buttons_from_arrays($vals, $texts,'extra_fields['.$extra_field_id.']', $checked, $show_100, $text_100, $show_any, $text_any, $allowed, $attrs, $radios_attrs, $attrs_100);
	}

	/**
	 * renderCheckbox - this function builds checkboxes.
	 *
	 * @param	int		$extra_field_id	The ID of this field.
	 * @param	array		$checked	The items that should be checked
	 * @param	bool|string	$show_100	Whether to show the '100 row'
	 * @param	string		$text_100	What to call the '100 row'
	 * @param	bool		$allowed
	 * @param	array		$attrs		Array of other attributes
	 * @return	string		radio buttons
	 */
	function renderCheckbox($extra_field_id, $checked=array(), $show_100=false, $text_100='none', $allowed=false, $attrs = array()) {
		if ($text_100 == 'none'){
			$text_100=_('None');
		}

		if (!$checked || !is_array($checked)) {
			$checked=array();
		}
		if (!empty($attrs['title'])) {
			$attrs['title'] = util_html_secure($attrs['title']);
		}

		$arr = $this->getExtraFieldElements($extra_field_id);
		$aef = new ArtifactExtraField($this, $extra_field_id);
		$aefChildren = $aef->getChildren();
		if (!empty($aefChildren)) {
			$attrs['class'] = (empty($attrs['class']) ? '':$attrs['class'].' ').'with-depcy';
		}

		$texts = array();
		$vals = array();
		$chk_attrs = array();
		$attrs_100 = array();

		for ($i=0; $i<count($arr); $i++) {
			$aefe = new ArtifactExtraFieldElement($aef, $arr[$i]['element_id']);
			$vals[$i] = $arr[$i]['element_id'];
			$texts[$i] = $arr[$i]['element_name'];
			$chk_attrs[$i] = array();
			$dependency = '';
			if (!empty($aefChildren)) {
				$cElmntArr = $aefe->getChildrenElements();
				if (!empty($cElmntArr))
				{
					foreach ($cElmntArr as $key=>$cElmnt) {
						$childField = new ArtifactExtraField($this, $key);
						$dependency .= (empty($dependency) ? '':', ').'{"field":'.$key.', "elmnt": ['.implode(', ', $cElmnt).']}';
					}
					$dependency = '['.$dependency.']';
					$chk_attrs[$i]['data-dependency']=$dependency;
				}
			}
		}
		if ($show_100 && !empty($aefChildren)) {
			$attrs_100['data-dependency'] ='{"fields": ['.implode(', ', $aefChildren).']}';
		}
		return html_build_checkboxes_from_arrays($vals, $texts,'extra_fields['.$extra_field_id.']', $checked,false, $show_100, $text_100, $allowed, $attrs, $chk_attrs, $attrs_100);
	}

	/**
	 * renderMultiSelectBox - this function builds checkboxes.
	 *
	 * @param	int		$extra_field_id	The ID of this field.
	 * @param	array		$checked	The items that should be checked
	 * @param	bool|string	$show_100	Whether to show the '100 row'
	 * @param	string		$text_100	What to call the '100 row'
	 * @param	bool		$allowed
	 * @param	array		$attrs		Array of other attributes
	 * @return	string		radio multiselectbox
	 */
	function renderMultiSelectBox($extra_field_id, $checked=array(), $show_100=false, $text_100='none', $allowed=false, $attrs=array()) {
		if (!$checked) {
			$checked=array();
		}
		if (!is_array($checked)) {
			$checked = explode(',', $checked);
		}
		$arr = $this->getExtraFieldElements($extra_field_id);
		$aef = new ArtifactExtraField($this, $extra_field_id);
		$aefChildren = $aef->getChildren();
		if (!empty($aefChildren)) {
			$attrs['class'] = (empty($attrs['class']) ? '':$attrs['class'].' ').'with-depcy';
		}
		$vals = array();
		$texts = array();
		$opt_attrs = array();
		$attrs_100 = array();

		for ($i=0; $i<count($arr); $i++) {
			$vals[$i]=$arr[$i]['element_id'];
			$texts[$i]=$arr[$i]['element_name'];
			$aefe = new ArtifactExtraFieldElement($aef, $arr[$i]['element_id']);
			if (!empty($aefChildren)) {
				$cElmntArr = $aefe->getChildrenElements();
				if (!empty($cElmntArr))
				{
					$dependency = '';
					foreach ($cElmntArr as $key=>$cElmnt) {
						$childField = new ArtifactExtraField($this, $key);
						$dependency .= (empty($dependency) ? '':', ').'{"field":'.$key.', "elmnt": ['.implode(', ', $cElmnt).']}';
					}
					$dependency = '['.$dependency.']';
					$opt_attrs[$i]= array('data-dependency'=>$dependency);
				}
			}
		}
		$size = min(count($arr)+1, 15);
		if ($show_100 && !empty($aefChildren)) {
			$attrs_100 = array('data-dependency'=>'{"fields": ['.implode(', ', $aefChildren).']}');
		}
		return html_build_multiple_select_box_from_arrays($vals, $texts,"extra_fields[$extra_field_id][]", $checked, $size, $show_100, $text_100, $allowed, $attrs, $opt_attrs, $attrs_100);
	}

	/**
	 * renderTextField - this function builds a text field.
	 *
	 * @param	int	$extra_field_id	The ID of this field.
	 * @param	string	$contents	The data for this field.
	 * @param	string	$size
	 * @param	string	$maxlength
	 * @param	array	$attrs		Array of other attributes
	 * @return	string	HTML code of corresponding input tag.
	 */
	function renderTextField($extra_field_id, $contents, $size, $maxlength, $attrs = array()) {
		return html_e('input', array_merge(array('type'=>'text', 'name'=>'extra_fields['.$extra_field_id.']', 'value'=>$contents, 'size'=>$size, 'maxlength'=>$maxlength), $attrs));
	}

	/**
	 * renderIntegerField - this function builds a text field.
	 *
	 * @param	int	$extra_field_id	The ID of this field.
	 * @param	string	$contents	The data for this field.
	 * @param	string	$size
	 * @param	string	$maxlength
	 * @param	array	$attrs		Array of other attributes
	 * @return	string	HTML code of corresponding input tag.
	 */
	function renderIntegerField($extra_field_id, $contents, $size, $maxlength, $attrs = array()) {
		$intAttrs = array('type'=>'number', 'name'=>'extra_fields['.$extra_field_id.']', 'value'=>$contents, 'size'=>$size, 'maxlength'=>$maxlength, 'min'=>0);
		$newattrs =  array_merge($intAttrs, $attrs);
		return html_e('input', $newattrs);
	}

	/**
	 * renderRelationField - this function builds a relation field.
	 *
	 * @param	int	$extra_field_id	The ID of this field.
	 * @param	string	$contents	The data for this field.
	 * @param	string	$size
	 * @param	string	$maxlength
	 * @param	array	$attrs		Array of other attributes
	 * @return	string	text area and data.
	 */
	function renderRelationField($extra_field_id, $contents, $size, $maxlength, $attrs = array()) {
		$arr = $this->getExtraFieldElements($extra_field_id);
		for ($i=0; $i<count($arr); $i++) {
			$keys[$i]=$arr[$i]['element_id'];
			$vals[$i]=$arr[$i]['element_name'];
		}
		// Convert artifact id to links.
		$attrsTxt = array();
		if (isset($attrs['form'])) {
			$attrsTxt['form'] = $attrs['form'];
		}
		$html_contents = preg_replace_callback('/\b(\d+)\b/', create_function('$matches', 'return _artifactid2url($matches[1], \'title\');'), $contents);
		$edit_contents = $this->renderTextField ($extra_field_id, $contents, $size, $maxlength, $attrsTxt);
		return html_e('div',array_merge(array('id'=>'edit'.$extra_field_id, 'style'=>'display: none', 'title'=>_('Tip: Enter a space-separated list of artifact ids ([#NNN] also accepted)')), $attrs), $edit_contents)
			.html_e('div',array_merge(array('id'=>'show'.$extra_field_id, 'style'=>'display: block'), $attrs), $html_contents);
	}

	/**
	 * renderParentField - this function builds a parent field.
	 *
	 * @param	int	$extra_field_id	The ID of this field.
	 * @param	string	$contents	The data for this field.
	 * @param	string	$size
	 * @param	string	$maxlength
	 * @param	array	$attrs		Array of other attributes
	 * @return	string	text area and data.
	 */
	function renderParentField($extra_field_id, $contents, $size, $maxlength, $attrs = array()) {
		global $ah;
		$arr = $this->getExtraFieldElements($extra_field_id);
		for ($i=0; $i<count($arr); $i++) {
			$keys[$i]=$arr[$i]['element_id'];
			$vals[$i]=$arr[$i]['element_name'];
		}
		$attrsTxt = array();
		if (isset($attrs['form'])) {
			$attrsTxt['form'] = $attrs['form'];
		}
		if (is_object($ah)) {
			$attrsTxt['pattern']='^(?!'.$ah->getID().'$)\d*$';
		} else {
			$attrsTxt['pattern']='^\d*$';
		}
		// Convert artifact id to links.
		$html_contents = preg_replace_callback('/\b(\d+)\b/', create_function('$matches', 'return _artifactid2url($matches[1], \'title\');'), $contents);
		$edit_contents = $this->renderTextField ($extra_field_id, $contents, $size, $maxlength, $attrsTxt);
		return html_e('div',array_merge(array('id'=>'edit'.$extra_field_id, 'style'=>'display: none', 'title'=>_('Tip: Enter a space-separated list of artifact ids ([#NNN] also accepted)')), $attrs), $edit_contents)
		.html_e('div',array_merge(array('id'=>'show'.$extra_field_id, 'style'=>'display: block'), $attrs), $html_contents);
	}

	/**
	 * renderTextArea - this function builds a text area.
	 *
	 * @param	int	$extra_field_id	The ID of this field.
	 * @param	string	$contents	The data for this field.
	 * @param	string	$rows
	 * @param	string	$cols
	 * @param	array	$attrs		Array of other attributes
	 * @return	string	text area and data.
	 */
	function renderTextArea($extra_field_id, $contents, $rows, $cols, $attrs = array()) {
		return html_e('textarea', array_merge(array('name'=>'extra_fields['.$extra_field_id.']', 'rows'=>$rows, 'cols'=>$cols), $attrs), $contents, false);
	}

	/**
	 * renderDatetime - this function builds a Datetime field.
	 *
	 * @param	int	$extra_field_id	The ID of this field.
	 * @param	string	$datetime	datetime for this field.
	 * @param	array	$attrs		Array of other attributes
	 * @return	string	datetime.
	 */
	function renderDatetime($extra_field_id, $datetime, $attrs = array()) {
		if (!$datetime=='') {
			$datetime_format = _('Y-m-d H:i');
			$datetime = date($datetime_format, $datetime);
		}
		if (isset($attrs['class'])) {
			$attrs['class'] = $attrs['class'] . ' datetimepicker';
		} else {
			$attrs['class'] = 'datetimepicker';
		}
		return html_e('input', array_merge(array('type'=>'text', 'name'=>'extra_fields['.$extra_field_id.']', 'value'=>$datetime), $attrs));
	}

	function renderDateRange($extra_field_id, $dateRange, $attrs = array()) {
		// http://html5pattern.com/Dates
		// Date with leapyear-check
		$datepattern = '(?:19|20)(?:(?:[13579][26]|[02468][048])-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))|(?:[0-9]{2}-(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:29|30))|(?:(?:0[13578]|1[02])-31)))';
		return html_e('input', array_merge(array('type'=>'text', 'name'=>'extra_fields['.$extra_field_id.']', 'pattern'=>$datepattern.' '.$datepattern, 'maxlength'=>21, 'size'=>21, 'value'=>$dateRange), $attrs));
	}

	/**
	 * renderEffort - this function builds a Effort extra field.
	 *
	 * @param	int	$extra_field_id	The ID of this field.
	 * @param	string	$contents	The data for this field.
	 * @param	string	$size
	 * @param	string	$maxlength
	 * @param	array	$attrs		Array of other attributes
	 * @return	string	text area and data.
	 */
	function renderEffort($extra_field_id, $contents, $size, $maxlength, $attrs = array()) {
		$effortUnitSet = New EffortUnitSet($this, $this->getEffortUnitSet());
		$effortUnitFactory = New EffortUnitFactory($effortUnitSet);
		$units = $effortUnitFactory->getUnits();
		$vals = array();
		$texts = array();
		$factors = array();
		foreach ($units as $unit) {
			$vals [] = $unit->getID();
			$texts [] = $unit->getName();
			$opts_attrs []['data-factor'] = $unit->getConversionFactorForBaseUnit();
		}
		$valueInUnitBase = $effortUnitFactory->encodedToValueInBaseUnit($contents);
		$value = $effortUnitFactory->encodedToValue($contents);
		$unitId = $effortUnitFactory->encodedToUnitId($contents);
		if (isset($attrs['class'])) {
			$attrs['class'] .= ' effort';
		} else {
			$attrs['class'] = 'effort';
		}
		$attrs['data-effortid'] = $extra_field_id;
		$hiddenAttrs = array();
		if (isset($attrs['form'])) {
			$hiddenAttrs['form'] = $attrs['form'];
		}
		$return = html_e('input', array_merge(array('type'=>'hidden', 'name'=>'extra_fields['.$extra_field_id.']', 'value'=>$valueInUnitBase.'U'.$unitId), $hiddenAttrs));
		$return .= html_e('input', array_merge(array('type'=>'number', 'name'=>'value['.$extra_field_id.']', 'value'=>$value, 'size'=>$size, 'maxlength'=>$maxlength, 'min'=>0), $attrs));
		$return .= html_build_select_box_from_arrays($vals, $texts, 'unit['.$extra_field_id.']', $unitId, false, '', false, '', false, $attrs, $opts_attrs);
		return $return;
	}

	function renderEffortRange($extra_field_id, $contents, $size, $maxlength, $attrs = array()) {
		$effortUnitSet = New EffortUnitSet($this, $this->getEffortUnitSet());
		$effortUnitFactory = New EffortUnitFactory($effortUnitSet);
		$units = $effortUnitFactory->getUnits();
		$vals = array();
		$texts = array();
		$opts_attrs = array();
		foreach ($units as $unit) {
			$vals [] = $unit->getID();
			$texts [] = $unit->getName();
			$opts_attrs []['data-factor'] = $unit->getConversionFactorForBaseUnit();
		}
		$contentsFrom = '0U'.$vals[0];
		$contentsTo = '0U'.$vals[0];
		if (preg_match('/^(\d+U\d+) ?(\d+U\d+)?$/', $contents, $matches)) {
			$contentsFrom = $matches[1];
			$contentsTo = $matches[2];
		}
		$valueInUnitBaseFrom = $effortUnitFactory->encodedToValueInBaseUnit($contentsFrom);
		$valueInUnitBaseTo = $effortUnitFactory->encodedToValueInBaseUnit($contentsTo);
		$valueFrom = $effortUnitFactory->encodedToValue($contentsFrom);
		$valueTo = $effortUnitFactory->encodedToValue($contentsTo);
		$unitIdFrom = $effortUnitFactory->encodedToUnitId($contentsFrom);
		$unitIdTo = $effortUnitFactory->encodedToUnitId($contentsTo);
		$attrs['data-effortid'] = $extra_field_id;
		if (isset($attrs['class'])) {
			$attrs['class'] .= ' effort-range';
		} else {
			$attrs['class'] = 'effort-range';
		}
		$attrsFrom = $attrs;
		$attrsTo = $attrs;
		$hiddenAttrs = array();
		if (isset($attrs['form'])) {
			$hiddenAttrs['form'] = $attrs['form'];
		}
		$return = html_e('input', array_merge(array('type'=>'hidden', 'name'=>'extra_fields['.$extra_field_id.']', 'value'=>$valueInUnitBaseFrom.'U'.$unitIdFrom.' '.$valueInUnitBaseTo.'U'.$unitIdTo), $hiddenAttrs));
		$return .= _('Between')._(':').html_e('br');
		$return .= html_e('input', array_merge(array('type'=>'number', 'name'=>'value_from['.$extra_field_id.']', 'value'=>$valueFrom, 'size'=>$size, 'maxlength'=>$maxlength, 'min'=>0), $attrsFrom));
		$return .= html_build_select_box_from_arrays($vals, $texts, 'unit_from['.$extra_field_id.']', $unitIdFrom, false, '', false, '', false, $attrsFrom, $opts_attrs);
		$return .= html_e('br');
		$return .= _('and')._(':').html_e('br');
		$return .= html_e('input', array_merge(array('type'=>'number', 'name'=>'value_to['.$extra_field_id.']', 'value'=>$valueTo, 'size'=>$size, 'maxlength'=>$maxlength, 'min'=>0), $attrsTo));
		$return .= html_build_select_box_from_arrays($vals, $texts, 'unit_to['.$extra_field_id.']', $unitIdTo, false, '', false, '', false, $attrsTo, $opts_attrs);
		return $return;
	}

	/**
	 * renderFormulaField - this function builds a formula field (RO).
	 *
	 * @param 	string	$contents The data for this field.
	 * @return	string
	 */
	function renderFormulaField($contents) {
		return $contents;
	}

	/**
	 * renderSLAField - this function builds a formula field (RO).
	 *
	 * @param	int	$extra_field_id	The ID of this field.
	 * @param	string	$contents	The data for this field.
	 * @param	$ef
	 * @return	string
	 */
	function renderSLAField($extra_field_id, $contents, $ef) {
		global $aid;

		if (!isset($aid) ||!$aid) {
			return '';
		}

		$config = parse_ini_string($ef["attributes"]);
		$field_name = $config['field'];
		$rule = explode(':', $config['values']);

		if (!isset($config['start_id']) || $aid >= $config['start_id']) {
			// Get Max value from
			// @todo use global variable $aid !!!
			$artifact = artifact_get_object($aid);
			$value = $artifact->getFieldDataByKey('alias', $field_name);
			$field = $artifact->getFieldTypeByKey('alias', $field_name);
			$elements = $artifact->ArtifactType->getExtraFieldElements($field['extra_field_id']);
			for ($i = 0; $i < count($elements); $i++) {
				if ($elements[$i]['element_name'] == $value) {
					$max = 60 * $rule[$i];
					continue;
				}
			}

			if (!isset($max)) {
				$value = '<span class="label label-warning">Not Set</span>';
			} elseif ($ef['alias'] == 'response_time') {
				// Implement Response SLA: Time from create to first comment.
				$messages = $artifact->getMessageObjects();
				if (count($messages) > 0) {
					$message = $messages[count($messages) - 1];
					$current_time = $message->getAddDate();
					$timer_is_running = false;
				} elseif ($artifact->getStatusID() == 2) {
					$current_time = $artifact->getCloseDate();
					$timer_is_running = false;
				} else {
					$current_time = time();
					$timer_is_running = true;
				}
				$time = $current_time - $artifact->getOpenDate();
				$percent = round(100 * $time / $max);
				$remaining = $max - $time;
				if ($remaining >= 0) {
					$value = gmdate("G\h i", $remaining);
				} else {
					$value = '<b>-' . gmdate("G\h i", -$remaining) . "</b>";
				}

				if (!$timer_is_running) {
					if ($remaining >= 0) {
						$value = '<span class="label label-success">' . _('OK') . '</span>';
					} else {
						$value = '<span class="label label-danger">' . _('Missed') . '</span>';
					}
				} else {
					if ($percent > 90) {
						$value = '<span class="sla-alert">' . $value . '</span>';
					} elseif ($percent > 80) {
						$value = '<span class="sla-warning">' . $value . '</span>';
					} else {
						$value = '<span class="sla-normal">' . $value . '</span>';
					}
				}
			} elseif ($ef['alias'] == 'resolution_time') {
				// Implement Resolution SLA: Time from create to closed state.
				list($last_status_change, $timer, $timer_is_running) = json_decode($contents);

				$time = $timer;
				if ($timer_is_running) {
					$time += time() - $last_status_change;
				}

				$percent = round(100 * $time / $max);
				$remaining = $max - $time;
				if ($remaining >= 0) {
					$value = gmdate("G\h i", $remaining);
				} else {
					$value = '<b>-' . gmdate("G\h i", -$remaining) . "</b>";
				}
				if ($artifact->getStatusID() == 2) {
					if ($remaining >= 0) {
						$value = '<span class="label label-success">' . _('OK') . '</span>';
					} else {
						$value = '<span class="label label-danger">' . _('Missed') . '</span>';
					}
				} else {
					if (!$timer_is_running) {
						$value = ' <span class="label label-info">' . _('Suspended') . '</span>';
					} elseif ($percent > 90) {
						$value = '<span class="sla-alert">' . $value . '</span>';
					} elseif ($percent > 80) {
						$value = '<span class="sla-warning">' . $value . '</span>';
					} else {
						$value = '<span class="sla-normal">' . $value . '</span>';
					}
				}
			}
		} else {
			$value = '<span class="label label-default">' . _('Not Available') . '</span>';
		}
		return $value;
	}

	function technicianBox($name = 'assigned_to[]', $checked = 'xzxz', $show_100 = true, $text_100 = 'none', $extra_id = '-1', $extra_name = '', $multiple = false, $attrs = array()) {
		if ($text_100=='none'){
			$text_100=_('Nobody');
		}
		$engine = RBACEngine::getInstance();
		$techs = $engine->getUsersByAllowedAction('tracker', $this->getID(), 'tech') ;

		$ids = array();
		$names = array();

		sortUserList($techs);

		foreach ($techs as $tech) {
			$ids[] = $tech->getID() ;
			$names[] = $tech->getRealName() ;
		}

		if ($extra_id != '-1') {
			$ids[]=$extra_id;
			$names[]=$extra_name;
		}

		if ($multiple) {
			if (!is_array($checked)) {
				$checked = explode(',', $checked);
			}
			$size = min(count($ids)+1, 15);
			return html_build_multiple_select_box_from_arrays($ids, $names, $name, $checked, $size, $show_100, $text_100, false, $attrs);
		} else {
			return html_build_select_box_from_arrays($ids, $names, $name, $checked, $show_100, $text_100, false, false, false, $attrs);
		}
	}

	function submitterBox($name = 'submitted_by[]', $checked = 'xzxz', $show_100 = true, $text_100 = 'none', $extra_id = '-1', $extra_name = '', $multiple = false, $attrs = array()) {
		if ($text_100=='none'){
			$text_100=_('Nobody');
		}
		$result = $this->getSubmitters();
		$ids =& util_result_column_to_array($result,0);
		$names =& util_result_column_to_array($result,1);
		if ($extra_id != '-1') {
			$ids[]=$extra_id;
			$names[]=$extra_name;
		}

		if ($multiple) {
			if (!is_array($checked)) {
				$checked = explode(',', $checked);
			}
			$size = min(count($ids)+1, 15);
			return html_build_multiple_select_box_from_arrays($ids, $names, $name, $checked, $size, $show_100, $text_100, false, $attrs);
		} else {
			return html_build_select_box_from_arrays($ids, $names, $name, $checked, $show_100, $text_100, false, $attrs);
		}
	}

	function lastModifierBox($name='last_modified_by[]', $checked='xzxz', $show_100=true, $text_100='none', $extra_id='-1', $extra_name='', $multiple=false) {
		if ($text_100=='none'){
			$text_100=_('Nobody');
		}
		$result = $this->getLastModifiers();
		$ids =& util_result_column_to_array($result,0);
		$names =& util_result_column_to_array($result,1);
		if ($extra_id != '-1') {
			$ids[] = $extra_id;
			$names[] = $extra_name;
		}

		if ($multiple) {
			if (!is_array($checked)) {
				$checked = explode(',', $checked);
			}
			$size = min(count($ids)+1, 15);
			return html_build_multiple_select_box_from_arrays($ids, $names, $name, $checked, $size, $show_100, $text_100);
		} else {
			return html_build_select_box_from_arrays($ids, $names, $name, $checked, $show_100, $text_100);
		}
	}

	function cannedResponseBox($name = 'canned_response', $checked = 'xzxz', $attrs = array()) {
		if (!isset($attrs['id'])) {
			$attrs['id'] = $name;
		}
		return html_build_select_box($this->getCannedResponses(), $name, $checked, true, 'none', false, '', false, $attrs);
	}

	/**
	 * statusBox - show the statuses - automatically shows the "custom statuses" if they exist
	 *
	 * @param	string	$name
	 * @param	string	$checked
	 * @param	bool	$show_100
	 * @param	string	$text_100
	 * @param	array	$attrs
	 * @return	string	HTML code
	 */
	function statusBox($name = 'status_id', $checked = 'xzxz', $show_100 = false, $text_100 = 'none', $attrs = array()) {
		if ($text_100=='none'){
			$text_100=_('None');
		}
		return html_build_select_box($this->getStatuses(), $name, $checked, $show_100, $text_100, false, '', false, $attrs);
	}

	/**
	 * priorityBox - show the priorities
	 *
	 * @param	string	$name
	 * @param	string	$checked_val
	 * @param	bool	$nochange
	 * @param	array	$attrs
	 * @param	bool	$show_any
	 * @return	string	HTML code
	 */
	function priorityBox($name = 'priority', $checked_val = '3', $nochange = false, $attrs = array(), $show_any = false){
		return html_build_priority_select_box($name, $checked_val, $nochange, $attrs, $show_any);
	}

	function javascript() {
		$jsvariable ="
	var invalidSelectMsg = '"._("One or more of the selected options is not allowed")."';
	var invalidInputMsg = '". _("This choice is not allowed")."';
	var groupId =".$this->Group->getID().";
	var atId = ".$this->getID().";";
		$effortUnitSet = New EffortUnitSet($this, $this->getEffortUnitSet());
		if ($effortUnitSet->isAutoconvert()) {
			$jseffort = <<<'EOS'
	$("select.effort").change(function(){
		var effortid = $(this).data("effortid");
		var value = parseInt(parseInt($("input[name='extra_fields["+effortid+"]']").val())/$("select[name='unit["+effortid+"]'] option:selected").data('factor'));
		$("input[name='value["+effortid+"]']").val(value);
		$("input[name='extra_fields["+effortid+"]']").val(value*$("select[name='unit["+effortid+"]'] option:selected").data('factor')+'U'+$("select[name='unit["+effortid+"]']").val());
	});
	$("input.effort").change(function(){
		var effortid = $(this).data("effortid");
		var value = $("input[name='value["+effortid+"]']").val();
		$("input[name='extra_fields["+effortid+"]']").val(value*$("select[name='unit["+effortid+"]'] option:selected").data('factor')+'U'+$("select[name='unit["+effortid+"]']").val());
	});
	$(".effort-range").change(function(){
		var effortid = $(this).data("effortid");
		$("input[name='extra_fields["+effortid+"]']").val($("input[name='value_from["+effortid+"]']").val()*$("select[name='unit_from["+effortid+"]'] option:selected").data('factor')+'U'+$("select[name='unit_from["+effortid+"]']").val()+' '+$("input[name='value_to["+effortid+"]']").val()*$("select[name='unit_to["+effortid+"]'] option:selected").data('factor')+'U'+$("select[name='unit_to["+effortid+"]']").val());
	});
EOS;
		} else {
		$jseffort = <<<'EOS'
	$(".effort").change(function(){
		var effortid = $(this).data("effortid");
		$("input[name='extra_fields["+effortid+"]']").val($("input[name='value["+effortid+"]']").val()*$("select[name='unit["+effortid+"]'] option:selected").data('factor')+'U'+$("select[name='unit["+effortid+"]']").val());
	});
	$(".effort-range").change(function(){
		var effortid = $(this).data("effortid");
		$("input[name='extra_fields["+effortid+"]']").val($("input[name='value_from["+effortid+"]']").val()*$("select[name='unit_from["+effortid+"]'] option:selected").data('factor')+'U'+$("select[name='unit_from["+effortid+"]']").val()+' '+$("input[name='value_to["+effortid+"]']").val()*$("select[name='unit_to["+effortid+"]'] option:selected").data('factor')+'U'+$("select[name='unit_to["+effortid+"]']").val());
	});
EOS;
		}
		$javascript = <<<'EOS'
	function showMessage( msg_text, msg_class) {
		$("#maindiv h1").append($("<p>", { "class": msg_class }).html( msg_text )).show();
	};
	$.expr[':'].invalid = function(elem, index, match) {
		var invalids = document.querySelectorAll(':invalid'), result = false, len = invalids.length;
		if (len) {
			for (var i=0; i<len; i++) {
				if (elem === invalids[i]) {
					result = true;
					break;
				}
		}
		}
		return result;
	};

	$("input[type='radio'].readonly, input[type='checkbox'].readonly").on('click', function(){
		return false;
	}).on('keydown', function(event){
		if(event.keyCode !== 9) return false;
	});
	$(".in-formula").change(function(){
		$.ajax({
			type: 'POST',
			url: 'index.php',
			data: 'rtype=ajax&function=get_formulas_results&group_id='+groupId+'&atid='+atId+'&status='+$("select[name='status_id'] option:selected").text()+'&assigned_to='+$("select[name='assigned_to'] option:selected").text()+'&'+$("[name^='extra_fields'], #tracker-summary, #tracker-description, [name='priority']").serialize(),
			async: false,
			dataType: 'json',
			success: function(answer){
				if(answer['message']) {
					showMessage(answer['message'], 'error');
				}
				fields = answer['fields'];
				$.each(fields, function (index, field) {
					if (field.error!=null){
						showMessage(field.error, 'error');
					}
					fieldObj = $("[name^='extra_fields["+field.id+"]']");
					if (fieldObj.is("input[type='checkbox']")){
						fieldObj.each(function() {
							var in_array = -1;
							for (var key in field.value) {
								if (field.value[key] == $(this).val()) {
									in_array = key;
									break;
								}
							}
							if (in_array > -1) {
								$(this).prop("checked",true);
							} else {
								$(this).prop("checked",false);
							}
						});
					} else if (fieldObj.is("input[type='radio']")){
						fieldObj.each(function() {
							var in_array = -1;
							for (var key in field.value) {
								if (field.value[key] == $(this).val()) {
									in_array = key;
									break;
								}
							}
							if (in_array > -1) {
								$(this).prop("checked",true);
							} else {
								$(this).prop("checked",false);
							}
						});
					} else if (fieldObj.is("input")){
						fieldObj.val(field.value);
					} else if (fieldObj.is("select")){
						fieldObj.val(field.value);
					}  else if (fieldObj.is("textarea")){
						fieldObj.val(field.value);
					}
				});
				return true;
			}
		});
	});

	$("img.addparent").click(function(){
		$.ajax({
			type: 'POST',
			url: 'index.php',
			data: 'rtype=ajax&function=add_parent&group_id='+groupId+'&atid='+atId+'&aid='+$("input#aid").val()+'&parent_id='+$("input#parent_id").val(),
			async: false,
			dataType: 'json',
			success: function(answer){
				if(answer['message']) {
					showMessage(answer['message'], 'error');
				} else {
					$("table.parent").replaceWith(answer['parent']);
					$("input[name='extra_fields["+answer['parent_efid']+"]']").val(answer['parent_id']);
					$("div#show"+answer['parent_efid']).html(answer['parent_link']);
					$("input#parent_id").val('');
					$("div.addparent").addClass('hide');
					$("img.removeparent").click(function(){
						removeParent($(this).data("id"));
					});
				}
				return true;
			}
		});
	});

	$("img.addchild").click(function(){
		$.ajax({
			type: 'POST',
			url: 'index.php',
			data: 'rtype=ajax&function=add_child&group_id='+groupId+'&atid='+atId+'&aid='+$("input#aid").val()+'&child_id='+$("input#child_id").val(),
			async: false,
			dataType: 'json',
			success: function(answer){
				if(answer['message']) {
					showMessage(answer['message'], 'error');
				} else {
					$("table.children").replaceWith(answer['children']);
					$("input#child_id").val('');
					$("img.removechild").click(function(){
						removeChild($(this).data("id"));
					});
				}
				return true;
			}
		});
	});


	$("img.removeparent").click(function(){
		removeParent($(this).data("id"));
	});

	function removeParent(id) {
		$.ajax({
			type: 'POST',
			url: 'index.php',
			data: 'rtype=ajax&function=remove_parent&group_id='+groupId+'&atid='+atId+'&aid='+$("input#aid").val()+'&parent_id='+id,
			async: false,
			dataType: 'json',
			success: function(answer){
				if(answer['message']) {
					showMessage(answer['message'], 'error');
				} else {
					$("table#parent"+id).remove();
					$("div.addparent").removeClass('hide');
				}
				return true;
			}
		});
	};

	$("img.removechild").click(function(){
		removeChild($(this).data("id"));
	});

	function removeChild(id) {
		$.ajax({
			type: 'POST',
			url: 'index.php',
			data: 'rtype=ajax&function=remove_child&group_id='+groupId+'&atid='+atId+'&aid='+$("input#aid").val()+'&child_id='+id,
			async: false,
			dataType: 'json',
			success: function(answer){
				if(answer['message']) {
					showMessage(answer['message'], 'error');
				} else {
					$("span#child"+id).remove();
				}
				return true;
			}
		});
	};
	$(".autoassign[name^='extra_fields']").change(function(){
		if ($(this).prop('tagName') == 'SELECT') {
			var elmnts = $(this).children('option:selected');
		} else {
			var elmnts = $(this).siblings('input:checked');
		}
		elmnts.each(function(i){
			var aat = $(this).data("autoassignto");
			$("select#tracker-assigned_to option[value="+aat.id+"]").prop('selected', true);
			$("span#tracker-assigned_to").text(aat.name);
		});
	});
	$(".with-depcy[name^='extra_fields']").change(function(){
		if ($(this).prop('tagName') == 'SELECT') {
			var elmnts = $(this).children('option:selected');
		} else {
			var elmnts = $(this).siblings('input:checked');
		}
		elmnts.each(function(i){
			var dep = $(this).data("dependency");
			if (this.value!='100') {
				$(dep).each(function(j, val) {
					$("select[name^='extra_fields["+val.field+"]']:invalid, input[name^='extra_fields["+val.field+"]']:invalid").each(function() {
						this.setCustomValidity("");
						$(this).off("change.invalid");
					});
					$("select[name^='extra_fields["+val.field+"]'] option").each(function(k,opt){
						if (this.value!='100') {
							if ($.inArray(parseInt(this.value),val.elmnt)>-1) {
								$(this).prop('disabled', false).removeClass('option_disabled');
							} else if (i==0) {
								$(this).prop('disabled', true);
								$(this).addClass('option_disabled');
							}
						}
					});
					$("input[name^='extra_fields["+val.field+"]']").each(function(k,opt){
						if (this.value!='100') {
							if ($.inArray(parseInt(this.value),val.elmnt)>-1) {
								$(this).prop('disabled', false).removeClass($(this).attr('type')+'_disabled');
							} else if (i==0) {
								$(this).prop('disabled', true);
								$(this).addClass($(this).attr('type')+'_disabled');
							}
						}
					});
				});
			} else {
				$(dep.fields).each(function(j, val) {
					$("select[name^='extra_fields["+val+"]']:invalid, input[name^='extra_fields["+val+"]']:invalid").each(function() {
						this.setCustomValidity("");
					});
					$("select[name^='extra_fields["+val+"]'] option.option_disabled").each(function() {
						$(this).prop('disabled', false).removeClass('option_disabled');
					});
					$("input.radio_disable[name^='extra_fields["+val+"]']").each(function() {
						$(this).prop('disabled', false).removeClass('radio_disabled');
					});
					$("input.checkbox_disabled[name^='extra_fields["+val+"]']").each(function() {
						$(this).prop('disabled', false).removeClass('checkbox_disabled');
					});
				});
			}
		});
		$("select[name^='extra_fields'] option:selected:disabled").parent().each(function() {
			$(this).children('option:selected:disabled').prop('disabled', false);
			this.setCustomValidity(invalidSelectMsg);
			$(this).on("change.invalid", function() {
				$(this).children('option.option_disabled:not(:disabled):not(:selected)').prop('disabled', true);
				if (!$(this).children('option.option_disabled:selected').length) {
					this.setCustomValidity("");
					$(this).off("change.invalid");
				}
			});
		});
		$("input[name^='extra_fields']:checked:disabled").each(function() {
			$(this).prop('disabled', false);
			this.setCustomValidity(invalidInputMsg);
			if ($(this).attr('type') == 'radio') {
				$(this).siblings('input[type="radio"]').on("change.invalid", function() {
					$(this).siblings('input[type="radio"]:invalid').prop('disabled', true).addClass('input_disabled').each(function() {
						this.setCustomValidity("");
					});
					$(this).siblings('input[type="radio"]').off("change.invalid");
					$(this).off("change.invalid");
				});
			} else {
				$(this).on("change.invalid", function() {
					$(this).prop('disabled', true);
					this.setCustomValidity("");
					$(this).off("change.invalid");
				});
			}
		});
	});
EOS;
		return html_e('script', array('type'=>'text/javascript'), '//<![CDATA['."\n".'$(function(){'.$jsvariable."\n".$jseffort."\n".$javascript.'});'."\n".'//]]>');
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
