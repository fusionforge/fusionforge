#! /usr/bin/php -f
<?php
/**
 * Copyright 2016,2017, Franck Villaume - TrivialDev
 * Copyright 2016, Stéphane-Eymeric Bredthauer - TrivialDev
 * http://fusionforge.org
 *
 * This file is a part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge. If not, see <http://www.gnu.org/licenses/>.
 */

require (dirname(__FILE__).'/../common/include/env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'docman/Document.class.php';

define('MAXSIZE__USER_UNIXNAME', 32);
define('MAXSIZE__GROUP_UNIXNAME', 15);
define('MAXSIZE__GROUP_PROJECTNAME', 40);
define('MAXSIZE__DOCGROUP_NAME', 254);
define('MINSIZE__DOCUMENT_DESCRIPTION', 10);
define('MAXSIZE__DOCUMENT_DESCRIPTION', 254);
define('MAXSIZE__DOCUMENT_TITLE', 254);
define('MINSIZE__DOCUMENT_TITLE', 5);
define('MAXSIZE__DOCUMENT_VCOMMENT', 200);
define('MINSIZE__FRS_PACKAGE_NAME', 3);

// table ctf_mapping format
// xid | ffobject || ffid
// xid = ctf object unique id
// ffobject = type of fusionforge object created
// ffid = id of the fusionforge object created
// this table is used to identify which object has been injected in FusionForge

$ff_ctf_mapping = array();

$usage = 'Usage: '.$argv[0].' /path/where/is/located/the/splitted/project/xml'."\n";
if ($argc != 2) {
	echo 'Path parameter is missing'."\n";
	echo $usage;
	exit(1);
}
$project_path = $argv[1];

$testres = db_query_params('select count(*) as count from ctf_mapping', array());
if (!$testres) {
	$res = db_query_params('CREATE TABLE "ctf_mapping" (
					"xid" text,
					"ffobject" text,
					"ffid" integer)',
				array());
	if ($res) {
		echo 'New table ctf_mapping created'."\n";
	} else {
		echo 'No database access'."\n";
		exit(2);
	}
}
db_query_params('DELETE FROM ctf_mapping where ffobject != $1', array('User'));
echo 'DB check OK'."\n";

echo 'Get default admin user'."\n";
$admins = RBACEngine::getInstance()->getUsersByAllowedAction("forge_admin", -1);
$adminUser = $admins[0];
if (!$adminUser || $adminUser->isError()) {
	echo 'Cannot get default admin user'."\n";
	exit(3);
}
// let start a new session with default user.
session_set_new($adminUser->getID());
if (is_dir($project_path.'/project')) {
	db_begin();
	echo 'project found to create or update'."\n";
	if (is_file($project_path.'/project/project.xml')) {
		echo 'found xml file to parse'."\n";
		$simpleXmlLoadedFile = simplexml_load_file($project_path.'/project/project.xml');
		if ($simpleXmlLoadedFile !== false) {
			// do not inject deleted project!
			if ($simpleXmlLoadedFile->isDeleted == "true") {
				echo 'You are trying to inject deleted project! Aborting'."\n";
				echo 'Operation not supported'."\n";
				db_rollback();
				exit(10);
			}
			// generate a uniq project name using microtime
			$uniq_project_name = microtime(true);
			$uniq_project_name = preg_replace('/\./', '', $uniq_project_name);
			// warning: underscore is forbidden by dns, therefore we replace it by dash
			//$unixname = preg_replace('/_/','-',trim((string)$simpleXmlLoadedFile->name));
			// ^- the above method does not generate an unique name...
			$unixname = 'p'.$uniq_project_name;
			// warning: max MAXSIZE__GROUP_UNIXNAME chars.
			if (strlen($unixname) > MAXSIZE__GROUP_UNIXNAME) {
				echo 'Warning! project unixname too long. '.MAXSIZE__GROUP_UNIXNAME.' chars max. Shorten to '.MAXSIZE__GROUP_UNIXNAME.' chars.'."\n";
				$unixname = substr($unixname, 0, MAXSIZE__GROUP_UNIXNAME);
			}
			// check if another project exists with the same name
			$ctfUnixname = group_get_object_by_name($unixname);
			if ($ctfUnixname && !$ctfUnixname->isError()) {
				echo 'You are willing to inject this project on a forge containing the same project unixname! Aborting'."\n";
				echo 'Project unixname must be unique'."\n";
				db_rollback();
				exit(11);
			}
			$fullname = trim((string)$simpleXmlLoadedFile->title);
			if (strlen($fullname) > MAXSIZE__GROUP_PROJECTNAME) {
				echo 'Warning! project title name too long. '.MAXSIZE__GROUP_PROJECTNAME.' chars max. Shorten to '.MAXSIZE__GROUP_PROJECTNAME.' chars.'."\n";
				$fullname = substr($fullname, 0, MAXSIZE__GROUP_PROJECTNAME);
			}
			$description = trim((string)$simpleXmlLoadedFile->description);
			$is_public = trim((string)$simpleXmlLoadedFile['accessLevel']);
			$username = trim((string)$simpleXmlLoadedFile->createdByUsername);
			$ctfxid = trim((string)$simpleXmlLoadedFile['xid']);
			$status = trim((string)$simpleXmlLoadedFile->status);
			// check if username exists in database. If not, then use the default admin account
			// warning if $username is > MAXSIZE__USER_UNIXNAME chars, we need to shorten the $username to MAXSIZE__USER_UNIXNAME chars.
			if (strlen($username) > MAXSIZE__USER_UNIXNAME) {
				echo 'Information: username used to create project is too long, shorten to '.MAXSIZE__USER_UNIXNAME.' chars first!'."\n";
				$username = substr($username, 0, MAXSIZE__USER_UNIXNAME);
			}
			$ctfUser = user_get_object_by_name($username);
			if (!$ctfUser || $ctfUser->isError()) {
				echo 'User: '.$username.' used to create the project does not exist in the forge!'."\n";
				$ctfUser = $adminUser;
				echo 'Information: fallback to default admin account: '.$ctfUser->getUnixName()."\n";
			}
			// now create the Group in DB
			$g = new Group();
			$r = $g->create($ctfUser, $fullname, $unixname, $description, 'Project injected into the database using CTF forklift XML export',
					'shell', 'scm', $is_public, false);

			if (!$r) {
				echo 'Error: '.$g->getErrorMessage()."\n";
				if (isset($GLOBALS['register_error'])) {
					echo 'More error message: '.$GLOBALS['register_error']."\n";
				}
				db_rollback();
				exit(12);
			}
			$resxid = db_query_params('insert into ctf_mapping (xid, ffobject, ffid) values ($1, $2, $3)',
						array($ctfxid, 'Group', $g->getID()));
			if (!$resxid) {
				echo 'Error: '.db_error()."\n";
				db_rollback();
				exit(13);
			}

			// approve project according to XML status value
			if ($status == 'Active') {
				$r = $g->approve($adminUser);
				if (!$r) {
					echo 'Error: '.$g->getErrorMessage()."\n";
					db_rollback();
					exit(14);
				}
			}
			// is a template project?
			$istemplate = (boolean)trim((string)$simpleXmlLoadedFile->isTemplate);
			$g->setAsTemplate($istemplate);
		} else {
			echo 'Cannot load project XML file'."\n";
			db_rollback();
			exit(15);
		}
	}
	db_commit();
}

if (is_file($project_path.'/user.xml')) {
	$ff_ctf_mapping['user'] = array();
	$themeId = getThemeIdFromName(forge_get_config('default_theme'));
	if (!$themeId) {
		echo 'Error: missing theme id! Please setup default theme in your configuration file'."\n";
		exit(4);
	}
	db_begin();
	echo 'users found to inject'."\n";
	$simpleXmlLoadedFile = simplexml_load_file($project_path.'/user.xml');
	if ($simpleXmlLoadedFile !== false) {
		$xmlObjectUsersArray = $simpleXmlLoadedFile->users->sfuser;
		foreach ($xmlObjectUsersArray as $xmlObjectUser) {
			$keep = true;
			$add_role = false;
			$user_status = trim((string)$xmlObjectUser->status);
			$user_username = strtolower(trim((string)$xmlObjectUser->username));
			$original_username = $user_username;
			$user_createdate = strtotime(trim((string)$xmlObjectUser->dateCreated));
			// We only support active user.
			// if another object depends on a deleted/removed user, we will use the "Nobody" user.
			if ($user_status != 'Active') {
				if (preg_match('/d-.*[0-9]{6}/', $user_username)) {
					$user_username = substr($user_username, 2);
					$underscore = strpos($user_username, '_');
					$user_username = substr($user_username, 0, $underscore);
				}
			}
			// $user_username must be 15 chars max!
			if (strlen($user_username) > MAXSIZE__USER_UNIXNAME) {
				echo 'Warning: username '.$user_username.' too long! '.MAXSIZE__USER_UNIXNAME.' chars max. removing extra chars'."\n";
				$user_username = substr($user_username,0,MAXSIZE__USER_UNIXNAME);
			}
			// Check if another user with same unixname exists. Unixname must be unique!
			$userObject = user_get_object_by_name($user_username);
			if (is_object($userObject) && !$userObject->isError()) {
				echo 'User '.$user_username.' already existing. Skipped!'."\n";
				$keep = false;
				$add_role = true;
			}
			if ($keep) {
				$user_email = (string)$xmlObjectUser->email;
				if (forge_get_config('require_unique_email')) {
					$userObject = user_get_object_by_email($user_email);
					if (is_object($userObject) && !$userObject->isError()) {
						echo 'User '.$user_username.' with email '.$user_email.' existing. Skipped!'."\n";
						$keep = false;
					}
				}
			}

			if ($keep) {
				$user_fullname = trim((string)$xmlObjectUser->fullName);
				// let's split that by default into 2 parts. First part will be firstname...
				// if no second part, then set is to "unknown"
				$user_fullnameSplitted = explode(' ', $user_fullname, 2);
				$user_firstname = $user_fullnameSplitted[0];
				if (isset($user_fullnameSplitted[1])) {
					$user_lastname = $user_fullnameSplitted[1];
				} else {
					$user_lastname = 'unknown';
				}
				$user_isSuperUser = (string)$xmlObjectUser->isSuperUser;
				$user_locale = (string)$xmlObjectUser->locale;
				// check for language. if not found, set default to en
				$res = db_query_params('select * from supported_languages where language_code = $1', array($user_locale));
				if ($res) {
					if (db_numrows($res) == 0) {
						$user_language = 1;
					} else {
						$user_language = db_result($res, 0, 'language_id');
					}
				}
				$user_timezone = (string)$xmlObjectUser->timeZone;
				$user_xid = (string)$xmlObjectUser['xid'];
				$user_default_password = 'user1234'; // how to set real value ? should be overwritten by ldap anyway
				$userObject = new FFUser();
				$r = $userObject->create($user_username, $user_firstname, $user_lastname,
							$user_default_password, $user_default_password, $user_email,
							1, 0, $user_language, $user_timezone, '', '',
							$themeId, 'shell', '', '', '', '', '', 'US', false, true, $user_createdate);
				if (!$r) {
					echo 'Error: '.$user_username.' '.$userObject->getErrorMessage()."\n";
					if (isset($GLOBALS['register_error'])) {
						echo 'More error message: '.$GLOBALS['register_error']."\n";
					}
					db_rollback();
					exit(5);
				}
				$userObject->setStatus('A');
				$resxid = db_query_params('insert into ctf_mapping (xid, ffobject, ffid) values ($1, $2, $3)',
							array($user_xid, 'User', $userObject->getID()));
				if (!$resxid) {
					echo 'Error: '.db_error()."\n";
					db_rollback();
					exit(6);
				}
				echo 'User '.$user_username.' injected and actived'."\n";
				$add_role = true;
			}
			if ($add_role) {
				$roles = RBACEngine::getInstance()->getRolesByAllowedAction('project_admin', $g->getID());
				foreach ($roles as $role) {
					if ($role instanceof RoleExplicit) {
						continue;
					} else {
						// by default there is only 1 admin role.
						$role->addUser($userObject);
						$default_added_role_to_user = $role;
						break;
					}
				}
			}
			$ff_ctf_mapping['user'][$original_username] = $user_username;
		}
	}
	db_commit();
}

