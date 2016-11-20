<?php
/**
 * Generic Tracker Content Widget Class
 *
 * Copyright 2016, Franck Villaume - TrivialDev
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
	var $trackercolumns;

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
		return _("Create an empty widget to link fields together and then organize the artifact display view (update & submit new).");
	}

	function loadContent($id) {
		$this->content_id = $id;
		$this->trackercontent_title = $this->getTitleBlock($id);
	}

	function getTitleBlock($id) {
		$res = db_query_params('select title from artifact_display_widget where id = $1', array($id));
		$title = false;
		if ($res) {
			$arr = db_fetch_array($res);
			$title = $arr[0];
		}
		return $title;
	}

	function create(&$request) {
		$hp = Codendi_HTMLPurifier::instance();
		$this->trackercontent_title = $hp->purify($request->get('title'), CODENDI_PURIFIER_CONVERT_HTML);
		$this->trackercolumns = (int)$request->get('columns');
		$res = db_query_params('INSERT INTO artifact_display_widget (owner_id, title, cols) VALUES ($1, $2, $3)', array($this->owner_id, $this->trackercontent_title, $this->trackercolumns));
		$content_id = db_insertid($res, 'artifact_display_widget', 'id');
		$extrafieldIDs = getArrayFromRequest('extrafieldids');
		$extrafieldIDColumns = getArrayFromRequest('extrafield_column_ids');
		$extrafieldIDRows = getArrayFromRequest('extrafield_row_ids');
		foreach ($extrafieldIDs as $key => $extrafieldID) {
			db_query_params('INSERT INTO artifact_display_widget_field (id, field_id, column_id, row_id) VALUES ($1, $2, $3, $4)', array($content_id, $extrafieldID, $extrafieldIDColumns[$key], $extrafieldIDRows[$key]));
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

	private function getPartialPreferencesFormColumns($column_number) {
		return html_e('p', array(), _('Number of vertical columns')._(':').html_e('input', array('type' => 'number', 'name' => 'columns', 'value' => $column_number, 'step' => 1, 'min' => 1, 'title' => _('Set number of columns to split your box into columns'))));
	}

	private function getExtraFieldsForm($owner_id) {
		global $HTML;
		$atid = $owner_id;
		$artifactTypeObject = artifactType_get_object($atid);
		$availableExtraFields = $artifactTypeObject->getExtraFields();
		$stillAvailableExtraFields = $this->availableExtrafields($owner_id, $availableExtraFields);
		$content = html_e('p', array(), sprintf(_('Tick available ExtraFields from %s to display into this widget'), $artifactTypeObject->getName())._(':'));
		if (count($stillAvailableExtraFields) > 0) {
			$content .= $HTML->listTableTop(array('', '', _('Column ID'), _('Row ID')));
			foreach ($stillAvailableExtraFields as $key => $stillAvailableExtraField) {
				$cells = array();
				$cells[][] = util_unconvert_htmlspecialchars($stillAvailableExtraField['field_name']);
				$cells[][] = html_e('input', array('type' => 'checkbox', 'name' => 'extrafieldids[]', 'value' => $stillAvailableExtraField[0]));
				$cells[][] = html_e('input', array('type' => 'number', 'name' => 'extrafield_column_ids[]', 'value' => 1, 'step' => 1, 'min' => 1, 'title' => _('Set the column number accordingly to the number of columns you created')));
				$cells[][] = html_e('input', array('type' => 'number', 'name' => 'extrafield_row_ids[]', 'value' => 1, 'step' => 1, 'min' => 1, 'title' => _('Set the row number accordingly to organize the extrafields')));
				$content .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($key, true)), $cells);
			}
			$content .= $HTML->listTableBottom();
		}
		return $content;
	}

	private function availableExtrafields($owner_id, $extrafields) {
		$arr = $this->getExtraFieldIDs($owner_id);
		$availableExtrafields = array();
		foreach ($extrafields as $extrafield) {
			if (!in_array($extrafield[0], $arr)) {
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
		$res = db_query_params('select row_id, column_id, field_id from artifact_display_widget_field where id = $1 order by row_id, column_id', array($id));
		$extrafieldIDs = array();
		if ($res && (db_numrows($res) > 0)) {
			while ($arr = db_fetch_array($res)) {
				$extrafieldIDs[$arr[0]][$arr[1]][] = $arr[2];
			}
		}
		return $extrafieldIDs;
	}

	function getInstallPreferences() {
		$request =& HTTPRequest::instance();
		$owner_id = (int)substr($request->get('owner'), 1);
		$content = $this->getPartialPreferencesFormTitle(_('Enter title of Tracker Content Box'));
		$content .= $this->getPartialPreferencesFormColumns(1);
		$content .= $this->getExtraFieldsForm($owner_id);
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
		$layoutExtraFieldIDs = $this->getLayoutExtraFieldIDs($this->content_id);
		$readonly = false;
		if (count($layoutExtraFieldIDs) > 0) {
			$mandatoryDisplay = false;
			$return .= $HTML->listTableTop();
			$selected = array();
			$i = 0;
			if (is_object($ah)) {
				$selected = $ah->getExtraFieldData();
			} elseif ($func = 'add') {
				$selected = $ath->getExtraFieldsDefaultValue();
			}
			if (!forge_check_perm('tracker', $atid, 'submit')) {
				$readonly = true;
			}
			$maxcol = 0;
			foreach ($layoutExtraFieldIDs as $row_id => $column_id) {
				$numcol = 1;
				$cells = array();
				foreach ($column_id as $key => $extrafieldIDs) {
					if ($key != $numcol) {
						$cells[][] = '&nbsp;';
					}
					foreach ($extrafieldIDs as $extrafieldID) {
						$extrafieldObject = new ArtifactExtraField($ath, $extrafieldID);
						if ($func == 'add') {
							$display = !(int)$extrafieldObject->isHiddenOnSubmit();
						} else {
							$display = true;
						}
						if ($display) {
							$value = null;
							$allowed = false;
							if (isset($selected[$extrafieldID])) {
								$value = $selected[$extrafieldID];
							} elseif (isset($extra_fields[$extrafieldID])) {
								$value = $extra_fields[$extrafieldID];
							}
							$attrs = array();
							$mandatory = '';
							if ($extrafieldObject->isRequired() && !$readonly) {
								$mandatory = utils_requiredField();
								$mandatoryDisplay = true;
								$attrs['required'] = 'required';
							}
							if (strlen($extrafieldObject->getDescription()) > 0) {
								$attrs['title'] = $extrafieldObject->getDescription();
							}
							$cellContent = html_e('strong', array(), $extrafieldObject->getName()._(':')).$mandatory.html_e('br');
							switch ($extrafieldObject->getType()) {
								case ARTIFACT_EXTRAFIELDTYPE_SELECT:
									if ($readonly) {
										if ($value == 100) {
											$value = $extrafieldObject->getShow100label();
										} else {
											$value = $ath->getElementName($value);
										}
										$cells[] = array($cellContent.$value, 'style' => 'vertical-align: top;');
									} else {
										$parent = $extrafieldObject->getParent();
										if (!is_null($parent) && !empty($parent) && $parent != '100') {
											$selectedElmnts = (isset($selected[$parent]) ? $selected[$parent] : '');
											$allowed = $aef->getAllowedValues($selectedElmnts);
										}
										$cells[] = array($cellContent.$ath->renderSelect($extrafieldID, $value, $extrafieldObject->getShow100(), $extrafieldObject->getShow100label(), false, false, $allowed, $attrs), 'style' => 'vertical-align: top;');
									}
									break;
								case ARTIFACT_EXTRAFIELDTYPE_CHECKBOX:
									if ($readonly) {
										if ($value == 100) {
											$value = $extrafieldObject->getShow100label();
										} else {
											$value = explode(',', $ath->getElementName($value));
										}
										$cells[] = array($cellContent.join(html_e('br'), $value), 'style' => 'vertical-align: top;');
									} else {
										$parent = $extrafieldObject->getParent();
										if (!is_null($parent) && !empty($parent) && $parent != '100') {
											$selectedElmnts = (isset($selected[$parent]) ? $selected[$parent] : '');
											$allowed = $aef->getAllowedValues($selectedElmnts);
										}
										$cells[] = array($cellContent.$ath->renderCheckbox($extrafieldID, $value, $extrafieldObject->getShow100(), $extrafieldObject->getShow100label(), $allowed, $attrs), 'style' => 'vertical-align: top;');
									}
									break;
								case ARTIFACT_EXTRAFIELDTYPE_RADIO:
									if ($readonly) {
										if ($value == 100) {
											$value = $extrafieldObject->getShow100label();
										} else {
											$value = $ath->getElementName($value);
										}
										$cells[] = array($cellContent.$value, 'style' => 'vertical-align: top;');
									} else {
										$parent = $extrafieldObject->getParent();
										if (!is_null($parent) && !empty($parent) && $parent != '100') {
											$selectedElmnts = (isset($selected[$parent]) ? $selected[$parent] : '');
											$allowed = $aef->getAllowedValues($selectedElmnts);
										}
										$cells[] = array($cellContent.$ath->renderRadio($extrafieldID, $value, $extrafieldObject->getShow100(), $extrafieldObject->getShow100label(), false, false, $allowed, $attrs), 'style' => 'vertical-align: top;');
									}
									break;
								case ARTIFACT_EXTRAFIELDTYPE_TEXT:
									if ($readonly) {
										if (strlen($value) > 0) {
											$value = preg_replace('/((http|https|ftp):\/\/\S+)/', "<a href=\"\\1\" target=\"_blank\">\\1</a>", $value);
										} else {
											$value = '&nbsp;';
										}
										$cells[] = array($cellContent.$value, 'style' => 'vertical-align: top;');
									} else {
										$attrs['style'] = 'box-sizing: border-box; width: 100%';
										if ($extrafieldObject->getPattern()) {
											$attrs['pattern'] = $extrafieldObject->getPattern();
										}
										$cells[] = array($cellContent.$ath->renderTextField($extrafieldID, $value, $extrafieldObject->getAttribute1(), $extrafieldObject->getAttribute2(), $attrs), 'style' => 'vertical-align: top;');
									}
									break;
								case ARTIFACT_EXTRAFIELDTYPE_MULTISELECT:
									if ($readonly) {
										if ($value == 100) {
											$value = $extrafieldObject->getShow100label();
										} else {
											$value = explode(',', $ath->getElementName($value));
										}
										$cells[] = array($cellContent.join(html_e('br'), $value), 'style' => 'vertical-align: top;');
									} else {
										$parent = $extrafieldObject->getParent();
										if (!is_null($parent) && !empty($parent) && $parent != '100') {
											$selectedElmnts = (isset($selected[$parent]) ? $selected[$parent] : '');
											$allowed = $aef->getAllowedValues($selectedElmnts);
										}
										$cells[] = array($cellContent.$ath->renderMultiSelectBox($extrafieldID, $value, $extrafieldObject->getShow100(), $extrafieldObject->getShow100label(), $allowed, $attrs), 'style' => 'vertical-align: top;');
									}
									break;
								case ARTIFACT_EXTRAFIELDTYPE_TEXTAREA:
									if ($readonly) {
										if (strlen($value) > 0) {
											$value = preg_replace('/((http|https|ftp):\/\/\S+)/', "<a href=\"\\1\" target=\"_blank\">\\1</a>", $value);
										} else {
											$value = '&nbsp;';
										}
										$cells[] = array($cellContent.$value, 'style' => 'vertical-align: top;');
									} else {
										$attrs['style'] = 'box-sizing: border-box; width: 100%';
										$cells[] = array($cellContent.$ath->renderTextArea($extrafieldID, $value, $extrafieldObject->getAttribute1(), $extrafieldObject->getAttribute2(), $attrs), 'style' => 'vertical-align: top;');
									}
									break;
								case ARTIFACT_EXTRAFIELDTYPE_STATUS:
									if ($readonly) {
										if ($value == 100) {
											$value = $extrafieldObject->getShow100label();
										} else {
											$value = $ath->getElementName($value);
										}
										$cells[] = array($cellContent.$value, 'style' => 'vertical-align: top;');
									} else {
										$atw = new ArtifactWorkflow($ath, $extrafieldID);
										// Special treatment for the initial step (Submit). In this case, the initial value is the first value.
										if (!$value) {
											$value = 100;
										}
										$allowed = $atw->getNextNodes($value);
										$allowed[] = $value;
										$cells[] = array($cellContent.$ath->renderSelect($extrafieldID, $value, false, $extrafieldObject->getShow100label(), false, false, $allowed, $attrs), 'style' => 'vertical-align: top;');
									}
									break;
								//case ARTIFACT_EXTRAFIELDTYPE_ASSIGNEE:
								case ARTIFACT_EXTRAFIELDTYPE_RELATION:
									if ($readonly) {
										$value = preg_replace_callback('/\b(\d+)\b/', create_function('$matches', 'return _artifactid2url($matches[1], \'title\');'), $value);
										$cells[] = array($cellContent.$value, 'style' => 'vertical-align: top;');
									} else {
										// specific rewrite of cellContent
										$cellContent = '<div style="width:100%; line-height: 20px;">' .
												'<div style="float:left;">'.html_e('strong', array(), $extrafieldObject->getName()._(':')).$mandatory.'</div>' .
												'<div>' . $HTML->getEditFilePic(_('Click to edit'), _('Click to edit'), array('class' => 'mini_buttons tip-ne', 'onclick'=>"switch2edit(this, 'show$extrafieldID', 'edit$extrafieldID')")) . '</div>' .
												'</div>';
										$cells[] = array($cellContent.$ath->renderRelationField($extrafieldID, $value, $extrafieldObject->getAttribute1(), $extrafieldObject->getAttribute2(), $attrs), 'style' => 'vertical-align: top;');
									}
									break;
								case ARTIFACT_EXTRAFIELDTYPE_INTEGER:
									if ($readonly) {
										$cells[] = array($cellContent.$value, 'style' => 'vertical-align: top;');
									} else {
										$cells[] = array($cellContent.$ath->renderIntegerField($extrafieldID, $value, $extrafieldObject->getAttribute1(), $extrafieldObject->getAttribute2(), $attrs), 'style' => 'vertical-align: top;');
									}
									break;
								/* reserved for aljeux extension, for merge into FusionForge */
								case ARTIFACT_EXTRAFIELDTYPE_FORMULA:
									break;
								/* reserved for Evolvis extension, for merge into FusionForge */
								case ARTIFACT_EXTRAFIELDTYPE_DATETIME:
									if ($readonly) {
										$cells[] = array($cellContent.$value, 'style' => 'vertical-align: top;');
									} else {
										$cells[] = array($cellContent.$ath->renderDatetime($extrafieldID, $value, $attrs), 'style' => 'vertical-align: top;');
									}
									break;
								/* 12: reserved DATETIME*/
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
										$cells[] = array($cellContent.$value, 'style' => 'vertical-align: top;');
									} else {
										$cells[] = array($cellContent.$ath->renderUserField($extrafieldID, $value, $extrafieldObject->getShow100(), $extrafieldObject->getShow100label(), false, false, false, $attrs), 'style' => 'vertical-align: top;');
									}
									break;
								/* 15: reserved MULTIUSER */
								case ARTIFACT_EXTRAFIELDTYPE_RELEASE:
									if ($readonly) {
										if ($value != 0) {
											$releaseObj = frsrelease_get_object($value);
											$value = $releaseObj->FRSPackage->getName().' - '.$releaseObj->getName();
										}
										$cells[] = array($cellContent.$value, 'style' => 'vertical-align: top;');
									} else {
										$cells[] = array($cellContent.$ath->renderReleaseField($extrafieldID, $value, $extrafieldObject->getShow100(), $extrafieldObject->getShow100label(), false, false, false, $attrs), 'style' => 'vertical-align: top;');
									}
									break;
							}
						}
					}
					$numcol++;
					if ($numcol > $maxcol) {
						$maxcol = $numcol;
					}
				}
				while (count($cells) < ($maxcol -1)) {
					$cells[][] = '&nbsp;';
				}
				$return .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i++, true)), $cells);
			}
			$return .= $HTML->listTableBottom();
			$return .= $ath->javascript();
			$return .= init_datetimepicker();
			if ($mandatoryDisplay) {
				$return .= $HTML->addRequiredFieldsInfoBox();
			}
		}
		if (!$readonly) {
			$return .= html_e('p', array('class' => 'middleRight'), html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Save Changes'), 'title' => _('Save is validating the complete form'))));
		}
		return $return;
	}

	function getCategory() {
		return _('Trackers');
	}

	function canBeMinize() {
		return false;
	}
}
