<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2012,2016-2017, Franck Villaume - TrivialDev
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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $g; // Group object
global $group_id; // id of group
global $childgroup_id; // id of child group if any
global $feedback;
global $error_msg;
global $warning_msg;

$urlparam = '/docman/?group_id='.$group_id;

if ($childgroup_id) {
	$g = group_get_object($childgroup_id);
	$urlparam .= '&childgroup_id='.$childgroup_id;
}

if (!forge_check_perm('docman', $g->getID(), 'approve')) {
	$warning_msg = _('Document Manager Action Denied.');
	session_redirect($urlparam);
}

$subaction = getStringFromRequest('subaction', 'version');
$docid = getIntFromRequest('docid');
if (!$docid) {
	$warning_msg = _('No document found to update');
	session_redirect($urlparam);
}
$d = document_get_object($docid, $g->getID());
if ($d->isError()) {
	$error_msg = $d->getErrorMessage();
	session_redirect($urlparam);
}

$doc_group = getIntFromRequest('doc_group');
$fromview = getStringFromRequest('fromview');

switch ($fromview) {
	case 'listrashfile': {
		$urlparam .= '&view='.$fromview;
		break;
	}
	default: {
		$urlparam .= '&dirid='.$doc_group;
		break;
	}
}

$sanitizer = new TextSanitizer();