if (is_dir($project_path.'/project')) {
	if (is_dir($project_path.'/project/roles')) {
		if (is_file($project_path.'/project/roles/roles.xml')) {
			$role_first_compute = false;
			echo 'found roles to be injected into the project'."\n";
			$simpleXmlLoadedFile = simplexml_load_file($project_path.'/project/roles/roles.xml');
			if ($simpleXmlLoadedFile !== false) {
				$roles = $simpleXmlLoadedFile->role;
				if ($roles) {
					// we have roles to create then delete default one.
					$existing_roles = $g->getRoles();
					foreach ($existing_roles as $existing_role) {
						$existing_role->delete();
					}
				}
				foreach ($roles as $role) {
					$role_name = (string)$role->title;
					$role_xid = (string)$role['xid'];
					$permissionPerSectionArray = array();
					$permissionPerSectionArray['project_read'][$g->getID()] = 1;
					$ctfpermissionsSettings = $role->operationClusters->operation_cluster;
					foreach ($ctfpermissionsSettings as $ctfpermissionsSetting) {
						$ctfpermissionSetting = (string)$ctfpermissionsSetting->clusterName;
						$ctfpermissionSettingFolder = (string)$ctfpermissionsSetting->resourceName;
						if ($ctfpermissionSettingFolder == '*') {
							switch ($ctfpermissionSetting) {
								case 'docman_admin': // fusionforge value: docman 4
									if (forge_get_config('use_docman') && $g->usesDocman())
										$permissionPerSectionArray['docman'][$g->getID()] = 4;
									break;
								case 'docman_create': // fusionforge value: docman 3
								case 'docman_edit': // fusionforge value: docman 3
								case 'docman_delete': // fusionforge value: docman 3
									if (forge_get_config('use_docman') && $g->usesDocman()) {
										if ((isset($permissionPerSectionArray['docman'][$g->getID()]) && $permissionPerSectionArray['docman'][$g->getID()] < 3)
											|| (!isset($permissionPerSectionArray['docman'][$g->getID()]))) {
											$permissionPerSectionArray['docman'][$g->getID()] = 3;
										}
									}
									break;
								case 'docman_view': // fusionforge value: docman 1
									if (forge_get_config('use_docman') && $g->usesDocman()) {
										if ((isset($permissionPerSectionArray['docman'][$g->getID()]) && $permissionPerSectionArray['docman'][$g->getID()] < 1)
											|| (!isset($permissionPerSectionArray['docman'][$g->getID()]))) {
											$permissionPerSectionArray['docman'][$g->getID()] = 1;
										}
									}
									break;
								case 'scm_admin': // fusionforge value: project_admin 1
									if (forge_get_config('use_scm') && $g->usesSCM()) {
										$permissionPerSectionArray['project_admin'][$g->getID()] = 1;
									}
									break;
								case 'scm_commit': // fusionforge value: scm 2
								case 'scm_delete': // fusionforge value: scm 2
									if (forge_get_config('use_scm') && $g->usesSCM()) {
										if ((isset($permissionPerSectionArray['scm'][$g->getID()]) && $permissionPerSectionArray['scm'][$g->getID()] < 1)
											|| (!isset($permissionPerSectionArray['scm'][$g->getID()]))) {
											$permissionPerSectionArray['scm'][$g->getID()] = 2;
										}
									}
									break;
								case 'scm_view': // fusionforge value: scm 1
									if (forge_get_config('use_scm') && $g->usesSCM()) {
										if ((isset($permissionPerSectionArray['scm'][$g->getID()]) && $permissionPerSectionArray['scm'][$g->getID()] < 1)
											|| (!isset($permissionPerSectionArray['scm'][$g->getID()]))) {
											$permissionPerSectionArray['scm'][$g->getID()] = 1;
										}
									}
									break;
								case 'frs_admin': // fusionforge value: frs_admin 2 + new_frs 4 (package administrator)
								case 'frs_delete':
								case 'frs_create':
									if (forge_get_config('use_frs') && $g->usesFRS()) {
										$permissionPerSectionArray['frs_admin'][$g->getID()] = 2;
										$permissionPerSectionArray['new_frs'][$g->getID()] = 4;
									}
									break;
								case 'frs_view': // fusionforge value: frs_admin = 1 + new_frs = 1 (read only)
									if (forge_get_config('use_frs') && $g->usesFRS()) {
										$permissionPerSectionArray['frs_admin'][$g->getID()] = 1;
										$permissionPerSectionArray['new_frs'][$g->getID()] = 1;
									}
									break;
								case 'discussion_admin': // fusionforge value: forum_admin 1 + new_forum 4 (forum moderator)
								case 'discussion_delete':
									if (forge_get_config('use_forum') && $g->usesForum()) {
										$permissionPerSectionArray['forum_admin'][$g->getID()] = 1;
										$permissionPerSectionArray['new_forum'][$g->getID()] = 4;
									}
									break;
								case 'discussion_participate': // fusionforge value: forum_admin = 0 + new_forum = 2 (moderated post)
									if (forge_get_config('use_forum') && $g->usesForum()) {
										$permissionPerSectionArray['forum_admin'][$g->getID()] = 1;
										$permissionPerSectionArray['new_forum'][$g->getID()] = 2;
									}
									break;
								case 'discussion_view': // fusionforge value: forum_admin = 0 + new_forum = 1 (read only)
									if (forge_get_config('use_forum') && $g->usesForum()) {
										$permissionPerSectionArray['forum_admin'][$g->getID()] = 1;
										$permissionPerSectionArray['new_forum'][$g->getID()] = 1;
									}
									break;
								case 'tracker_admin': // fusionforge value: tracker_admin 1 + new_tracker = 15 (manager + tech without vote)
									if (forge_get_config('use_tracker') && $g->usesTracker()) {
										$permissionPerSectionArray['tracker_admin'][$g->getID()] = 1;
										$permissionPerSectionArray['new_tracker'][$g->getID()] = 15;
									}
									break;
								case 'tracker_create': // fusionforge value: tracker_admin = 0 + new_tracker = 11 (technician)
								case 'tracker_delete':
								case 'tracker_edit':
									if (forge_get_config('use_tracker') && $g->usesTracker()) {
										$permissionPerSectionArray['tracker_admin'][$g->getID()] = 0;
										$permissionPerSectionArray['new_tracker'][$g->getID()] = 11;
									}
									break;
								case 'tracker_view': // fusionforge value: tracker_admin = 0 + new_tracker = 1 (read only)
									if (forge_get_config('use_tracker') && $g->usesTracker()) {
										$permissionPerSectionArray['tracker_admin'][$g->getID()] = 0;
										$permissionPerSectionArray['new_tracker'][$g->getID()] = 1;
									}
									break;
								case 'taskmgr_admin': // fusionforge value: pm_admin = 1 + new_pm = 7 (tech + manager)
								case 'taskmgr_delete':
									if (forge_get_config('use_pm') && $g->usesPM()) {
										$permissionPerSectionArray['pm_admin'][$g->getID()] = 1;
										$permissionPerSectionArray['new_pm'][$g->getID()] = 7;
									}
									break;
								default:
									echo 'Role permission setting '.$ctfpermissionSetting.' unknown? To be implemented'."\n";
									break;
							}
						}
					}
					$newrole = new Role($g);
					if ($newrole->create($role_name, $permissionPerSectionArray)) {
						echo 'New role '.$role_name.' created'."\n";
						$role_members = $role->roleUsers->role_user;
						foreach ($role_members as $role_member) {
							$member_name = (string)$role_member->username;
							// check user really exists before binding it to a role
							if (strlen($member_name) > MAXSIZE__USER_UNIXNAME)
								$member_name = substr($member_name, 0, MAXSIZE__USER_UNIXNAME);
							$member_nameObject = user_get_object_by_name($member_name);
							if ($member_nameObject && !$member_nameObject->isError()) {
								if ($newrole->addUser($member_nameObject)) {
									echo 'User '.$member_name.' binded to role '.$role_name."\n";
								} else {
									echo 'Cannot bind user '.$member_name.' to this role '.$role_name."\n";
								}
							} else {
								echo 'User '.$member_name.' does not exist! Cannot bind it to this role '.$role_name."\n";
							}
						}
						$resxid = db_query_params('insert into ctf_mapping (xid, ffobject, ffid) values ($1, $2, $3)',
									array($role_xid, 'Role', $newrole->getID()));
						echo $role_name.' injected'."\n";
					} else {
						echo 'Cannot create new role '.$role_name."\n";
					}
				}
				$role_first_compute = true;
			}
		}
	}
	if (is_dir($project_path.'/project/applications')) {
		echo 'found features to setup or update for this project'."\n";
		$dummyFeatureStatusArr = array();
		if (is_file($project_path.'/project/applications/applications.xml')) {
			echo 'found xml to parse with dummy features to enable without any data'."\n";
			$simpleXmlLoadedFile = simplexml_load_file($project_path.'/project/applications/applications.xml');
			if ($simpleXmlLoadedFile !== false) {
				foreach ($simpleXmlLoadedFile as $key => $value) {
					$dummyFeatureStatusArr[(string)$key] = true;
				}
			} else {
				echo 'Cannot load applications XML file'."\n";
				exit(20);
			}
		}
		$dirContentArray = scandir($project_path.'/project/applications');
		if ($dirContentArray) {
			if (count($dirContentArray) > 0) {
				$mergeFeaturesArray = array_merge($dirContentArray, $dummyFeatureStatusArr);
			} else {
				$mergeFeaturesArray = $dummyFeatureStatusArr;
			}
			foreach ($mergeFeaturesArray as $contentElement) {
				$feature_keep = true;
				if ($contentElement == '.' || $contentElement == '..' || $contentElement == 'applications.xml')
					$feature_keep = false;

				if ($feature_keep) {
					enableFeature($g, $contentElement);
					if (is_dir($project_path.'/project/applications/'.$contentElement)) {
						if (is_file($project_path.'/project/applications/'.$contentElement.'/'.$contentElement.'.xml')) {
							computeXmlApplication($g, $contentElement, $project_path);
						}
					}
				}
			}
		} else {
			echo 'Unable to list files & directories in '.$project_path.'/project/applications'."\n";
			exit(21);
		}
	}
	// second computation for roles on specific sections that requires to be create first before setting permissions.
	if (is_dir($project_path.'/project/roles')) {
		if (is_file($project_path.'/project/roles/roles.xml') && $role_first_compute) {
			$simpleXmlLoadedFile = simplexml_load_file($project_path.'/project/roles/roles.xml');
			if ($simpleXmlLoadedFile !== false) {
				echo 'second computation of role to attribute correct permission to role based on injected objects'."\n";
				$roles = $simpleXmlLoadedFile->role;
				foreach ($roles as $role) {
					$role_xid = (string)$role['xid'];
					$roleFFId = get_ff_id('Role', $role_xid);
					if ($roleFFId) {
						$roleFFObject = new Role($g, $roleFFId);
						if ($roleFFObject && !$roleFFObject->isError()) {
							$permissionPerSectionArray = $roleFFObject->getSettingsForProject($g);
							$ctfpermissionsSettings = $role->operationClusters->operation_cluster;
							foreach ($ctfpermissionsSettings as $ctfpermissionsSetting) {
								$ctfpermissionSetting = (string)$ctfpermissionsSetting->clusterName;
								$ctfpermissionSettingFolder = (string)$ctfpermissionsSetting->ressourceName;
								if ($ctfpermissionSettingFolder != '*') {
									$ctfpermissionSettingRessource = (string)$ctfpermissionsSetting->resourceValue;
									$ffid = null;
									switch ($ctfpermissionSetting) {
										case 'docman_admin': // fusionforge value: docman 4; Warning: this is FusionForge specific. There is no permission per folder. We overwrite the general value if missing.
											if (forge_get_config('use_docman') && $g->usesDocman())
												$permissionPerSectionArray['docman'][$g->getID()] = 4;
											break;
										case 'docman_create': // fusionforge value: docman 3; Warning: this is FusionForge specific. There is no permission per folder. We overwrite the general value if missing.
										case 'docman_edit':
										case 'docman_delete':
											if (forge_get_config('use_docman') && $g->usesDocman()) {
												if ((isset($permissionPerSectionArray['docman'][$g->getID()]) && $permissionPerSectionArray['docman'][$g->getID()] < 3)
													|| (!isset($permissionPerSectionArray['docman'][$g->getID()]))) {
													$permissionPerSectionArray['docman'][$g->getID()] = 3;
												}
											}
											break;
										case 'docman_view': // fusionforge value: docman 1; Warning: this is FusionForge specific. There is no permission per folder. We overwrite the general value if missing.
											if (forge_get_config('use_docman') && $g->usesDocman()) {
												if ((isset($permissionPerSectionArray['docman'][$g->getID()]) && $permissionPerSectionArray['docman'][$g->getID()] < 1)
													|| (!isset($permissionPerSectionArray['docman'][$g->getID()]))) {
													$permissionPerSectionArray['docman'][$g->getID()] = 1;
												}
											}
											break;
										case 'scm_admin': // fusionforge value: project_admin 1; Warning: this is FusionForge specific. There is no permission as scm_admin. We overwrite the general value if missing.
											if (forge_get_config('use_scm') && $g->usesSCM()) {
												$permissionPerSectionArray['project_admin'][$g->getID()] = 1;
											}
											break;
										case 'scm_commit': // fusionforge value: scm 2; Warning: this is FusionForge specific. There is no permission per repository. We overwrite the general value if missing.
										case 'scm_delete':
											if (forge_get_config('use_scm') && $g->usesSCM()) {
												if ((isset($permissionPerSectionArray['scm'][$g->getID()]) && $permissionPerSectionArray['scm'][$g->getID()] < 1)
													|| (!isset($permissionPerSectionArray['scm'][$g->getID()]))) {
													$permissionPerSectionArray['scm'][$g->getID()] = 2;
												}
											}
											break;
										case 'scm_view': // fusionforge value: scm 1; Warning: this is FusionForge specific. There is no permission per repository. We overwrite the general value if missing.
											if (forge_get_config('use_scm') && $g->usesSCM()) {
												if ((isset($permissionPerSectionArray['scm'][$g->getID()]) && $permissionPerSectionArray['scm'][$g->getID()] < 1)
													|| (!isset($permissionPerSectionArray['scm'][$g->getID()]))) {
													$permissionPerSectionArray['scm'][$g->getID()] = 1;
												}
											}
											break;
										case 'frs_admin': // fusionforge value: frs 4 (package administrator)
										case 'frs_delete':
										case 'frs_create':
											if (forge_get_config('use_frs') && $g->usesFRS()) {
												set_permission_in_role($permissionPerSectionArray, $ctfpermissionSettingRessource, 'FRSPackage', 'frs', 4);
											}
											break;
										case 'frs_view': // fusionforge value: frs_admin = 1 + new_frs = 1 (read only)
											if (forge_get_config('use_frs') && $g->usesFRS()) {
												set_permission_in_role($permissionPerSectionArray, $ctfpermissionSettingRessource, 'FRSPackage', 'frs', 1);
											}
											break;
										case 'discussion_admin': // fusionforge value: forum 4 (forum moderator)
										case 'discussion_delete':
											if (forge_get_config('use_forum') && $g->usesForum()) {
												set_permission_in_role($permissionPerSectionArray, $ctfpermissionSettingRessource, 'Forum', 'forum', 4);
											}
											break;
										case 'discussion_participate': // fusionforge value: forum = 2 (moderated post)
											if (forge_get_config('use_forum') && $g->usesForum()) {
												set_permission_in_role($permissionPerSectionArray, $ctfpermissionSettingRessource, 'Forum', 'forum', 2);
											}
											break;
										case 'discussion_view': // fusionforge value: forum = 1 (read only)
											if (forge_get_config('use_forum') && $g->usesForum()) {
												set_permission_in_role($permissionPerSectionArray, $ctfpermissionSettingRessource, 'Forum', 'forum', 1);
											}
											break;
										case 'taskmgr_admin': // fusionforge value: pm = 7 (tech + manager)
										case 'taskmgr_delete':
											if (forge_get_config('use_pm') && $g->usesPM()) {
												set_permission_in_role($permissionPerSectionArray, $ctfpermissionSettingRessource, 'ProjectGroup', 'pm', 7);
											}
											break;
										case 'tracker_admin': // fusionforge value: tracker_admin 1 + new_tracker = 15 (manager + tech without vote)
											if (forge_get_config('use_tracker') && $g->usesTracker()) {
												set_permission_in_role($permissionPerSectionArray, $ctfpermissionSettingRessource, 'ArtifactType', 'tracker', 15);
											}
											break;
										case 'tracker_create': // fusionforge value: tracker 11 (technician)
										case 'tracker_delete':
										case 'tracker_edit':
											if (forge_get_config('use_tracker') && $g->usesTracker()) {
												set_permission_in_role($permissionPerSectionArray, $ctfpermissionSettingRessource, 'ArtifactType', 'tracker', 11);
											}
											break;
										case 'tracker_view': // fusionforge value: tracker = 1 (read only)
											if (forge_get_config('use_tracker') && $g->usesTracker()) {
												set_permission_in_role($permissionPerSectionArray, $ctfpermissionSettingRessource, 'ArtifactType', 'tracker', 1);
											}
											break;
										default:
											echo 'Specific Role Permission setting unknown. To be implemented?'."\n";
											break;
									}
								}
							}
						} else {
							echo 'Cannot get Role with FF ID '.$roleFFId.' from database'."\n";
						}
					} else {
						echo 'Cannot recompute this role '.$role_xid.'. Not injected in database.'."\n";
					}
				}
			}
		}
	}
} else {
	echo 'No project found'."\n";
	echo 'Check path of the project tree'."\n";
	exit(100);
}

