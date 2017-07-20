<?php
/**
 * Generic Tracker Content Widget Class
 *
 * Copyright 2016,2017, Franck Villaume - TrivialDev
 * Copyright 2017, Stephane-Eymeric Bredthauer - TrivialDev
 * http://fusionforge.org
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

require_once 'Widget.class.php';
require_once $gfwww.'include/jquery_plugins.php';

class Widget_TrackerContent extends Widget {
	var $trackercontent_title;
	var $layoutExtraFieldIDs;

	function __construct() {
		$request =& HTTPRequest::instance();
		$owner_id = (int)substr($request->get('owner'), 1);
		if (!$owner_id) {
			$owner_id = (int)$request->get('atid');
		}
		parent::__construct('trackercontent', $owner_id, WidgetLayoutManager::OWNER_TYPE_TRACKER);
		$this->setOwner($owner_id, WidgetLayoutManager::OWNER_TYPE_TRACKER);
	}

	function getTitle() {
		$hp = Codendi_HTMLPurifier::instance();
		return $this->trackercontent_title ? $hp->purify($this->trackercontent_title, CODENDI_PURIFIER_CONVERT_HTML)  : _('Tracker Content Box');
	}

	function isUnique() {
		return false;
	}

	function isAvailable() {
		return true;
	}

	function getDescription() {
		return _('Create an empty widget to link fields together and then organize the artifact display view (update & submit new).');
	}

	function loadContent($id) {
		$this->content_id = $id;
		$this->fetchData($id);
	}

	function fetchData($id) {
		$res = db_query_params('SELECT title FROM artifact_display_widget WHERE id = $1', array($id));
		if ($res) {
			$title = db_result($res, 0, 'title');
		}
		$this->trackercontent_title = $title;
		$this->layoutExtraFieldIDs = $this->getLayoutExtraFieldIDs($id);
	}

	function create(&$request) {
		$hp = Codendi_HTMLPurifier::instance();
		$this->trackercontent_title = $hp->purify($request->get('title'), CODENDI_PURIFIER_CONVERT_HTML);
		$trackerrows = getArrayFromRequest('trackercontent_layout');
		$trackerextrafields = getArrayFromRequest('trackercontent_ef');
		$res = db_query_params('INSERT INTO artifact_display_widget (owner_id, title) VALUES ($1, $2)', array($this->owner_id, $this->trackercontent_title));
		$content_id = db_insertid($res, 'artifact_display_widget', 'id');
		foreach ($trackerrows as $rowkey => $trackerrow) {
			$columns = explode(',', $trackerrow);
			$extrafields = explode(',', $trackerextrafields[$rowkey]);
			$rowid = $rowkey;
			foreach ($columns as $columnkey => $column) {
				if ($extrafields[$columnkey] == "fake") {
					$extrafieldid = 0;
				} else {
					$extrafieldid = substr($extrafields[$columnkey], 2); //remove prefix ef
				}
				db_query_params('INSERT INTO artifact_display_widget_field (id, field_id, column_id, row_id, width) VALUES ($1, $2, $3, $4, $5)',
						array($content_id, $extrafieldid, $columnkey, $rowid, $column));
			}
		}
		return $content_id;
	}

	function destroy($id) {
		db_query_params('DELETE FROM artifact_display_widget WHERE id = $1 AND owner_id = $2',array($id, $this->owner_id));
		db_query_params('DELETE FROM artifact_display_widget_field WHERE id = $1', array($id));
	}

	private function getPartialPreferencesFormTitle($title) {
		return html_e('p', array(), _('Title')._(':').html_e('input', array('type' => 'text', 'name' => 'title', 'size' => 30, 'value' => htmlspecialchars($title))));
	}

	private function buildRenderWidget() {
		global $HTML;
		global $ath;
		$content = html_e('p', array(), _('Build your layout and drag & drop customfields in cells')._(':'));
		$content .= $HTML->listTableTop(array(), array(), '', 'layout-manager').
				'<tr>
				<td>'
				.html_e('div', array('class' => 'layout-manager-row-add'), '+');

		if (count($this->layoutExtraFieldIDs) > 0) {
			foreach ($this->layoutExtraFieldIDs as $row_id => $column_id) {
				$cells = array();
				$content .= '<table class="layout-manager-row" id="widget_layout_build">
							<tr>
							<td class="layout-manager-column-add">+</td>';
				foreach ($column_id as $extrafieldID) {
					$keys = array_keys($extrafieldID);
					if ($keys[0]) {
						$extrafieldObject = new ArtifactExtraField($ath, $keys[0]);
						$divEF = html_e('div', array('id' => 'ef'.$keys[0], 'class' => 'wb_extrafield', 'style' => 'background: #e6e6e6 none repeat scroll 0 0; padding: 2px; text-align: center;'), $extrafieldObject->getName().'<div id="xef'.$keys[0].'" class="ef-widget-remove">x</div>');
					} else {
						$divEF = '<div id="fake" class="wb_extrafield" style="display: none"></div>';
					}

					$content .= '<td class="layout-manager-column" width="'.$extrafieldID[$keys[0]][0].'%">
							<div class="layout-manager-column-remove">x</div>
							<div class="layout-manager-column-width">
							'._('Section Title')._(':').'<br  />
							<input type="text" value="'.htmlspecialchars($extrafieldID[$keys[0]][1]).'" size="20" maxlength="20" /><br />
							<input type="number" value="'.$extrafieldID[$keys[0]][0].'" autocomplete="off" size="1" maxlength="3" />%
							</div>';
					$content .= $divEF;
					$content .= '</td><td class="layout-manager-column-add">+</td>';
				}
				$content .= '</tr></table>';
				$content .= html_e('div', array('class' => 'layout-manager-row-add'), '+');
			}
		} else {
			$content .= '<table class="layout-manager-row" id="widget_layout_build">
				<tr>
				<td class="layout-manager-column-add">+</td>';
			$content .= '<td class="layout-manager-column">
				<div class="layout-manager-column-remove">x</div>
				<div class="layout-manager-column-width">
				'._('Section Title')._(':').'<br  />
				<input type="text" value="" size="20" maxlength="20" /><br />
				<input type="number" value="50" autocomplete="off" size="1" maxlength="3" />%
				</div>
				<div id="fake" class="wb_extrafield" style="display: none"></div>
				</td>
				<td class="layout-manager-column-add">+</td>';
			$content .= '<td class="layout-manager-column">
				<div class="layout-manager-column-remove">x</div>
				<div class="layout-manager-column-width">
				'._('Section Title')._(':').'<br  />
				<input type="text" value="" size="20" maxlength="20" /><br />
				<input type="number" value="50" autocomplete="off" size="1" maxlength="3" />%
				</div>
				<div id="fake" class="wb_extrafield" style="display: none"></div>
				</td>
				<td class="layout-manager-column-add">+</td>
				</tr></table>';
			$content .= html_e('div', array('class' => 'layout-manager-row-add'), '+');
		}
		$content .= '</td>
			</tr>'.
			$HTML->listTableBottom();
		$jsvariable = "var sectiontitle = '"._("Section Title")._(":")."';";
		$javascript = <<<'EOS'
				var controllerWidgetBuilder;
				jQuery(document).ready(function() {
					controllerWidgetBuilder = new WidgetBuilderController({
						buttonAddRow:		jQuery('.layout-manager-row-add'),
						buttonAddColumn:	jQuery('.layout-manager-column-add'),
						buttonRemoveColumn:	jQuery('.layout-manager-column-remove'),
						buttonRemoveEF:		jQuery('.ef-widget-remove'),
						labelTitle:		sectiontitle
					});
					jQuery('.layout-manager-column').droppable({
										accept: '#extrafield_table .wb_extrafield',
										drop: function(event, ui) {
											ui.draggable.appendTo(this).css('position', '');
											ui.draggable.parent().droppable('destroy');
											ui.draggable.parent().find('#fake').remove();
											ui.draggable.find('#x'+ui.draggable.attr('id')).show()},
										over: function(event, ui) {
											ui.helper.css('z-index', 1);
										},
					});
					if (jQuery("[name='name[trackercontent][add]']") != 'undefined') {
						jQuery("[name='name[trackercontent][add]']").click(function(){
							var form = jQuery(this).parents('form').first();
							form.find('.layout-manager-row').each(function(i, e) {
								jQuery('<input>', {
									type: 'hidden',
									name: 'trackercontent_layout[]',
									value: jQuery(e).find('.layout-manager-column input[type=number]').map(function(){ return this.value;}).get().join(',')
								}).appendTo(form);
								jQuery('<input>', {
									type: 'hidden',
									name: 'trackercontent_ef[]',
									value: jQuery(e).find('.layout-manager-column > .wb_extrafield').map(function(){ return this.id;}).get().join(',')
								}).appendTo(form);
								jQuery('<input>', {
									type: 'hidden',
									name: 'trackercontent_title[]',
									value: jQuery(e).find('.layout-manager-column input[type=text]').map(function(){ return this.value;}).get().join(',')
								}).appendTo(form);
							});
						});
					}
					if (jQuery("[name='trackercontent-submit']") != 'undefined') {
						jQuery("[name='trackercontent-submit']").click(function(){
							var form = jQuery(this).parents('form').first();
							form.find('.layout-manager-row').each(function(i, e) {
								jQuery('<input>', {
									type: 'hidden',
									name: 'trackercontent_layout[]',
									value: jQuery(e).find('.layout-manager-column input[type=number]').map(function(){ return this.value;}).get().join(',')
								}).appendTo(form);
								jQuery('<input>', {
									type: 'hidden',
									name: 'trackercontent_ef[]',
									value: jQuery(e).find('.layout-manager-column > .wb_extrafield').map(function(){ return this.id;}).get().join(',')
								}).appendTo(form);
								jQuery('<input>', {
									type: 'hidden',
									name: 'trackercontent_title[]',
									value: jQuery(e).find('.layout-manager-column input[type=text]').map(function(){ return this.value;}).get().join(',')
								}).appendTo(form);
							});
						});
					}
				});
EOS;
		$content .= html_e('script', array( 'type'=>'text/javascript'), '//<![CDATA['."\n".'jQuery(function(){'.$jsvariable."\n".$javascript.'});'."\n".'//]]>');
		return $content;
	}

	private function getAvailableExtraFieldsForm($owner_id, $preference = false) {
		global $HTML;
		$atid = $owner_id;
		$artifactTypeObject = artifactType_get_object($atid);
		$availableExtraFields = $artifactTypeObject->getExtraFields();
		$stillAvailableExtraFields = $this->availableExtrafields($owner_id, $availableExtraFields);
		$arr = array();
		if ($preference) {
			$arr = $this->getExtraFieldIDs($owner_id);
		}
		if (count($stillAvailableExtraFields) > 0 || count($arr) > 0) {
			$content = html_e('p', array(), sprintf(_('Drag & drop into your layout the available custom fields from %s to display into this widget'), $artifactTypeObject->getName())._(':'));
			$content .= $HTML->listTableTop(array(), array(), 'full', 'extrafield_table');
		}
		if (count($stillAvailableExtraFields) > 0) {
			$cells = array();
			for ($i = 0; count($stillAvailableExtraFields) > $i; $i++) {
				$cells[] = array(html_e('div', array('id' => 'ef'.$stillAvailableExtraFields[$i][0], 'class' => 'wb_extrafield', 'style' => 'background: #e6e6e6 none repeat scroll 0 0; padding: 2px; text-align: center;'), util_unconvert_htmlspecialchars($stillAvailableExtraFields[$i]['field_name']).'<div id="xef'.$stillAvailableExtraFields[$i][0].'" style="display: none" class="ef-widget-remove">x</div>'), 'id' => 'tdef'.$stillAvailableExtraFields[$i][0], 'class' => 'td-droppable', 'width' => '50%');
				if ($i % 2) {
					$content .= $HTML->multiTableRow(array(), $cells);
					$cells = array();
				}
			}
			if (count($cells)) {
				$content .= $HTML->multiTableRow(array(), $cells);
			}
		}
		if (count($arr) > 0) {
			$cells = array();
			for ($i = 0; count($arr) > $i; $i++) {
				$cells[] = array('', 'id' => 'tdef'.$arr[$i], 'width' => '50%');
				if ($i % 2) {
					$content .= $HTML->multiTableRow(array(), $cells);
					$cells = array();
				}
			}
			if (count($cells)) {
				$content .= $HTML->multiTableRow(array(), $cells);
			}
		}
		if (count($stillAvailableExtraFields) > 0 || count($arr) > 0) {
			$content .= $HTML->listTableBottom();
			$javascript = <<<'EOS'
				jQuery(document).ready(function() {
					var wb_extrafield_start;
					jQuery('.wb_extrafield', '#extrafield_table').draggable({
												cursor: "move",
												helper: "clone",
												});
				});
EOS;
			$content .= html_e('script', array( 'type'=>'text/javascript'), '//<![CDATA['."\n".'jQuery(function(){'.$javascript.'});'."\n".'//]]>');
		}

		if (!isset($content)) {
			$content = $HTML->information(_('No customfields available to link to a widget'));
		}
		return $content;
	}

	private function availableExtrafields($owner_id, $extrafields) {
		$arr = $this->getExtraFieldIDs($owner_id);
		$availableExtrafields = array();
		foreach ($extrafields as $extrafield) {
			if (($extrafield[3] != ARTIFACT_EXTRAFIELDTYPE_PARENT) && !in_array($extrafield[0], $arr)) {
				$availableExtrafields[] = $extrafield;
			}
		}
		return $availableExtrafields;
	}

	private function getExtraFieldIDs($id) {
		$res = db_query_params('select field_id from artifact_display_widget_field, artifact_display_widget
					where artifact_display_widget_field.id = artifact_display_widget.id and artifact_display_widget.owner_id = $1', array($id));
		$extrafieldIDs = array();
		if ($res) {
			$extrafieldIDs = util_result_column_to_array($res, 0);
		}
		return $extrafieldIDs;
	}

	private function getLayoutExtraFieldIDs($id) {
		$res = db_query_params('select row_id, column_id, field_id, width, section from artifact_display_widget_field where id = $1 order by row_id, column_id', array($id));
		$extrafieldIDs = array();
		if ($res && (db_numrows($res) > 0)) {
			while ($arr = db_fetch_array($res)) {
				// row_id is unique, column_id is unique per row, field_id is unique, width is not unique, section is not unique
				$extrafieldIDs[$arr[0]][$arr[1]][$arr[2]] = array($arr[3], $arr[4]);
			}
		}
		return $extrafieldIDs;
	}

	function getInstallPreferences() {
		$request =& HTTPRequest::instance();
		$owner_id = (int)substr($request->get('owner'), 1);
		$content = $this->getPartialPreferencesFormTitle(_('Enter title of Tracker Content Box'));
		$content .= $this->buildRenderWidget();
		$content .= $this->getAvailableExtraFieldsForm($owner_id);
		return $content;
	}

	function getContent() {
		global $ath;
		global $ah;
		global $group_id;
		global $group;
		global $aid;
		global $atid;
		global $HTML;
		global $func;

		//manage redirect in case of missing required fields
		global $extra_fields;

		$return = '';
		$readonly = false;
		if (count($this->layoutExtraFieldIDs) > 0) {
			$mandatoryDisplay = false;
			$selected = array();
			if (is_object($ah)) {
				$selected = $ah->getExtraFieldData();
				$efInFormula = $ath->getExtraFieldsInFormula();
				$efWithFormula = $ath->getExtraFieldsWithFormula();
			} elseif ($func = 'add') {
				$selected = $ath->getExtraFieldsDefaultValue();
				$efInFormula = $ath->getExtraFieldsInFormula(array(), false, false);
				$efWithFormula = $ath->getExtraFieldsWithFormula(array(), false, false);
			}
			if (!forge_check_perm('tracker', $atid, 'submit')) {
				$readonly = true;
			}
			foreach ($this->layoutExtraFieldIDs as $row_id => $column_id) {
				$return .= $HTML->listTableTop();
				$cells = array();
				foreach ($column_id as $extrafieldID) {
					$keys = array_keys($extrafieldID);
					if ($keys[0]) {
						$cellContent = '';
						if (strlen($extrafieldID[$keys[0]][1]) > 0) {
							$cellContent .= html_e('div', array('class' => 'widget_section_title'), htmlspecialchars($extrafieldID[$keys[0]][1]));
						}
						$extrafieldObject = new ArtifactExtraField($ath, $keys[0]);
						if ($func == 'add') {
							$display = !(int)$extrafieldObject->isHiddenOnSubmit();
						} else {
							$display = true;
						}
						if ($display) {
							$value = null;
							$allowed = false;
							if (isset($selected[$keys[0]])) {
								$value = $selected[$keys[0]];
							} elseif (isset($extra_fields[$keys[0]])) {
								$value = $extra_fields[$keys[0]];
							}
							$attrs = array('form' => 'trackerform');
							$mandatory = '';
							if ($extrafieldObject->isRequired() && !$readonly) {
								$mandatory = utils_requiredField();
								$mandatoryDisplay = true;
								$attrs['required'] = 'required';
							}
							if (strlen($extrafieldObject->getDescription()) > 0) {
								$attrs['title'] = $extrafieldObject->getDescription();
							}
							if (in_array($extrafieldObject->getID(), $efInFormula)) {
								$attrs['class'] = (empty($attrs['class']) ? '' : $attrs['class'].' ').'in-formula';
							}
							if (in_array($extrafieldObject->getID(), $efWithFormula)) {
								$attrs['class'] = (empty($attrs['class']) ? '' : $attrs['class'].' ').'with-formula readonly';
								$attrs['readonly'] = 'readonly';
							}
							$cellContent .= html_e('strong', array(), $extrafieldObject->getName()._(':')).$mandatory.html_e('br');
							switch ($extrafieldObject->getType()) {
								case ARTIFACT_EXTRAFIELDTYPE_SELECT:
									if ($readonly) {
										if ($value == 100) {
											$value = $extrafieldObject->getShow100label();
										} else {
											$value = $ath->getElementName($value);
										}
										$cellContent .= $value;
									} else {
										$parent = $extrafieldObject->getParent();
										if (!is_null($parent) && !empty($parent) && $parent != '100') {
											$selectedElmnts = (isset($selected[$parent]) ? $selected[$parent] : '');
											$allowed = $aef->getAllowedValues($selectedElmnts);
										}
										$cellContent .= $ath->renderSelect($keys[0], $value, $extrafieldObject->getShow100(), $extrafieldObject->getShow100label(), false, false, $allowed, $attrs);
									}
									break;
								case ARTIFACT_EXTRAFIELDTYPE_CHECKBOX:
									if ($readonly) {
										if ($value == 100) {
											$value = $extrafieldObject->getShow100label();
										} else {
											$value = explode(',', $ath->getElementName($value));
										}
										$cellContent .= join(html_e('br'), $value);
									} else {
										$parent = $extrafieldObject->getParent();
										if (!is_null($parent) && !empty($parent) && $parent != '100') {
											$selectedElmnts = (isset($selected[$parent]) ? $selected[$parent] : '');
											$allowed = $aef->getAllowedValues($selectedElmnts);
										}
										$cellContent .= $ath->renderCheckbox($keys[0], $value, $extrafieldObject->getShow100(), $extrafieldObject->getShow100label(), $allowed, $attrs);
									}
									break;
								case ARTIFACT_EXTRAFIELDTYPE_RADIO:
									if ($readonly) {
										if ($value == 100) {
											$value = $extrafieldObject->getShow100label();
										} else {
											$value = $ath->getElementName($value);
										}
										$cellContent .= $value;
									} else {
										$parent = $extrafieldObject->getParent();
										if (!is_null($parent) && !empty($parent) && $parent != '100') {
											$selectedElmnts = (isset($selected[$parent]) ? $selected[$parent] : '');
											$allowed = $aef->getAllowedValues($selectedElmnts);
										}
										$cellContent .= $ath->renderRadio($keys[0], $value, $extrafieldObject->getShow100(), $extrafieldObject->getShow100label(), false, false, $allowed, $attrs);
									}
									break;
								case ARTIFACT_EXTRAFIELDTYPE_TEXT:
									if ($readonly) {
										if (strlen($value) > 0) {
											$value = preg_replace('/((http|https|ftp):\/\/\S+)/', "<a href=\"\\1\" target=\"_blank\">\\1</a>", $value);
										} else {
											$value = '&nbsp;';
										}
										$cellContent .= $value;
									} else {
										$attrs['style'] = 'box-sizing: border-box; width: 100%';
										if ($extrafieldObject->getPattern()) {
											$attrs['pattern'] = $extrafieldObject->getPattern();
										}
										$cellContent .= $ath->renderTextField($keys[0], $value, $extrafieldObject->getAttribute1(), $extrafieldObject->getAttribute2(), $attrs);
									}
									break;
								case ARTIFACT_EXTRAFIELDTYPE_MULTISELECT:
									if ($readonly) {
										if ($value == 100) {
											$value = $extrafieldObject->getShow100label();
										} else {
											$value = explode(',', $ath->getElementName($value));
										}
										$cellContent .= join(html_e('br'), $value);
									} else {
										$parent = $extrafieldObject->getParent();
										if (!is_null($parent) && !empty($parent) && $parent != '100') {
											$selectedElmnts = (isset($selected[$parent]) ? $selected[$parent] : '');
											$allowed = $aef->getAllowedValues($selectedElmnts);
										}
										$cellContent .= $ath->renderMultiSelectBox($keys[0], $value, $extrafieldObject->getShow100(), $extrafieldObject->getShow100label(), $allowed, $attrs);
									}
									break;
								case ARTIFACT_EXTRAFIELDTYPE_TEXTAREA:
									if ($readonly) {
										if (strlen($value) > 0) {
											$value = preg_replace('/((http|https|ftp):\/\/\S+)/', "<a href=\"\\1\" target=\"_blank\">\\1</a>", $value);
										} else {
											$value = '&nbsp;';
										}
										$cellContent .= $value;
									} else {
										$attrs['style'] = 'box-sizing: border-box; width: 100%';
										$cellContent .= $ath->renderTextArea($keys[0], $value, $extrafieldObject->getAttribute1(), $extrafieldObject->getAttribute2(), $attrs);
									}
									break;
								case ARTIFACT_EXTRAFIELDTYPE_STATUS:
									if ($readonly) {
										if ($value == 100) {
											$value = $extrafieldObject->getShow100label();
										} else {
											$value = $ath->getElementName($value);
										}
										$cellContent .= $value;
									} else {
										// parent artifact can't be close if a child is still open
										if ( $func != "add" &&
												$extrafieldObject->getAggregationRule() == ARTIFACT_EXTRAFIELD_AGGREGATION_RULE_STATUS_CLOSE_RESTRICTED &&
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
												$extrafieldObject = new ArtifactExtraField($ath, $keys[0]);
												//$aef = new ArtifactExtraField($this, $efarr[$i]['extra_field_id']);
												$statusArr = $extrafieldObject->getAvailableValues();
												$openStatus = array();
												foreach ($statusArr as $status) {
													if ($child['status_id'] == 1) {
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
										$atw = new ArtifactWorkflow($ath, $keys[0]);
										// Special treatment for the initial step (Submit). In this case, the initial value is the first value.
										if (!$value) {
											$value = 100;
										}
										$allowedWF = $atw->getNextNodes($value);
										if ($allowed) {
											$allowed = array_intersect($allowed, $allowedWF);
										} else {
											$allowed = $allowedWF;
										}
										$allowed[] = $value;
										$cellContent .= $ath->renderSelect($keys[0], $value, false, $extrafieldObject->getShow100label(), false, false, $allowed, $attrs);
									}
									break;
								//case ARTIFACT_EXTRAFIELDTYPE_ASSIGNEE:
								case ARTIFACT_EXTRAFIELDTYPE_RELATION:
									if ($readonly) {
										$value = preg_replace_callback('/\b(\d+)\b/', create_function('$matches', 'return _artifactid2url($matches[1], \'title\');'), $value);
										$cellContent .= $value;
									} else {
										// specific rewrite of cellContent
										$cellContent = '<div style="width:100%; line-height: 20px;">' .
												'<div style="float:left;">'.html_e('strong', array(), $extrafieldObject->getName()._(':')).$mandatory.'</div>' .
												'<div>' . $HTML->getEditFilePic(_('Click to edit'), _('Click to edit'), array('class' => 'mini_buttons tip-ne', 'onclick'=>"switch2edit(this, 'show$keys[0]', 'edit$keys[0]')")).'</div>'.
												'</div>';
										$cellContent .= $ath->renderRelationField($keys[0], $value, $extrafieldObject->getAttribute1(), $extrafieldObject->getAttribute2(), $attrs);
									}
									break;
								case ARTIFACT_EXTRAFIELDTYPE_PARENT:
									if ($readonly) {
										$value = preg_replace_callback('/\b(\d+)\b/', create_function('$matches', 'return _artifactid2url($matches[1], \'title\');'), $value);
										$cellContent .= $value;
									} else {
										// specific rewrite of cellContent
										$cellContent = '<div style="width:100%; line-height: 20px;">' .
												'<div style="float:left;">'.html_e('strong', array(), $extrafieldObject->getName()._(':')).$mandatory.'</div>' .
												'<div>' . $HTML->getEditFilePic(_('Click to edit'), _('Click to edit'), array('class' => 'mini_buttons tip-ne', 'onclick'=>"switch2edit(this, 'show$keys[0]', 'edit$keys[0]')")).'</div>'.
												'</div>';
										$cellContent .= $ath->renderParentField($keys[0], $value, $extrafieldObject->getAttribute1(), $extrafieldObject->getAttribute2(), $attrs);
									}
									break;
								case ARTIFACT_EXTRAFIELDTYPE_INTEGER:
									if ($readonly) {
										$cellContent .= $value;
									} else {
										$cellContent .= $ath->renderIntegerField($keys[0], $value, $extrafieldObject->getAttribute1(), $extrafieldObject->getAttribute2(), $attrs);
									}
									break;
								/* reserved for aljeux extension, for merge into FusionForge */
								case ARTIFACT_EXTRAFIELDTYPE_FORMULA:
									break;
								case ARTIFACT_EXTRAFIELDTYPE_DATETIME:
									if ($readonly) {
										if ($value) {
											$cellContent .= date('Y-m-d H:i', $value);
										} else {
											$cellContent .= _('None');
										}
									} else {
										$cellContent .= $ath->renderDatetime($keys[0], $value, $attrs);
									}
									break;
								/* 13: reserved SLA */
								case ARTIFACT_EXTRAFIELDTYPE_SLA:
									break;
								case ARTIFACT_EXTRAFIELDTYPE_USER:
									if ($readonly) {
										if ($value == 100) {
											$value = _('None');
										} else {
											$user = user_get_object($value);
											$value = $user->getRealName().' ('.html_e('samp', array(), util_make_link_u($user->getUnixname(),$value,$user->getUnixname())).')';
										}
										$cellContent .= $value;
									} else {
										$cellContent .= $ath->renderUserField($keys[0], $value, $extrafieldObject->getShow100(), $extrafieldObject->getShow100label(), false, false, false, $attrs);
									}
									break;
								/* 15: reserved MULTIUSER */
								case ARTIFACT_EXTRAFIELDTYPE_RELEASE:
									if ($readonly) {
										if ($value != 0) {
											$releaseObj = frsrelease_get_object($value);
											if (is_object($releaseObj)) {
												$value = $releaseObj->FRSPackage->getName().' - '.$releaseObj->getName();
												$cellContent .= $value;
											} else {
												$cellContent .= '&nbsp;';
											}
										}
									} else {
										$cellContent .= $ath->renderReleaseField($keys[0], $value, $extrafieldObject->getShow100(), $extrafieldObject->getShow100label(), false, false, false, $attrs);
									}
									break;
								case ARTIFACT_EXTRAFIELDTYPE_EFFORT:
									if ($readonly) {
										if ($value) {
											$effortUnitSet = New EffortUnitSet($ath, $ath->getEffortUnitSet());
											$effortUnitFactory = New EffortUnitFactory($effortUnitSet);
											$cellContent .= $effortUnitFactory->encodedToString($value);
										} else {
											$cellContent .= _('None');
										}
									} else {
										$cellContent .= $ath->renderEffort($keys[0], $value, $extrafieldObject->getAttribute1(), $extrafieldObject->getAttribute2(), $attrs);
									}
									break;
							}
						} else {
							$cellContent = '&nbsp';
						}
					} else {
						$cellContent = '&nbsp;';
					}
					$cells[] = array($cellContent, 'style' => 'vertical-align: top; width: '.$extrafieldID[$keys[0]][0].'%');
				}
				$return .= $HTML->multiTableRow(array(), $cells);
				$return .= $HTML->listTableBottom();
			}
			$return .= $ath->javascript();
			$return .= init_datetimepicker();
			if ($mandatoryDisplay) {
				$return .= $HTML->addRequiredFieldsInfoBox();
			}
		}
		if (!$readonly) {
			$return .= html_e('p', array('class' => 'middleRight'), html_e('input', array('form' => 'trackerform', 'type' => 'submit', 'name' => 'submit', 'value' => _('Save Changes'), 'title' => _('Save is validating the complete form'), 'onClick' => 'iefixform()')));
		}
		return $return;
	}

	function getCategory() {
		return _('Trackers');
	}

	function canBeMinize() {
		return false;
	}

	function hasPreferences() {
		return true;
	}

	function getPreferences() {
		return $this->getPartialPreferencesFormTitle($this->getTitle()).
			$this->buildRenderWidget().
			$this->getAvailableExtraFieldsForm($this->owner_id, true);
	}

	function getPreferencesForm($layout_id, $owner_id, $owner_type) {
		global $HTML;
		global $aid;
		global $func;
		$url = '/widgets/widget.php?owner='.$owner_type.$owner_id.'&action=update&name['.$this->id.']='.$this->getInstanceId().'&content_id='.$this->getInstanceId().'&layout_id='.$layout_id.'&func='.$func;
		if ($aid) {
			$url .= '&aid='.$aid;
		}
		$prefs  = $HTML->openForm(array('method' => 'post', 'action' => $url));
		$prefs .= html_ao('fieldset').html_e('legend', array(), _('Preferences'));
		$prefs .= $this->getPreferences();
		$prefs .= html_e('br');
		$prefs .= html_e('input', array('type' => 'submit', 'name' => 'cancel', 'value' => _('Cancel')));
		$prefs .= html_e('input', array('type' => 'submit', 'name' => 'trackercontent-submit', 'value' => _('Submit')));
		$prefs .= html_ac(html_ap() - 1);
		$prefs .= $HTML->closeForm();
		return $prefs;
	}

	function updatePreferences(&$request) {
		$sanitizer = new TextSanitizer();
		$done = false;
		$vContentId = new Valid_UInt('content_id');
		$vContentId->required();
		if ($request->valid($vContentId)) {
			$vTitle = new Valid_String('title');
			if($request->valid($vTitle)) {
				$title = $sanitizer->SanitizeHtml($request->get('title'));
			} else {
				$title = '';
			}
			$content_id = (int)$request->get('content_id');
			if ($title) {
				$sql = "UPDATE artifact_display_widget SET title = $1 WHERE owner_id =$2 AND id = $3";
				db_query_params($sql,array($title, $this->owner_id, $content_id));
				$done = true;
			}
			$trackerrows = getArrayFromRequest('trackercontent_layout');
			$trackerextrafields = getArrayFromRequest('trackercontent_ef');
			$trackercelltitles = getArrayFromRequest('trackercontent_title');
			db_query_params('DELETE FROM artifact_display_widget_field WHERE id = $1', array($content_id));
			foreach ($trackerrows as $rowkey => $trackerrow) {
				$columns = explode(',', $trackerrow);
				$extrafields = explode(',', $trackerextrafields[$rowkey]);
				$celltitle = explode(',', $trackercelltitles[$rowkey]);
				$rowid = $rowkey;
				foreach ($columns as $columnkey => $column) {
					if ($extrafields[$columnkey] == "fake") {
						$extrafieldid = 0;
					} else {
						$extrafieldid = substr($extrafields[$columnkey], 2); //remove prefix ef
					}
					$section = $sanitizer->SanitizeHtml($celltitle[$columnkey]);
					db_query_params('INSERT INTO artifact_display_widget_field (id, field_id, column_id, row_id, width, section) VALUES ($1, $2, $3, $4, $5, $6)',
							array($content_id, $extrafieldid, $columnkey, $rowid, $column, $section));
				}
				$done = true;
			}
		}
		return $done;
	}
}