switch ($subaction) {
	case 'version':
		$title = getStringFromRequest('title');
		$description = getStringFromRequest('description');
		$vcomment = getStringFromRequest('vcomment');
		$details = getStringFromRequest('details');
		$file_url = getStringFromRequest('file_url');
		$uploaded_data = getUploadedFile('uploaded_data');
		$stateid = getIntFromRequest('stateid');
		$filetype = getStringFromRequest('filetype');
		$editor = getStringFromRequest('editor');
		$current_version_radio = getIntFromRequest('doc_version_cv_radio');
		$current_version = getIntFromRequest('current_version', 0);
		$version = getIntFromRequest('edit_version', 0);
		$new_version = getIntFromRequest('new_version', 0);
		$details = $sanitizer->SanitizeHtml($details);
		$data = '';

		if ($version) {
			$dv = documentversion_get_object($version, $docid, $group_id);
			if (($editor) && ($dv->getFileData() != $details) && (!$uploaded_data['name'])) {
				$filename = $dv->getFileName();
				$datafile = tempnam('/tmp', 'docman');
				$fh = fopen($datafile, 'w');
				fwrite($fh, $details);
				fclose($fh);
				$data = $datafile;
				if (!$filetype) {
					$filetype = $dv->getFileType();
				}
			} elseif (!empty($uploaded_data) && $uploaded_data['name']) {
				if (!is_uploaded_file($uploaded_data['tmp_name'])) {
					$error_msg = sprintf(_('Invalid file attack attempt %s.'), $uploaded_data['name']);
					session_redirect($urlparam);
				}
				$data = $uploaded_data['tmp_name'];
				$filename = $uploaded_data['name'];
				if (function_exists('finfo_open')) {
					$finfo = finfo_open(FILEINFO_MIME_TYPE);
					$filetype = finfo_file($finfo, $uploaded_data['tmp_name']);
				} else {
					$filetype = $uploaded_data['type'];
				}
			} elseif ($file_url) {
				$filename = $file_url;
				$filetype = 'URL';
			} else {
				$filename = $dv->getFileName();
				$filetype = $dv->getFileType();
			}
		} elseif ($new_version) {
			if ($editor && $details && $name) {
				$filename = $name;
				$datafile = tempnam('/tmp', 'docman');
				$fh = fopen($datafile, 'w');
				fwrite($fh, $details);
				fclose($fh);
				$data = $datafile;
				if (!$filetype) {
					$filetype = 'text/html';
				}
			} elseif (!empty($uploaded_data) && $uploaded_data['name']) {
				if (!is_uploaded_file($uploaded_data['tmp_name'])) {
					$error_msg = sprintf(_('Invalid file attack attempt %s.'), $uploaded_data['name']);
					session_redirect($urlparam);
				}
				$data = $uploaded_data['tmp_name'];
				$filename = $uploaded_data['name'];
				if (function_exists('finfo_open')) {
					$finfo = finfo_open(FILEINFO_MIME_TYPE);
					$filetype = finfo_file($finfo, $uploaded_data['tmp_name']);
				} else {
					$filetype = $uploaded_data['type'];
				}
			} elseif ($file_url) {
				$filename = $file_url;
				$filetype = 'URL';
			}
		} elseif (($d->getDocGroupID() != $doc_group) || ($d->getStateID() != $stateid)) {
			// we do the update based on the current version.
			if (!$current_version_radio) {
				$current_version_radio = $d->getVersion();
			}
			$dv = documentversion_get_object($current_version_radio, $docid, $group_id);
			$filename = $dv->getFileName();
			$filetype = $dv->getFileType();
			$title = $dv->getTitle();
			$description = $dv->getDescription();
			$vcomment = $dv->getComment();
			$version = $current_version_radio;
			$current_version = 1;
		} else {
			$warning_msg = _('No action to perform');
			session_redirect($urlparam);
		}

		if (!$d->update($filename, $filetype, $data, $doc_group, $title, $description, $stateid, $version, $current_version, $new_version, null, $vcomment)) {
			$error_msg = $d->getErrorMessage();
		} else {
			$feedback = sprintf(_('Document [D%s] updated successfully.'), $d->getID());
		}
		break;
	case 'association':
		$newobjectsassociation = getStringFromRequest('newobjectsassociation');
		if (!$d->addAssociations($newobjectsassociation)) {
			$error_msg = $d->getErrorMessage();
		} else {
			$feedback = sprintf(_('Document [D%s] updated successfully.'), $d->getID());
		}
		break;
	case 'review':
		$reviewtitle = getStringFromRequest('review-title');
		$reviewtitle = $sanitizer->SanitizeHtml($reviewtitle);
		$reviewdescription = getStringFromRequest('review-description');
		$reviewdescription = $sanitizer->SanitizeHtml($reviewdescription);
		$reviewversionserialid = getIntFromRequest('review-serialid', null);
		$reviewenddateraw = getStringFromRequest('review-enddate');
		$date_format = _('%Y-%m-%d');
		$tmp = strptime($reviewenddateraw, $date_format);
		$reviewenddate = mktime(0, 0, 0, $tmp['tm_mon']+1, $tmp['tm_mday'], $tmp['tm_year'] + 1900);
		$reviewmandatoryusers = getArrayFromRequest('review-select-mandatory-users', array());
		$reviewoptionalusers = getArrayFromRequest('review-select-optional-users', array());
		$new_review = getIntFromRequest('new_review');
		$reviewid = getIntFromRequest('review_id');
		$reviewcompletedchecked = getIntFromRequest('review-completedchecked');
		$reviewconclusioncomment = getStringFromRequest('review-completedcomment', '');
		$reviewconclusioncomment = $sanitizer->SanitizeHtml($reviewconclusioncomment);
		$reviewvalidatedocument = getIntFromRequest('review-validatedocument');
		$reviewfinalstatus = getIntFromRequest('review-finalstatus');
		$reviewcurrentversion = getIntFromRequest('review-currentversion');
		$reviewnewcomment = getIntFromRequest('review_newcomment');
		$reviewcomment = getStringFromRequest('review-comment');
		$reviewcomment = $sanitizer->SanitizeHtml($reviewcomment);
		$reviewdone = getIntFromRequest('review-done');
		$reviewnotificationcomment = getStringFromRequest('review-notificationcomment');
		$remindernotification = getStringFromRequest('review-remindernotification');
		if ($reviewversionserialid) {
			if ($new_review) {
				$dr = new DocumentReview($d);
				if ($dr->create($reviewversionserialid, $reviewtitle, $reviewdescription, $reviewenddate, $reviewmandatoryusers, $reviewoptionalusers, $reviewnotificationcomment)) {
					$feedback = _('Review created');
				} else {
					$error_msg = $dr->getErrorMessage();
				}
			} elseif ($reviewnewcomment) {
				$reviewattachment = getUploadedFile('review-attachment');
				if (!empty($reviewattachment) && $reviewattachment['name']) {
					if (!is_uploaded_file($reviewattachment['tmp_name'])) {
						$error_msg = sprintf(_('Invalid file attack attempt %s.'), $reviewattachment['name']);
						session_redirect($urlparam);
					}
					$data = $reviewattachment['tmp_name'];
					$filename = $reviewattachment['name'];
					if (function_exists('finfo_open')) {
						$finfo = finfo_open(FILEINFO_MIME_TYPE);
						$filetype = finfo_file($finfo, $reviewattachment['tmp_name']);
					} else {
						$filetype = $reviewattachment['type'];
					}
				}
				$dr = new DocumentReview($d, $reviewid);
				$drc = new DocumentReviewComment($dr);
				$now = time();
				if ($drc->create(user_getid(), $reviewid, $reviewcomment, $now)) {
					if ($reviewdone) {
						$dr->setUserDone(user_getid(), $now);
					}
					if (isset($filename)) {
						$drc->attachFile($filename, $filetype, $now, $data);
					}
					$feedback = _('Review commented successfully');
				} else {
					$error_msg = $drc->getErrorMessage();
				}
			} elseif ($remindernotification) {
				$dr = new DocumentReview($d, $reviewid);
				if ($dr && !$dr->isError()) {
					$users = $dr->getUsers(array(1));
					if ($dr->sendNotice($users, false, $remindernotification)) {
						$feedback = _('Reminder sent successfully.');
					} else {
						$error_msg = _('No reminder sent for review ID')._(': ').$reviewid;
					}
				} else {
					$error_msg = _('Cannot create object documentreview');
				}
			} else {
				$dr = new DocumentReview($d, $reviewid);
				if ($reviewcompletedchecked) {
					if (strlen($reviewconclusioncomment) > 0) {
						$reviewdescription = $reviewconclusioncomment;
					}
					if ($dr->close($reviewversionserialid, $reviewtitle, $reviewdescription, $reviewfinalstatus, $reviewvalidatedocument, $reviewcurrentversion)) {
						$feedback = _('Review closed successfully');
					} else {
						$error_msg = $dr->getErrorMessage();
					}
				} else {
					if ($dr->update($reviewversionserialid, $reviewtitle, $reviewdescription, $reviewenddate, $reviewmandatoryusers, $reviewoptionalusers)) {
						$feedback = _('Review updated');
					} else {
						$error_msg = $dr->getErrorMessage();
					}
				}
			}
		} else {
			$warning_msg = _('Missing flag action');
		}
		break;
}
session_redirect($urlparam);