if (is_file($project_path.'/user.xml')) {
	db_begin();
	echo 'users found to recheck for deletion & role cleaning'."\n";
	$simpleXmlLoadedFile = simplexml_load_file($project_path.'/user.xml');
	if ($simpleXmlLoadedFile !== false) {
		$xmlObjectUsersArray = $simpleXmlLoadedFile->users->sfuser;
		foreach ($xmlObjectUsersArray as $xmlObjectUser) {
			$user_status = (string)$xmlObjectUser->status;
			$user_username = (string)$xmlObjectUser->username;
			// We only support active user.
			// if another object depends on a deleted/removed user, we will use the "Nobody" user.
			$user_xid = (string)$xmlObjectUser['xid'];
			$userFFid = get_ff_id('User', $user_xid);
			if ($userFFid) {
				$user = user_get_object($userFFid);
				if (isset($default_added_role_to_user)) {
					echo 'removing default role to user '.$user->getUnixName();
					$default_added_role_to_user->removeUser($user);
				}
				if ($user_status != 'Active') {
					echo 'deleting user '.$user->getUnixName()."\n";
					$user->delete(true);
				}
			}
		}
	}
	db_commit();
}

echo 'Project injected & parametrized'."\n";
exit(0);

function enablePlugin($pluginname) {
	$pm = plugin_manager_get_object();
	$ok = true;
	$pluginObject = plugin_get_object($pluginname);
	if (!$pluginObject || !$pm->isPluginAvailable($pluginObject)) {
		$res = $pm->activate($pluginname);
		if (!$res) {
			echo 'Unable to activate plugin: '.$pluginname.' with error: '.db_error()."\n";
			$ok = false;
		} else {
			// Load the plugin and now get information from it.
			$pm = plugin_manager_get_object();
			$pm->LoadPlugin($pluginname);
			$plugin = $pm->GetPluginObject($pluginname);
			if (!$plugin || $plugin->isError()) {
				// we need to deactivate the plugin, something went wrong
				$pm->deactivate($pluginname);
				echo 'Could not get plugin object '.$pluginname."\n";
				$ok = false;
			} else {
				if (method_exists($plugin, 'install')) {
					$plugin->install();
				}
			}
			if ($plugin->isError()) {
				echo 'Error: '.$plugin->getErrorMessage()."\n";
				$ok = false;
			}
		}
	}
	return $ok;
}

function enableFeature(&$g, $value) {
	$status = false;
	switch ($value) {
		case 'discussionApplication': // usesForum
			if (forge_get_config('use_forum'))
				$status = $g->setUseForum(true);
			break;
		case 'documentApplication': // usesDocman
			if (forge_get_config('use_docman'))
				$status = $g->setUseDocman(true);
			break;
		case 'frsApplication': // usesFRS
			if (forge_get_config('use_frs'))
				$status = $g->setUseFRS(true);
			break;
		case 'linkedAppApplication': // uses headermenu plugin
			// be sure that headermenu plugin is on, if not enable it!
			if (enablePlugin('headermenu'))
				$status = $g->setPluginUse('headermenu');
			break;
		case 'newsApplication': // usesNews
			if (forge_get_config('use_news'))
				$status = $g->setUseNews(true);
			break;
		case 'pageApplication': // uses vhost ?
			break;
		case 'planningApplication': // planning folder => roadmap from tracker ?
			break;
		case 'reportingApplication': // uses Statistics
			if (forge_get_config('use_activity'))
				$status = $g->setUseActivity(true);
			break;
		case 'scmApplication': // usesSCM
			if (forge_get_config('use_scm'))
				$status = $g->setUseSCM(true);
			break;
		case 'taskApplication': // usesPM
			if (forge_get_config('use_pm'))
				$status = $g->setUsePM(true);
			break;
		case 'trackerApplication': // usesTracker
			if (forge_get_config('use_tracker'))
				$status = $g->setUseTracker(true);
			break;
		case 'wikiApplication': // uses wiki plugin ?
			// be sure that moinmoin plugin is on, if not enable it!
			if (enablePlugin('moinmoin'))
				$status = $g->setPluginUse('moinmoin');
			break;
		default:
			echo 'Unknown feature '.$value."\n";
			break;
	}
	if ($status)
		echo 'feature '.$value.' enabled'."\n";

	return $status;
}

function computeXmlApplication(&$g, $feature, $project_path) {
	$status = false;
	switch ($feature) {
		case 'documentApplication': // usesDocman
			if (forge_get_config('use_docman') && $g->usesDocman()) {
				echo $feature.' data injection'."\n";
				// disable search engine to speed up injection
				//$g->setDocmanSearchStatus(0);
				$g->setDocmanSearchStatus(1);
				$status = computeXmldocumentApplication($g, $project_path);
				// warning to set all permissions correctly
				system('chown -R '.forge_get_config('apache_user').':'.forge_get_config('apache_group').' '.forge_get_config('data_path').'/docman');

			}
			break;
		case 'frsApplication': // usesFRS
			if (forge_get_config('use_frs') && $g->usesFRS()) {
				echo $feature.' data injection'."\n";
				$status = computeXmlfrsApplication($g, $project_path);
				system('chown -R '.forge_get_config('apache_user').':'.forge_get_config('apache_group').' '.forge_get_config('data_path').'/download');
			}
			break;
		case 'discussionApplication': // usesForum
			if (forge_get_config('use_forum') && $g->usesForum()) {
				echo $feature.' data injection'."\n";
				$status = computeXmldiscussionApplication($g, $project_path);
			}
			break;
		case 'newsApplication': // usesNews
			echo $feature.' data injection not yet implemented'."\n";
			break;
		case 'linkedAppApplication': // uses headermenu plugin
			if ($g->usesPlugin('headermenu')) {
				echo $feature.' data injection'."\n";
				$status = computeXmllinkedAppApplication($g, $project_path);
			}
			break;
		case 'pageApplication': // uses vhost ?
		case 'planningApplication': // planning folder => roadmap from tracker ?
		case 'reportingApplication': // uses Statistics
		case 'scmApplication': // usesSCM
		case 'taskApplication': // usesPM
			echo $feature.' data injection not yet implemented'."\n";
			break;
		case 'trackerApplication': // usesTracker
			if (forge_get_config('use_tracker') && $g->usesTracker()) {
				echo $feature.' data injection'."\n";
				$status = computeXmltrackerApplication($g, $project_path);
				system('chown -R '.forge_get_config('apache_user').':'.forge_get_config('apache_group').' '.forge_get_config('data_path').'/tracker');
			}
			break;
		case 'wikiApplication': // uses wiki plugin ?
		default:
			echo $feature.' data injection not yet implemented'."\n";
			break;
	}
	if ($status)
		echo 'feature '.$feature.' data injection done'."\n";

	return $status;
}

function computeXmldocumentApplication(&$g, $project_path) {
	$status = false;
	if (is_file($project_path.'/project/applications/documentApplication/documentApplication.xml')) {
		$simpleXmlLoadedFile = simplexml_load_file($project_path.'/project/applications/documentApplication/documentApplication.xml');
		if ($simpleXmlLoadedFile !== false) {
			$documentFolders = $simpleXmlLoadedFile->document_root_folder->documentFolders->document_folder;
			$subFolderStatus = array();
			foreach ($documentFolders as $documentFolder) {
				$subFolderStatus[] = inject_folder($g, $documentFolder, 0, $project_path);
			}
			if (!in_array(false, $subFolderStatus))
				$status = true;
		}
	}
	return $status;
}

function inject_folder(&$g, $documentFolder, $parentFolderId, $project_path) {
	$continue = true;
	$docgroup_name = trim((string)$documentFolder->title);
	// limitation is 255 chars
	if (strlen($docgroup_name) > MAXSIZE__DOCGROUP_NAME)
		$docgroup_name = substr($docgroup_name, 0, MAXSIZE__DOCGROUP_NAME);
	$docgroup_xid = trim((string)$documentFolder['xid']);
	$docgroup_createdate = trim((string)$documentFolder->dateCreated);
	$dg = new DocumentGroup($g);
	// create a new DocumentGroup with default status: 1 = public
	if ($dg->isError() || !$dg->create($docgroup_name, $parentFolderId, 1, strtotime($docgroup_createdate))) {
		echo 'Error creation folder '.$dg->getErrorMessage();
		$dg->clearError();
		$continue = false;
	}
	if ($continue) {
		$documents = $documentFolder->documents->document;
		$subFolders = $documentFolder->documentFolders->document_folder;
		$documentStatus = array();
		foreach ($documents as $document) {
			$subFolderStatus[] = inject_document($g, $document, $dg->getID(), $project_path);
		}
		$subFolderStatus = array();
		foreach ($subFolders as $subFolder) {
			$subFolderStatus[] = inject_folder($g, $subFolder, $dg->getID(), $project_path);
		}
		if (in_array(false, $subFolderStatus)) {
			$continue = false;
		} else {
			$resxid = db_query_params('insert into ctf_mapping (xid, ffobject, ffid) values ($1, $2, $3)',
						array($docgroup_xid, 'DocumentGroup', $dg->getID()));
			echo 'folder '.$docgroup_name.' injected'."\n";
		}
	}
	return $continue;
}

function inject_document(&$g, $document, $folderId, $project_path) {
	global $adminUser, $ff_ctf_mapping;
	$document_current_version = 0;
	$document_versions = $document->documentVersions->document_version;
	$document_title = trim((string)$document->title);
	if (strlen($document_title) > MAXSIZE__DOCUMENT_TITLE) {
		echo 'Information: document title too long. Shorten to '.MAXSIZE__DOCUMENT_TITLE."\n";
		$document_title = substr($document_title, 0, MAXSIZE__DOCUMENT_TITLE);
		echo 'New document title: '.$document_title."\n";
	}
	if (strlen($document_title) < MINSIZE__DOCUMENT_TITLE) {
		echo 'Information: document title too short. Extented to >'.MINSIZE__DOCUMENT_TITLE."\n";
		$document_title .= ' [comment: title was too short for automatic import]';
		echo 'New document title: '.$document_title."\n";
	}
	$document_description = trim((string)$document->description);
	if (strlen($document_description) < MINSIZE__DOCUMENT_DESCRIPTION) {
		echo 'Information: document description too short. Extented to >'.MINSIZE__DOCUMENT_DESCRIPTION."\n";
		$document_description .= ' [comment: description was too short for automatic import]';
		echo 'New document description: '.$document_description."\n";
	}
	if (strlen($document_description) > MAXSIZE__DOCUMENT_DESCRIPTION) {
		echo 'Information: document description too long. Shorten to '.MAXSIZE__DOCUMENT_DESCRIPTION."\n";
		$document_description = substr($document_description, 0, MAXSIZE__DOCUMENT_DESCRIPTION);
		echo 'New document description: '.$document_description."\n";
	}
	$document_xid = trim((string)$document['xid']);
	$lockByUsername = trim((string)$document->lockByUsername);
	$first_version = true;
	$d = new Document($g);
	foreach ($document_versions as $document_version) {
		if ((string)$document_version['currentVersion'] == 'true') {
			$document_current_version = 1;
		}
		$filedata = $project_path.'/'.$document_version->attach['filename'];
		$filename = trim((string)$document_version->attach['fileDisplayName']);
		if (is_file($filedata)) {
			$filetype = trim((string)$document_version->attach['mimeType']);
			$createdByUsername = trim((string)$document_version->createdByUsername);
			$createdate = trim((string)$document_version->dateCreated);
			$versionComment = trim((string)$document_version->versionComment);
			if (strlen($versionComment) > MAXSIZE__DOCUMENT_VCOMMENT) {
				echo 'Information: document comment too long. Shorten to >'.MAXSIZE__DOCUMENT_VCOMMENT."\n";
				$versionComment = substr($versionComment, 0, MAXSIZE__DOCUMENT_VCOMMENT);
				echo 'New document comment: '.$versionComment."\n";
			}
			$createUserID = get_user_id_by_name($ff_ctf_mapping['user'][$createdByUsername]);
			if ($createUserID == false) {
				echo 'Creator User do not exist: use default admin user'."\n";
				$createUserID = $adminUser->getID();
			}
			$importData = array('nonotice' => 1, 'nocheck' => 1, 'user' => $createUserID, 'time' => strtotime($createdate));
			if ($first_version) {
				if ($d->create($filename, $filetype, $filedata, $folderId, $document_title, $document_description, 1, $versionComment, $importData)) {
					$resxid = db_query_params('insert into ctf_mapping (xid, ffobject, ffid) values ($1, $2, $3)',
							array($document_xid, 'Document', $d->getID()));
					echo 'file '.$filename.' injected'."\n";
					$first_version = false;
				} else {
					echo 'file '.$filename.' injection error '.$d->getErrorMessage()."\n";
					$d->clearError();
					$first_version = true;
				}
			} else {
				if ($d->getID()) {
					if ($d->update($filename, $filetype, $filedata, $folderId, $document_title, $document_description.' '.$versionComment, 1, 0, $document_current_version, 1, $importData, $versionComment)) {
						echo 'file '.$filename.' new version injected'."\n";
					} else {
						echo 'file '.$filename.' new version injection error '.$d->getErrorMessage()."\n";
						$d->clearError();
					}
				}
			}
		} else {
			echo 'Error: file xid '.$document_xid.' - '.$filename.'::'.$filedata.' skipped. Missing file!'."\n";
		}
	}
	if (!$first_version && (strlen($lockByUsername) > 0)) {
		if (isset($ff_ctf_mapping['user'][$lockByUsername])) {
			$ffUserID = get_user_id_by_name($ff_ctf_mapping['user'][$lockByUsername]);
			if ($ffUserID != false) {
				if ($d->setReservedBy(1, $ffUserID)) {
					echo 'document reserved'."\n";
				} else {
					echo 'Error: unable to reserved document: '.$d->getErrorMessage()."\n";
					$d->clearError();
				}
			}  else {
				echo 'Warning! cannot set reservation. username not existing.'."\n";
			}
		} else {
			echo 'Warning! cannot set reservation. username not existing.'."\n";
		}
	}
	return true;
}

function computeXmltrackerApplication(&$g, $project_path) {
	$status = false;
	if (is_file($project_path.'/project/applications/trackerApplication/trackerApplication.xml')) {
		$simpleXmlLoadedFileTracker = simplexml_load_file($project_path.'/project/applications/trackerApplication/trackerApplication.xml');
		if ($simpleXmlLoadedFileTracker === false) {
			echo 'Error when loading file trackerApplication.xml'."\n";
			foreach(libxml_get_errors() as $error) {
				echo "\t", $error->message;
			}
			return $status;
		}
	} else {
		echo 'File not found: '.$project_path.'/project/applications/trackerApplication/trackerApplication.xml'."\n";
		return false;
	}


	if (is_file($project_path.'/project/artifactHistories/artifactHistories.xml')) {
		$simpleXmlLoadedFileArtifactHistories = simplexml_load_file($project_path.'/project/artifactHistories/artifactHistories.xml');
		if ($simpleXmlLoadedFileArtifactHistories === false) {
			echo 'Error when loading file artifactHistories.xml'."\n";
			foreach(libxml_get_errors() as $error) {
				echo "\t", $error->message;
			}
			return $status;
		}
	} else {
		echo 'Warning: File not found '.$project_path.'/project/artifactHistories/artifactHistories.xml'."\n";
		$simpleXmlLoadedFileArtifactHistories = new SimpleXMLElement('<artifactHistories />');
	}

	if (is_file($project_path.'/project/auditing/auditing.xml')) {
		$simpleXmlLoadedFileAuditing = simplexml_load_file($project_path.'/project/auditing/auditing.xml');
		if ($simpleXmlLoadedFileAuditing === false) {
			echo 'Error when loading file auditing.xml'."\n";
			foreach(libxml_get_errors() as $error) {
				echo "\t", $error->message;
			}
			return $status;
		}
	} else {
		echo 'Warning: File not found '.$project_path.'/project/auditing/auditing.xml'."\n";
		$simpleXmlLoadedFileAuditing = new SimpleXMLElement('<auditing />');
	}

	//trakers
	$trackers = $simpleXmlLoadedFileTracker->tracker;
	$histories = $simpleXmlLoadedFileArtifactHistories;
	$auditing = $simpleXmlLoadedFileAuditing;
	$trackerStatus = array();
	$t = array();
	$tracker_xid = array();
	$default_values = array();
	$key = 0;
	echo 'traker XML :'."\n";
	foreach ($trackers as $tracker) {
		$t[$key] = null;
		$tracker_xid[$key] = '';
		$default_values[$key]= array();
		echo 'key tracker :'.$key."\n";
		$trackerStatus[$key] = inject_tracker($g, $tracker, $t[$key], $tracker_xid[$key], $default_values[$key], $project_path);
		$key += 1;
	}

	//artifacts
	$artifactStatus = array();
	$key = 0;
	foreach ($trackers as $tracker) {
		echo 'key tracker :'.$key."\n";
		if ($trackerStatus[$key]) {
			$artifacts = $tracker->artifacts->artifact;
			if (is_array($artifacts) || is_object($artifacts)) {
				foreach ($artifacts as $artifact) {
					$artifactStatus[] = inject_artifact($g, $t[$key], $artifact, $histories, $auditing, $tracker_xid[$key], $default_values[$key], $project_path);
				}
			}
		}
		$key += 1;
	}

	if (!in_array(false, $trackerStatus) && !in_array(false, $artifactStatus)) {
		$status = true;
	}

	return $status;
}

function inject_tracker(&$g, $tracker, &$t, &$tracker_xid, &$default_values, $project_path) {
	$continue = true;
	if ((string)$tracker->isDeleted == "true")
		return $continue;
	$trackername = (string)$tracker->title;
	$description = (string)$tracker->description;
	if (trim($description)=="") {
		$description = $trackername;
	}
	//dateCreated
	//dateLastModified
	//createdByUsername
	//lastModifiedByUsername
	//icon
	$email_all = '';
	$email_address = '';
	$due_period = 30;
	$use_resolution = '';
	$submit_instructions = '';
	$browse_instructions = '';
	$tracker_xid = (string)$tracker['xid'];
	$t = new ArtifactType ($g);
	$r = $t->create($trackername, $description, $email_all, $email_address, $due_period, $use_resolution, $submit_instructions, $browse_instructions);
	if ($t->isError() || !$r) {
		echo 'Error when creating tracker '.$trackername.': '.$t->getErrorMessage()."\n";
		$t->clearError();
		$continue = false;
	} else {
		$resxid = db_query_params('insert into ctf_mapping (xid, ffobject, ffid) values ($1, $2, $3)', array($tracker_xid, 'ArtifactType', $t->getID()));
		echo 'Tracker: '.$trackername.' injected'."\n";
	}

	//fields
	if ($continue) {
		$fields = $tracker->fields->field;
		if (is_array($fields) || is_object($fields)) {
			$fieldStatus = array();
			foreach ($fields as $field) {
				$fieldStatus[] = inject_field($t, $field, $default_values, $project_path, $tracker_xid);
			}
			if (in_array(false, $fieldStatus))
				$continue = false;
		}
	}

	//autoAssignments
	//autoAssignField
	//folderLayouts


	//workflow
	if ($continue && !empty($tracker->workflow)) {
		echo "Workflow\n";
		// update workflow event sequence if < 100
		$res = db_query_params ("select setval('artifact_workflow_event_id_seq', GREATEST(currval('artifact_workflow_event_id_seq'),100))");
		$arr = db_fetch_array($res);
		$r=$t->fetchData($t->getID());
		$CSFid = $t->getCustomStatusField();
		$CSFname =$t->getExtraFieldName($CSFid);
		$CSFElements = $t->getExtraFieldElements($CSFid);
		$allElements = array_map('get_column_element_id',$CSFElements);
		// php>=5.5 : $allNodes = array_column($CSFElements, 'element_id');
		$CSFElements []  = Array( 'element_id' => 100, 'element_name'=>'fldv-new', 'status_id' => 0);

		$w = new ArtifactWorkflow($t, $CSFid);
		if (is_array($fields) || is_object($fields))
			foreach ($fields as $field) {
				if ($field->name == $CSFname) {
					$CSField = $field;
					break;
				}
			}

		//nodes
		$transitions = $tracker->workflow->transition;
		if (is_array($CSFElements) || is_object($CSFElements)) {
			foreach ($CSFElements as $element) {
				echo "element : ".$element ["element_name"]."\n";
				$nodes = array ();
				if (is_array ( $transitions ) || is_object ( $transitions ))
					foreach ($transitions as $transition) {
						$fromValue = (string)$transition->fromValue;
						if ($element ["element_name"] == $fromValue || $fromValue == 'fldv-any') {
							echo 'from value '.$fromValue."\n";
							$toValue = (string)$transition->toValue;
							echo 'to value '.$toValue."\n";
							if ($toValue == 'fldv-any') {
								$nodes = array_diff($allElements, array($element ["element_id"]));
							} else {
								$toValueId = get_element_id_by_name ( $CSFElements, $toValue );
								if (!$toValueId) {
									echo 'Warning, unknown status in workflow : '.$toValue."\n";
								} else {
									$nodes [] = $toValueId;
								}
							}
						}
					}
				if ($element ["status_id"] == 1 && empty($nodes)) {
					$nodes = array_diff($allElements, array($element ["element_id"]));
				}
				$w->saveNextNodes ( $element ["element_id"], $nodes );
			}
		}

		$engine = RBACEngine::getInstance () ;
		$roleObjArr = $engine->getRolesByAllowedAction('tracker', $g->getID(), 'tech') ;
		$allRoles = array();
		foreach ($roleObjArr as $roleObj) {
			$allRoles [] = $roleObj->getID();
		}

		if (is_array ( $transitions ) || is_object ( $transitions )) {
			foreach ($transitions as $transition) {
				$fromValue = (string)$transition->fromValue;
				if ($fromValue == 'fldv-any') {
					$fromValueIdArr = $allElements;
				} else {
					$fromValueId = get_element_id_by_name ( $CSFElements, $fromValue );
					if ($fromValueId) {
						$fromValueIdArr = array($fromValueId);
					} else {
						$fromValueIdArr = array();
					}
				}
				$toValue = (string)$transition->toValue;
				if ($toValue == 'fldv-any') {
					$toValueIdArr = $allElements;
				} else {
					$toValueId = get_element_id_by_name ( $CSFElements, $toValue );
					if ($toValueId) {
						$toValueIdArr = array($toValueId);
					} else {
						$toValueIdArr = array();
					}
				}
				if (!empty($fromValueIdArr) && !empty($toValueIdArr)) {

					//transitionRoles
					$transitionRoles = $transition->transitionRoles->transition_role;
					$roles = array();
					if (!empty($transitionRoles)) {
						if (is_array($transitionRoles) || is_object($transitionRoles)) {
							foreach ( $transitionRoles as $transitionRole ) {
								$roleId = get_role_id_by_name($g, (string)$transitionRole->title);
								if (!$roleId) {
									echo 'Waring unknown role in workfow :'.(string)$transitionRole->title."\n";
								} else {
									$roles[] = $roleId;
								}
							}
						}
					} else {

						$roles = $allRoles;
					}
					foreach ($fromValueIdArr as $fromValueId) {
						//$statusId = get_element_status_id_by_name($CSFElements, $fromValueId);
						foreach ($toValueIdArr as $toValueId) {
							if (!empty($transitionRoles)) {
								$w->saveAllowedRoles($fromValueId, $toValueId, $roles);
							} else {
								$w->saveAllowedRoles($fromValueId, $toValueId, $allRoles);
							}
						}
					}

					//transitionRequiredFields
					$transitionRequiredFields = $transition->transitionRequiredFields->transition_required_field;
					$requiredfields = array();
					if (is_array ( $transitionRequiredFields ) || is_object ( $transitionRequiredFields )) {
						foreach ($transitionRequiredFields as $transitionRequiredField) {
							$ef = get_extra_field_by_name($t, (string)$transitionRequiredField->requiredFieldName);
							if (!$ef) {
								echo 'Waring unknown extra field in workfow :'.(string)$transitionRequiredField->requiredFieldName."\n";
							} else {
								$requiredfields[] = $ef['extra_field_id'];
							}
						}
						foreach ($fromValueIdArr as $fromValueId) {
							foreach ($toValueIdArr as $toValueId) {
								$w->saveRequiredFields($fromValueId, $toValueId, $requiredfields);
							}
						}
					}
				}
			}
		}
	}
	return $continue;
}

function get_initial_fields_value($artifact, $auditEntries) {

	$fieldsValue = array();

	$details = (string)$artifact->description;

	$fieldsValue['title'] = (string)$artifact->title;
	$fieldsValue['priority'] = (string)$artifact->priority;
	$fieldsValue['description'] = (string)$artifact->description;

	$fieldsValue['estimatedEffort'] = (integer)$artifact->estimatedEffort;
	$fieldsValue['actualEffort'] = (integer)$artifact->actualEffort;
	$fieldsValue['remainingEffort'] = (integer)$artifact->remainingEffort;
	$fieldsValue['autosumming'] = (string)$artifact->autosumming;

	$fieldsValue['points'] = (integer)$artifact->points;

	$fieldsValue['group'] = (string)$artifact->group;
	$fieldsValue['category'] = (string)$artifact->category;
	$fieldsValue['status'] = (string)$artifact->status;
	$fieldsValue['customer'] = (string)$artifact->customer;
	$fieldsValue['assignedToUsername'] = (string)$artifact->assignedToUsername;
	$fieldsValue['plannedFor'] = (string)$artifact->plannedFor;
	$fieldsValue['reportedInRelease'] = (string)$artifact->reportedInRelease;
	$fieldsValue['resolvedInRelease'] = (string)$artifact->resolvedInRelease;

	$flexValues = $artifact->flexValues->flexValue;
	foreach ($flexValues as $flexValue) {
		$fieldsValue[(string)$flexValue->fieldName] = (string)$flexValue->fieldValue;
	}

	$auditEntries = array_reverse($auditEntries);
	foreach ($auditEntries as $auditEntry) {
		$auditChanges = $auditEntry->auditChanges->audit_change;
		foreach ($auditChanges as $auditChange) {
			$propertyName = (string)$auditChange->propertyName;

			//rename
			// audit >>> histo >>> artifact
			// releaseId >>> reportedInReleaseXid >>> reportedInRelease
			// resolvedReleaseId >>> fixedInReleaseXid >>>resolvedInRelease
			// planningFolder >>> planningFolderXid >>> plannedFor
			switch ($propertyName) {
				case 'planningFolder':
					$propertyName = 'plannedFor';
					break;
				case 'releaseId':
					$propertyName = 'reportedInRelease';
					break;
				case 'resolvedReleaseId':
					$propertyName = 'resolvedInRelease';
					break;
			}

			// tracker or project move
			// folderId = tracker xid
			if ($propertyName == 'folderId') {
				$oldValue = (string)$auditChange->oldValue;
				$trackerId = get_traker_id_by_xid($oldValue);
				if ($trackerId) {
					$fieldsValue[$propertyName] = $oldValue;
				}
			} else {
				$oldValue = (string)$auditChange->oldValue;
				$fieldsValue[$propertyName] = $oldValue;
			}
		}
	}
	//var_dump($fieldsValue);
	return $fieldsValue;
}

function inject_artifact(&$g, $tracker, $artifact, $histories, $auditing, $tracker_xid, $default_values, $project_path) {
	global $adminUser;
	$continue = true;
	$t = $tracker;
	$r = $t->fetchData($t->getID());
	$artifact_xid = (string)$artifact['xid'];

	echo "Import artifact: ".$artifact_xid."\n";

	$auditEntries = $auditing->xpath("/auditing/audit_entry[./objectXid = '".$artifact_xid."']");
	usort($auditEntries, 'sort_audit_entries');

	$initialFieldsValue = get_initial_fields_value($artifact,$auditEntries);

	// if created in an other tracker (foldeId = tracker xid)
	if (isset($initialFieldsValue['folderId'])) {
		$trackerId = get_traker_id_by_xid($initialFieldsValue['folderId']);
		$t = new ArtifactType($g, $trackerId);
		$r = $t->fetchData($trackerId);
		if ($t->isError()) {
			echo 'Error traker '.$initialFieldsValue['folderId'].': '.$t->getErrorMessage()."\n";
			return false;
		}
	}

	$new_artifact_type_id = $t->getID();
	$extraFields = $t->getExtraFields();
	$efStatusId = $t->getCustomStatusField();

	$extraFieldsName = array_map('get_column_field_name', $extraFields);

	$extraFieldTypesWithId = array(ARTIFACT_EXTRAFIELDTYPE_SELECT, ARTIFACT_EXTRAFIELDTYPE_CHECKBOX, ARTIFACT_EXTRAFIELDTYPE_RADIO, ARTIFACT_EXTRAFIELDTYPE_MULTISELECT, ARTIFACT_EXTRAFIELDTYPE_STATUS);

	$artifactOperations = array('$create');
	$attachmentOperations = array('$add_attachment','$delete_attachment');
	$specialFields = array('title','description', 'assignedTo', 'priority');
	$dontCareFields = array('plannedFor', 'closeDate');
	// projectId => move form a project to an other
	$notExtraFiels = array_merge($artifactOperations, $attachmentOperations, $specialFields, $dontCareFields);

	//non géré dans history ou audit
//	$details = (string)$artifact->description;



	$summary = $initialFieldsValue['title'];
	$details = $initialFieldsValue['description'];
	if (!isset($initialFieldsValue['assignedTo'])) {
		$assigned_to = 100;
	} else {
		$assignedTo = $initialFieldsValue['assignedTo'];
		$assigned_to = get_user_id_by_name($assignedTo);
		if ($assigned_to == false)
			$assigned_to = 100;
	}
	$priority = $initialFieldsValue['priority'];
	if (isset($initialFieldsValue['projectId'])) {
		$createdInProject = $initialFieldsValue['projectId'];
	}

	$extra_fields = array();
	//echo 'Initials values'."\n";
	//var_dump($initialFieldsValue);
	foreach ($extraFields as $ef) {
		if (isset($initialFieldsValue[$ef['field_name']])) {
			$efvalue = $initialFieldsValue[$ef['field_name']];
			if (in_array($ef["field_type"],$extraFieldTypesWithId)) {
				if ($efvalue=='') {
					$extra_fields[$ef['extra_field_id']] = 100;
				} else {
					$efe = get_extra_field_element_by_name($t, $ef['extra_field_id'], $efvalue);
					if (!$efe) {
						echo 'Warning: extra field element '.$efvalue.' not found for extra field '.$ef['field_name']."\n";
					} else {
						$extra_fields[$ef['extra_field_id']] =  $efe['element_id'];
					}
				}
			} else {
				$extra_fields[$ef['extra_field_id']] = $efvalue;
			}
		}
	}

	$importData = array('nopermcheck' => true, 'nonotice' => true);
	$created = false;

	foreach ($auditEntries as $auditEntry) {
		$aEOperation = (string)$auditEntry->operation;

		// utilisateur
		$createdBy = (string)$auditEntry->createdByUsername;
		$user = get_user_id_by_name($createdBy);
		if ($user == false) {
			echo 'Warning : user '.$createdBy.' not found'."\n";
			$user = $adminUser->getID();
		}
		$datetime = (string)$auditEntry->dateCreated;
		$time = strtotime($datetime);
		$importData['user'] = $user;
		$importData['time'] = $time;
		$fileXid = '';
		$fileToDel = '';
		$change = false;
		$attachmentCounter=0;

		foreach ($auditEntry->auditChanges->audit_change as $auditChange) {
			$propertyName = (string)$auditChange->propertyName;
			$moveOfTraker = false;
			$moveOfProject = false;
			//rename
			// audit >>> histo >>> artifact
			// releaseId >>> reportedInReleaseXid >>> reportedInRelease
			// resolvedReleaseId >>> fixedInReleaseXid >>>resolvedInRelease
			// planningFolder >>> planningFolderXid >>> plannedFor
			switch ($propertyName) {
				case 'planningFolder':
					$propertyName = 'plannedFor';
					break;
				case 'releaseId':
					$propertyName = 'reportedInRelease';
					break;
				case 'resolvedReleaseId':
					$propertyName = 'resolvedInRelease';
					break;
			}
			switch ($propertyName) {
				case '$add_attachment':
					$filesXidArr = explode("\n",(string)$auditChange->newValue);
					$nbAttachment = count($filesXidArr);
					$fileXid = $filesXidArr[$attachmentCounter];
					break;
				case '$delete_attachment':
					$fileToDel = (string)$auditChange->oldValue;
					break;
				case 'title':
					$summary = (string)$auditChange->newValue;
					$change = true;
					break;
				case 'description':
					$details = (string)$auditChange->newValue;
					$change = true;
					break;
				case 'assignedTo':
					$assignedTo = (string)$auditChange->newValue;
					if ($assignedTo == 'nobody') {
						$assigned_to = 100;
					} else {
						$assigned_to = get_user_id_by_name($assignedTo);
						if ($assigned_to == false)
							$assigned_to = 100;
					}
					$change = true;
					break;
				case 'priority':
					$priority = (integer)$auditChange->newValue;
					$change = true;
					break;
				case 'folderId':
					$moveOfTraker = true;
					$moveTrackerXid = (string)$auditChange->newValue;
					$trackerId = get_traker_id_by_xid($moveTrackerXid);
					if (!$trackerId) {
						echo 'Error: tracker '.$moveTrackerXid.'not found'."\n";
						return false;
					}
					$new_artifact_type_id = $trackerId;
					break;
				case 'projectId':
					$moveOfProject = true;
					$moveFromProject = (string)$auditChange->oldValue;
					$moveToProject = (string)$auditChange->newValue;
					break;
				default:
					if (!in_array($propertyName,$notExtraFiels)) {
						if (in_array($propertyName, $extraFieldsName)) {
							$ef = get_extra_field_by_name($t, $propertyName);
							$newValue = (string)$auditChange->newValue;
							if (in_array($ef["field_type"],$extraFieldTypesWithId)) {
								if ($newValue=='') {
									$efe = 100;
								} else {
									$efe = get_extra_field_element_by_name($t, $ef['extra_field_id'], $newValue);
									if (!$efe) {
										echo 'Warning: extra field element '.$newValue.' not found for extra field '.$propertyName."\n";
									} else {
										$extra_fields[$ef['extra_field_id']] =  $efe['element_id'];;
										$change = true;
									}
								}
							} else {
								$extra_fields[$ef['extra_field_id']] = $newValue;
							}
							$change = true;
						} else {
							echo 'Warning, unknown propertyName: '.$propertyName."\n";
						}
					}
			}
		}

		switch ($aEOperation) {
			case 'create':
//   				if ($created) {
//  					break;
//  				}
				echo "\t".'create artifact '.$artifact_xid.' ('.$datetime.')'."\n";
// 				if ((string)$history[0]->operation!='create') {
// 					echo 'Error: create operation not found in artifact_history for artifact '.$artifact_xid."\n";
// 				}

				$a = new Artifact($t);
				if ($a->isError()) {
					echo 'Error when creating artifact: '.$a->getErrorMessage()."\n";
				}
				//				echo 'tracker id : '.$a->ArtifactType->getID()."\n";
				//echo 'extra fields :'."\n";
				//var_dump($extra_fields);
				$r = $a->create($summary, $details, $assigned_to, $priority, $extra_fields, $importData);
				if ($a->isError() || !$r) {
					echo 'Error when creating artifact '.$summary.': '.$a->getErrorMessage()."\n";
					$a->clearError();
				} else {
					if (isset($createdInProject)) {
						$canned_response = 100;
						$status_id = null;
						$CreatComment = 'Import information: Artifact create in project '.$createdInProject;
						$r = $a->update($priority, $status_id, $assigned_to, $summary, $canned_response, $CreatComment, $new_artifact_type_id, $extra_fields, $details, $importData);
					}
					echo "\t".'artifact '.$artifact_xid.' created'."\n";
					$created = true;
				}
				break;
			case 'move':
				if ($moveOfProject) {
					$canned_response = 100;
					$status_id = null;
					$moveComment = 'Import information: Artifact move form project '.$moveFromProject.' to project'.$moveToProject;
					$r = $a->update($priority, $status_id, $assigned_to, $summary, $canned_response, $moveComment, $new_artifact_type_id, $extra_fields, $details, $importData);
					if ($a->isError() || !$r) {
						echo 'Error when updating/moving artifact '.$summary.': '.$a->getErrorMessage()."\n";
						$a->clearError();
					} else {
						echo "\t".'artifact '.$artifact_xid.' '.$aEOperation.'d'."\n";
					}
				}
				// no break
			case 'update':
				echo "\t".$aEOperation.' artifact '.$artifact_xid.' ('.$datetime.')'."\n";
				if (!$created) {
					echo 'Error try to '.$aEOperation.' without having created the artifact '.$summary."\n";
				}

				//comment
				if (isset($auditEntry->comment)) {
					$comment = (string)$auditEntry->comment;
					$change = true;
				} else {
					$comment ='';
				}

				if ($change) {
					$canned_response = 100;
					$status_id = null;
					$r = $a->update($priority, $status_id, $assigned_to, $summary, $canned_response, $comment, $new_artifact_type_id, $extra_fields, $details, $importData);
					if ($a->isError() || !$r) {
						echo 'Error when updating/moving artifact '.$summary.': '.$a->getErrorMessage()."\n";
						$a->clearError();
					} else {
						echo "\t".'artifact '.$artifact_xid.' '.$aEOperation.'d'."\n";
					}
					if ($moveOfTraker) {
						$extra_fields = $a->getExtraFieldData();
						$t = $a->getArtifactType();
						$r = $t->fetchData($new_artifact_type_id);
					}
				}
				break;
			default:
				echo 'Warning unknown artifact audit operation:'.$aEOperation;
				break;
		}

		//attach
		//add
		if (isset($auditEntry->attach)) {
			$filename = (string)$auditEntry->attach['fileDisplayName'];
			$filename = preg_replace("/[^-a-zA-Z0-9+_\. ~]/", "-", $filename);
			$fileLocation = $project_path.(string)$auditEntry->attach['filename'];
			$filetype = (string)$auditEntry->attach['mimeType'];
			if (is_file($fileLocation)) {
				$filesize = filesize($fileLocation);
				$description = '';
				$file = new ArtifactFile($a);
				if (!$file || !is_object($file) || $file->isError()) {
					echo 'Error when creating ArtifactFile '.$filename.': '.$file->getErrorMessage()."\n";
				}
				$r = $file->create($filename, $filetype, $filesize, $fileLocation, $description, $importData);
				if ($file->isError() || !$r) {
					echo 'Error when creating ArtifactFile '.$filename.': '.$file->getErrorMessage()."\n";
				} else {
					$resxid = db_query_params('insert into ctf_mapping (xid, ffobject, ffid) values ($1, $2, $3)', array($fileXid, 'ArtifactFile', $file->getID()));
					echo "\t".'ArtifactFile: '.$filename.' injected'."\n";
				}
			} else {
				echo 'Error when creating ArtifactFile '.$filename.': file not found'."\n";
			}
			if ($nbAttachment == ($attachmentCounter + 1)) {
				$attachmentCounter = 0;
			} else {
				$attachmentCounter += 1;
			}
		}
		//del
		if ($fileToDel) {
			$attachedFiles = $a->getFiles();
			foreach ($attachedFiles as $attachedFile) {
				if ($fileToDel == $attachedFile->getName()) {
					$attachedFile->delete();
					$fileToDel = '';
				}
			}
		}
	}
	echo 'Artifact '.$artifact_xid.' injected'."\n";
}

function inject_field(&$t, $field, &$default_values, $project_path, $tracker_xid) {
	$continue = true;
	$fieldname = (string)$field->name;
	$displayType = (string)$field->displayType;
	$displaySize = (integer)$field->displaySize;
	$displayLines  = (integer)$field->displayLines;
	$is_required = ((string)$field->isRequired=="true")?true:false;
	$is_disabled = ((string)$field->isDisabled=="true")?true:false;
	$is_hidden_on_submit = ((string)$field->isHiddenOnCreate=="true")?true:false;
// 	dateCreated
// 	dateLastModified
	$description = (string)$field->helpText;
	$pattern = (string)$field->pattern;
// 	createdByUsername
// 	lastModifiedByUsername
	$attribute1 = 1;
	$attribute2 = 1;
	switch ($displayType) {
		case 'DROPDOWN':
			switch ($fieldname) {
				case 'status':
					$field_type = ARTIFACT_EXTRAFIELDTYPE_STATUS;
					break;
				case 'reportedInRelease':
				case 'resolvedInRelease':
					//$field_type = ARTIFACT_EXTRAFIELDTYPE_RELEASE;
					//$field_type = ARTIFACT_EXTRAFIELDTYPE_SELECT;
					$field_type = ARTIFACT_EXTRAFIELDTYPE_TEXT;
					$attribute1 = 25;
					$attribute2 = 25;
					break;
				default:
					$field_type = ARTIFACT_EXTRAFIELDTYPE_SELECT;
			}
			break;
// 		case '':
// 			$field_type = ARTIFACT_EXTRAFIELDTYPE_CHECKBOX;
//			break;
// 		case '':
// 			$field_type = ARTIFACT_EXTRAFIELDTYPE_RADIO;
//			break;
		case 'TEXT':
			if (in_array($fieldname ,array('estimatedEffort', 'actualEffort', 'remainingEffort', 'points'))) {
				$field_type = ARTIFACT_EXTRAFIELDTYPE_INTEGER;
				$attribute1 = $displaySize;
				$attribute2 = $displaySize*4;
			} elseif ($displayLines > 1) {
				$field_type = ARTIFACT_EXTRAFIELDTYPE_TEXTAREA;
				$attribute1 = $displayLines;
				$attribute2 = $displaySize;
 			} else {
 				$field_type = ARTIFACT_EXTRAFIELDTYPE_TEXT;
 				$attribute1 = $displaySize;
 				$attribute2 = $displaySize*4;
			}
			break;
		case 'MULTISELECT':
			$field_type = ARTIFACT_EXTRAFIELDTYPE_MULTISELECT;
			$attribute1 = $displayLines;
			$attribute2 = $displaySize;
			break;
// 		case '':
// 			$field_type = ARTIFACT_EXTRAFIELDTYPE_ASSIGNEE;
// 			break;
// 		case '':
// 			$field_type = ARTIFACT_EXTRAFIELDTYPE_RELATION;
// 			break;
// 		case '':
// 			$field_type = ARTIFACT_EXTRAFIELDTYPE_INTEGER;
// 			break;
// 		case '':
// 			$field_type = ARTIFACT_EXTRAFIELDTYPE_FORMULA;
// 			break;
		case 'DATE':
//			$field_type = ARTIFACT_EXTRAFIELDTYPE_DATE;
			$field_type = ARTIFACT_EXTRAFIELDTYPE_TEXT;
			$attribute1 = 10;
			$attribute2 = 10;
			break;
		case 'USER':
			$field_type = ARTIFACT_EXTRAFIELDTYPE_USER;
			break;
		default:
			echo 'Unknow type :'.$displayType."\n";
			exit;
	}


	$alias = '';
	$show100 = true;
	$show100label = 'none';
	$parent = 100;
	$autoassign = 0;

	$f = new ArtifactExtraField ($t);
	$r = $f->create($fieldname, $field_type, $attribute1, $attribute2, $is_required, $alias, $show100, $show100label, $description, $pattern, $parent, $autoassign, $is_hidden_on_submit, $is_disabled);
	if ($f->isError() || !$r) {
		echo 'Error when creating extra field '.$fieldname.': '.$f->getErrorMessage()."\n";
		$f->clearError();
		$continue = false;
	} else {
		$resxid = db_query_params('insert into ctf_mapping (xid, ffobject, ffid) values ($1, $2, $3)', array($tracker_xid."/".$fieldname, 'ArtifactExtraField', $f->getID()));
		echo 'Extra field: '.$fieldname.' injected'."\n";

		switch ($fieldname) {
			case 'estimatedEffort':
			case 'actualEffort':
			case 'remainingEffort':
			case 'points':
				$default_values[$f->getID()]=0;
				break;
			case 'autosumming':
				// needed ???
				$default_values[$f->getID()]=false;
				break;
		}
	}
	if ($continue) {

		// delete default Open and Closed
		if ($field_type == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
			$statusFieldValues = $f->getAvailableValues();
			$id = get_element_id_by_name($statusFieldValues,'Open');
			$element = new ArtifactExtraFieldElement($f,$id);
			$element->delete();
			$id = get_element_id_by_name($statusFieldValues,'Closed');
			$element = new ArtifactExtraFieldElement($f,$id);
			$element->delete();
		}

		$fieldValues = $field->fieldValues->field_value;
		$fieldValueStatus = array();
		if (is_array($fieldValues) || is_object($fieldValues))
			foreach ($fieldValues as $fieldValue) {
				$fieldValueStatus[] = inject_fieldValue($f, $fieldValue, $default_values, $project_path, $tracker_xid);
			}
		if (in_array(false, $fieldValueStatus))
			$continue = false;
	}
	return $continue;
}

function inject_fieldValue(&$f, $fieldValue, &$default_values, $project_path, $tracker_xid) {
	$continue = true;
	$valuename = (string)$fieldValue->value;
	$default = (string)$fieldValue['default'];

	if ($f->getName () == "status") {
		if (( string ) $fieldValue->valueClass == "Open")
			$status_id = 1;
		else
			$status_id = 2;
	} else
		$status_id = 0;
	$fv = new ArtifactExtraFieldElement ($f);
	$r = $fv->create($valuename,$status_id);
	if ($fv->isError() || !$r) {
		echo 'Error when creating extra field '.$valuename.': '.$fv->getErrorMessage()."\n";
		$fv->clearError();
		$continue = false;
	} else {
		$displayOrder = (integer)$fieldValue->displayOrder;
		$r = $f->updateOrder($fv->getID(), $displayOrder);
		if ($f->isError() || !$r) {
			echo 'Error when update extra field value order'.$valuename.': '.$f->getErrorMessage()."\n";
			$f->clearError();
			$continue = false;
		} else {
			if ($default =='true') {
				$default_values[$f->getId()]=$fv->getID();
			}
			$resxid = db_query_params('insert into ctf_mapping (xid, ffobject, ffid) values ($1, $2, $3)', array($tracker_xid."/".$f->getName()."/".$valuename, 'ArtifactExtraFieldElement', $fv->getID()));
			echo 'Extra field value: '.$valuename.' injected'."\n";
		}
	}
	return $continue;
}

function get_element_id_by_name($elements, $name) {
	$id = false;
	foreach ( $elements as $element ) {
		if ($element ["element_name"] == $name) {
			$id = $element ["element_id"];
			break;
		}
	}
	if (!$id) {
		echo 'Error unknow element: '.$name."\n";
	}
	return $id;
}

function get_element_status_id_by_name($elements, $name) {
	$status_id = false;
	foreach ( $elements as $element ) {
		if ($element ["element_name"] == $name) {
			$status_id = $element ["status_id"];
			break;
		}
	}
	if (!$status_id) {
		echo 'Error unknow element: '.$name."\n";
	}
	return $status_id;
}

function get_role_id_by_name($g, $name) {
	$roles = $g->getRoles();
	foreach ($roles as $role) {
		if ($role->getName() == $name) {
			return $role->getID();
		}
	}
	return false;
}

function get_user_id_by_name($name) {
	$id = false;
	if (preg_match('/d-.*_[^_]*/', $name)) {
		$name = substr($name, 2);
		$underscore = strrpos($name, '_');
		$name = substr($name, 0, $underscore);
	}
	if (strlen($name) > MAXSIZE__USER_UNIXNAME) {
		$name = substr($name, 0, MAXSIZE__USER_UNIXNAME);
	}
	$userObject = user_get_object_by_name($name);
	if ($userObject && is_object($userObject) && !$userObject->isError()) {
		$id = $userObject->getID();
	}
	return $id;
}

function get_email_by_name($name) {
	$email = false;
	if (preg_match('/d-.*_[^_]*/', $name)) {
		$name = substr($name, 2);
		$underscore = strrpos($name, '_');
		$name = substr($name, 0, $underscore);
	}
	if (strlen($name) > MAXSIZE__USER_UNIXNAME) {
		$name = substr($name, 0, MAXSIZE__USER_UNIXNAME);
	}
	$userObject = user_get_object_by_name($name);
	if ($userObject && is_object($userObject) && !$userObject->isError()) {
		$email = $userObject->getEmail();
	}
	return $email;
}

function get_extra_field_element_by_name($t, $extraFieldId, $name) {
	$extra_field_element=false;
	$r=$t->fetchData($t->getID());
	$elements = $t->getExtraFieldElements($extraFieldId);
	if (is_array($elements) || is_object($elements)) {
		foreach ($elements as $element) {
			if ($element ["element_name"] == $name) {
				$extra_field_element = $element;
				break;
			}
		}
	}
	return $extra_field_element;
}

function get_extra_field_by_name($t, $name) {
	$extra_field=false;
	$extraFields = $t->getExtraFields();
	if (is_array($extraFields) || is_object($extraFields)) {
		foreach ($extraFields as $extraField) {
			if ($extraField ["field_name"] == $name) {
				$extra_field = $extraField;
				break;
			}
		}
	}
	return $extra_field;
}

function get_traker_id_by_xid($xid) {
	$id=false;
	$result = db_query_params('select ffid from ctf_mapping WHERE xid=$1',
		array($xid));
	if ($result && db_numrows($result) > 0) {
		$id = db_result($result, 0, 'ffid');
	}
//	echo "id tracker ".$xid.": ".$id."\n";
	return $id;
}
function sort_audit_entries($ae1, $ae2) {
	return strcmp($ae1->dateCreated, $ae2->dateCreated);
}

function sort_artifact_history($ah1, $ah2) {
	return strcmp($ah1->dateModified, $ah2->dateModified);
}

function get_column_element_id ($array) {
	return $array['element_id'];
}

function get_column_field_name ($array) {
	return $array['field_name'];
}

function computeXmlfrsApplication(&$g, $project_path) {
	$status = false;
	if (is_file($project_path.'/project/applications/frsApplication/frsApplication.xml')) {
		$simpleXmlLoadedFile = simplexml_load_file($project_path.'/project/applications/frsApplication/frsApplication.xml');
		if ($simpleXmlLoadedFile !== false) {
			$frsPackages = $simpleXmlLoadedFile->frs_package;
			$frsPackageStatus = array();
			foreach ($frsPackages as $frsPackage) {
				$frsPackageStatus[] = inject_package($g, $frsPackage, $project_path);
			}
			if (!in_array(false, $frsPackageStatus))
				$status = true;
		}
	}
	return $status;
}

function inject_package(&$g, $frsPackage, $project_path) {
	global $adminUser;
	$continue = true;
	if ((string)$frsPackage->isDeleted == "true")
		return $continue;
	$packagename = trim((string)$frsPackage->title);
	$packagename = preg_replace("/[^-a-zA-Z0-9+_\. ~]/", "-", $packagename);
	if (strlen($packagename) < MINSIZE__FRS_PACKAGE_NAME) {
		echo 'Information: package name too short. Extended to >'.MINSIZE__FRS_PACKAGE_NAME."\n";
		$packagename .= '-migrated';
		echo 'New package name: '.$packagename."\n";
	}
	$package_xid = (string)$frsPackage['xid'];
	$package = new FRSPackage ($g);
	$r = $package->create($packagename);
	if ($package->isError() || !$r) {
		echo 'Error when creating FRS package '.$packagename.': '.$package->getErrorMessage()."\n";
		$package->clearError();
		$continue = false;
	} else {
		$resxid = db_query_params('insert into ctf_mapping (xid, ffobject, ffid) values ($1, $2, $3)', array($package_xid, 'FRSPackage', $package->getID()));
		echo 'FRS package: '.$packagename.' injected'."\n";
	}
	if ($continue) {
		$frsReleases = $frsPackage->frsReleases->frs_release;
		$frsReleaseStatus = array();
		foreach ($frsReleases as $frsRelease) {
			$frsReleaseStatus[] = inject_release($package, $frsRelease, $project_path);
		}
		if (in_array(false, $frsReleaseStatus))
			$continue = false;
	}
	return $continue;
}

function inject_release(&$package, $frsRelease, $project_path) {
	global $adminUser;
	$continue = true;
	if ((string)$frsRelease->isDeleted == "true")
		return $continue;
	$releasename = trim((string)$frsRelease->title);
	$releasename = preg_replace("/[^-a-zA-Z0-9+_\. ~]/", "-", $releasename);
	$notes = trim((string)$frsRelease->description);
	$changes = "";
	$preformatted = (substr_count($notes, "\n"));
	$release_date = (string)$frsRelease->dateCreated;
	$release_xid = (string)$frsRelease['xid'];
	$release = new FRSRelease($package);
	$r = $release->create($releasename, $notes, $changes, $preformatted ,($release_date)?strtotime($release_date):false);
	if ($release->isError() || !$r) {
		echo 'Error when creating FRS release '.$releasename.': '.$release->getErrorMessage()."\n";
		$release->clearError();
		$continue = false;
	} else {
		$resxid = db_query_params('insert into ctf_mapping (xid, ffobject, ffid) values ($1, $2, $3)', array($release_xid, 'FRSRelease', $release->getID()));
		echo 'FRS release '.$releasename.' injected'."\n";
	}
	if ($continue) {
		if (strtolower(trim((string)$frsRelease->status)) != "active") {
			// not active = hidden = 3
			$release->update(3, $releasename, $notes, $changes, $preformatted ,($release_date)?strtotime($release_date):false);
		}
		$frsFiles = $frsRelease->frsFiles->frs_file;
		$frsFileStatus = array();
		foreach ($frsFiles as $frsFile) {
			$frsFileStatus[] = inject_file($release, $frsFile, $project_path);
		}
		if (in_array(false, $frsFileStatus))
			$continue = false;
	}
	return $continue;
}

function inject_file(&$release, $frsFile, $project_path) {
	global $adminUser;
	$continue = true;
	if ((string)$frsFile->isDeleted == "true")
		return $continue;
	$filename = (string)$frsFile->attach['fileDisplayName'];
	$filename = preg_replace("/[^-a-zA-Z0-9+_\. ~]/", "-", $filename);
	$file_location = $project_path.'/'.(string)$frsFile->attach['filename'];
	$type_id = 100;
	$processor_id = 100;
	$release_time = (string)$frsFile->dateCreated;
	$file_xid = (string)$frsFile['xid'];
	$file = new FRSFile($release);
	$r = $file->create($filename, $file_location, $type_id, $processor_id, ($release_time)?strtotime($release_time):false);
	if ($file->isError() || !$r) {
		echo 'Error when creating FRS file '.$filename.': '.$file->getErrorMessage()."\n";
		$file->clearError();
		$continue = false;
	} else {
		$resxid = db_query_params('insert into ctf_mapping (xid, ffobject, ffid) values ($1, $2, $3)', array($file_xid, 'FRSFile', $file->getID()));
		echo 'FRS file '.$filename.' injected'."\n";
	}
	return $continue;
}

function computeXmldiscussionApplication(&$g, $project_path) {
	$status = false;
	if (is_file($project_path.'/project/applications/discussionApplication/discussionApplication.xml')) {
		$simpleXmlLoadedFile = simplexml_load_file($project_path.'/project/applications/discussionApplication/discussionApplication.xml');
		if ($simpleXmlLoadedFile !== false) {
			$discussionFora = $simpleXmlLoadedFile->discussion_forum;
			$discussionForumStatus = array();
			foreach ($discussionFora as $discussionForum) {
				$discussionForumStatus[] = inject_forum($g, $discussionForum, $project_path);
			}
			if (!in_array(false, $discussionForumStatus))
				$status = true;
		}
	}
	return $status;
}

function inject_forum(&$g, $discussionForum, $project_path) {
	$continue = true;
	if (strtolower(trim((string)$discussionForum->isDeleted)) == "true")
		return $continue;
	$forumname = trim((string)$discussionForum->title);
	if (!preg_match('/^([_\.0-9a-z-])*$/i',$forumname)) {
		echo 'Warning: '.$forumname.' is invalid'."\n";
		$forumname = preg_replace('/ /', '_', $forumname);
		echo 'Warning: forum name renamed into '.$forumname."\n";
	}
	$description = trim((string)$discussionForum->description);
 	$forum_xid = (string)$discussionForum['xid'];
	$forum = new Forum($g);
	// $send_all_posts_to = '', $create_default_message = 1
	$r=$forum->create($forumname, $description);
	if ($forum->isError() || !$r) {
		echo 'Error when creating discussion forum '.$forumname.': '.$forum->getErrorMessage()."\n";
		$forum->clearError();
		$continue = false;
	} else {
		$resxid = db_query_params('insert into ctf_mapping (xid, ffobject, ffid) values ($1, $2, $3)', array($forum_xid, 'Forum', $forum->getID()));
		echo 'Discussion forum '.$forumname.' injected'."\n";
	}
	return $continue;
}

function computeXmllinkedAppApplication(&$g, $project_path) {
	$status = false;
	if (is_file($project_path.'/project/applications/linkedAppApplication/linkedAppApplication.xml')) {
		$simpleXmlLoadedFile = simplexml_load_file($project_path.'/project/applications/linkedAppApplication/linkedAppApplication.xml');
		if ($simpleXmlLoadedFile !== false) {
			$linkedApps = $simpleXmlLoadedFile->linked_app;
			$linkedAppStatus = array();
			foreach ($linkedApps as $linkedApp) {
				$linkedAppStatus[] = inject_linkedapp($g, $linkedApp, $project_path);
			}
			if (!in_array(false, $linkedAppStatus))
				$status = true;
		}
	}
	return $status;
}

function inject_linkedapp(&$g, $linkedApp, $project_path) {
	if (strtolower(trim((string)$linkedApp->isDeleted)) == "true") {
		return true;
	}
	$linkedAppTitle = trim((string)$linkedApp->title);
	$linkedAppUrl = trim((string)$linkedApp->applicationUrl);
	$linkedAppOrder = (int)$linkedApp->displayOrder;
	$headermenuPlugin = plugin_get_object('headermenu');
	if ($headermenuPlugin->addLink($linkedAppUrl, $linkedAppTitle, '', 'groupmenu', 'url', $g->getID(), '', $linkedAppOrder)) {
		return true;
	}
	return false;
}

function util_session_set_new($user) {
	if (strlen($user) > MAXSIZE__USER_UNIXNAME)
		$user = substr($user, 0, MAXSIZE__USER_UNIXNAME);
	// check if this user exists. if yes, then start a new session with. Used by create document function
	// we use the last update value, not the value from the version itself.
	$userObject = user_get_object_by_name($user);
	if ($userObject && is_object($userObject) && !$userObject->isError())
		session_set_new($userObject->getID());
}

function get_ff_id($object_string, $xid) {
	$res = db_query_params('select ffid from ctf_mapping where xid = $1 and ffobject = $2', array($xid, $object_string));
	if ($res && db_numrows($res) == 1) {
		return db_result($res, 0, 'ffid');
	}
	return false;

}

function set_permission_in_role(&$permissionPerSectionArray, $ctfpermissionSettingRessource, $ffobject, $section, $value) {
	$ffid = get_ff_id($ffobject, $ctfpermissionSettingRessource);
	if ($ffid) {
		$permissionPerSectionArray[$section][$ffid] = $value;
	} else {
		echo 'Unable to find '.$ctfpermissionSettingRessource.' ressource in database. Not injected? Skipping permission setting.'."\n";
	}
}
