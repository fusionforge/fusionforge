<?php
/**
 * FusionForge REST API server
 *
 * Copyright 2016, Alain Peyrat (Nokia)
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

/*
 * Auth: Currently only basic auth is implemented so have to be used with https only.
 *
 * Example:
 *    curl -s -u login:password https://<forge>/plugins/rest/index.php/v1/whoami
 *
 * Command will return a JSON formatted array with user_id and logged value.
 *
 * /v1/info                      - GET:
 * /v1/users                     - GET:
 * /v1/user/<login>              - GET:
 * /v1/projects                  - GET: Get all active projects
 * /v1/project/<unix_name>       - GET:
 * /v1/project/<unix_name>       - DELETE:
 * /v1/trackers/<group_id>       - GET: Get trackers available in project.
 * /v1/trackers/<group_id>/<id>  - GET: Get tracker definition of id
 * /v1/tracker/<id>              - GET:
 * /v1/tracker                   - POST: Create ticket (with {...})
 * /v1/tracker/<id>/comment      - POST: Add comment to ticket (with {"body":"<comment>"})
 * /v1/whoami                    - GET:
 */


$no_gz_buffer = true;
$no_debug     = true;

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/gettext.php';
require_once $gfcommon.'include/FusionForge.class.php';
require_once $gfcommon.'tracker/Artifacts.class.php';
require_once $gfcommon.'tracker/ArtifactFactory.class.php';
require_once $gfcommon.'tracker/Artifact.class.php';
require_once $gfcommon.'forum/ForumFactory.class.php';
require_once $gfplugins.'rest/common/AltoRouter.php';

ini_set('memory_limit', -1);
ini_set('max_execution_time', 0);

$sysdebug_enable = false;

// Disable error_reporting as it breaks XML generated output.
//error_reporting(0);

class Rest {
	static function json($data) {
		header('Content-Type: application/json; charset=UTF-8');
		//error_log($data);
		echo json_encode($data);
	}

	static function error($message, $code=500) {
		header("X-Status-Reason: $message", true, $code);
	}

	static function renderUser($user) {
		return array(
		'user_id' => $user->getID(),
		'user_name' => $user->getUnixName(),
		'title' => $user->getTitle(),
		'firstname' => $user->getFirstName(),
		'lastname' => $user->getLastName(),
		'status' => $user->getStatus(),
		'email' => $user->getEmail(),
		'language' => lang_id_to_language_name($user->getLanguage()),
		'country_code' => $user->getCountryCode(),
		'add_date' => date('c', $user->getAddDate()),
		//'last_login_time' => date('c', $user->getLastLoginTime())
		);
	}

	static function renderProject($project) {
		return array(
		'group_id' => $project->getID(),
		'group_name' => $project->getPublicName(),
		'homepage' => $project->getHomePage(),
		'is_public' => $project->isPublic(),
		'status' => $project->getStatus(),
		'unix_group_name' => $project->getUnixName(),
		'short_description' => $project->getDescription(),
		'register_purpose' => $project->getRegisterPurpose(),
		'unix_box' => $project->getUnixBox(),
		'scm_box' => $project->getSCMBox(),
		'register_time' => date('c', $project->getStartDate()),
		//'built_from_template' => $project->getTemplateProject(),
		'use_mail' => $project->usesMail(),
		'use_survey' => $project->usesSurvey(),
		'use_forum' => $project->usesForum(),
		'use_pm' => $project->usesPM(),
		//'use_pm_depend_box' => $grps[$i]->data_array['use_pm_depend_box'],
		'use_scm' => $project->usesSCM(),
		'use_news' => $project->usesNews(),
		'use_docman' => $project->usesDocman(),
		'new_doc_address' => $project->getDocEmailAddress(),
		'send_all_docs' => $project->docEmailAll(),
		'use_ftp' => $project->usesFTP(),
		'use_tracker' => $project->usesTracker(),
		'use_frs' => $project->usesFRS(),
		'use_stats' => $project->usesStats(),
		'use_activity' => $project->usesActivity(),
		'tags'=>$project->getTags(),
		'admins'=> array_map('rest::renderUser', $project->getAdmins()),
		//'last_update_time' => date('c', $project->getLastUpdateTime())
		);
	}

	static function renderArtifactType($artifact) {
		return array(
		'artifact_id' => $artifact->getID(),
		'name' => $artifact->getName(),
		'description' => $artifact->getDescription(),
		'unix_name' => $artifact->getUnixName(),
		'is_public' => $artifact->isPublic(),
		'allow_anon' => $artifact->allowsAnon(),
		'email_all' => $artifact->emailAll(),
		'email_address' => $artifact->getEmailAddress(),
		'due_period' => $artifact->getDuePeriod() / (60*60*24),
		'use_resolution' => false,
		'datatype' => $artifact->getDataType(),
		'submit_instructions' => $artifact->getSubmitInstructions(),
		'browse_instructions' => $artifact->getBrowseInstructions()
		);
	}

	static function renderArtifact($artifact) {
		return array(
		'artifact_id' => $artifact->getID(),
		'summary' => $artifact->getSummary(),
		'details' => $artifact->getDetails(),
		'status_name' => $artifact->getStatusName(),
		'submitted_by' => $artifact->getSubmittedUnixName(),
		'open_date' => date('c', $artifact->getOpenDate()),
		'close_date' => date('c', $artifact->getCloseDate()),
		'last_modified_date' => date('c', $artifact->getLastModifiedDate()),
		'comments' => array_map('rest::renderArtifactComment', $artifact->getMessageObjects())
		);
	}

	static function renderArtifactComment($message) {
		return array(
		'message' => util_unconvert_htmlspecialchars($message->getBody()),
		'user_id' => $message->getUserID(),
		'add_date' => date('c', $message->getAddDate())
		);
	}

	static function renderForumType($forum) {
		return array(
		'forum_id' => $forum->getID(),
		'name' => $forum->getName(),
		'description' => $forum->getDescription(),
		'send_all_posts_to' => $forum->getSendAllPostsTo(),
		'is_public' => $forum->isPublic(),
		'moderation_level' => $forum->getModerationLevel()
		);
	}

	static function renderForum($message) {
		return array(
		'id' => $message->getID(),
		'body' => $message->getBody(),
		'user_id' => $message->getUserID(),
		'add_date' => date('c', $message->getAddDate())
		);
	}

}

$router = new AltoRouter();

$router->map('GET', '/v1/info', function () {
	$users = user_get_active_users();
	if (!$users) {
		return Rest::error('Could Not Get Active Users');
	}
	$projects = group_get_active_projects();
	if (!$projects) {
		return Rest::error('Could Not Get Active Projects');
	}

	$ff = new FusionForge();
	$info = array(
		'name' => $ff->software_name,
		'version' => $ff->software_version,
		'forge_name' => forge_get_config('forge_name'),
		'users' => count($users),
		'projects' => count($projects),
		'php_version' => PHP_VERSION,
	);

	Rest::json($info);
});

$router->map('GET', '/v1/whoami', function () {
	$response = array(
		'user_id' => user_getid(),
		'logged' => session_loggedin()
	);

	Rest::json($response);
});

$router->map('GET', '/v1/users', function () {
	$users = user_get_active_users();
	if (!$users) {
		return Rest::error('Could Not Get Active Users');
	}

	Rest::json(array_map("Rest::renderUser", $users));
});

$router->map('GET', '/v1/user/[a:login]', function ($login) {
	$users = user_get_objects_by_name(array($login));
	if (!$users) {
		return Rest::error('Could Not Get User: '.$login);
	}

	Rest::json(Rest::renderUser($users[0]));
});

$router->map('GET', '/v1/projects', function () {
	$projects = group_get_active_projects();
	if (!$projects) {
		return Rest::error('Could Not Get Active Projects');
	}

	Rest::json(array_map("Rest::renderProject", $projects));
});

$router->map('GET', '/v1/project/[a:name]', function ($name) {
	$project = group_get_object_by_name($name);
	if (!$project) {
		return Rest::error('Could Not Get Project: '.$name);
	}

	Rest::json(Rest::renderProject($project));
});

$router->map('POST', '/v1/project', function () {
	// get and decode JSON request body
	$input = json_decode(file_get_contents('php://input'));
	$user = session_get_user();
	$project = new Group();
	$ret = $project->create(
		$user,
		$input->group_name,
		$input->unix_group_name,
		$input->short_description,
		$input->register_purpose,
		$input->unix_box,
		$input->scm_box,
		$input->is_public,
		false, // no mail for now
		0); // built_from_template (missing)
	if (!$ret) {
		return Rest::error($project->getErrorMessage());
	}

	if (!$project->approve($user)) {
		return Rest::error($project->getErrorMessage());
	}
	$ret = $project->update(
		$user,
		$input->group_name,
		$input->homepage,
		$input->short_description,
		$input->use_mail,
		$input->use_survey,
		$input->use_forum,
		$input->use_pm,
		false, // $use_pm_depend_box,
		$input->use_scm,
		$input->use_news,
		$input->use_docman,
		$input->new_doc_address,
		$input->send_all_docs,
		0, // $logo_image_id,
		$input->use_ftp,
		$input->use_tracker,
		$input->use_frs,
		$input->use_stats,
		$input->tags,
		$input->use_activity,
		$input->is_public);
	if (!$ret) {
		return Rest::error($project->getErrorMessage());
	}

	return Rest::json(Rest::renderProject($project));
});

$router->map('DELETE', '/v1/project/[a:name]', function ($name) {
	$project = group_get_object_by_name($name);
	if (!$project) {
		return Rest::error('Could Not Get Project: '.$name);
	}
	if (!$project->delete(true, true, true)) {
		return Rest::error('Failed: '.$project->getErrorMessage());
	}

	Rest::json(true); // needed?
});

$router->map('GET', '/v1/trackers/[i:group_id]', function ($group_id) {
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return Rest::error('Could Not Get Project');
	} elseif ($grp->isError()) {
		return Rest::error($grp->getErrorMessage());
	}

	$atf = new ArtifactTypeFactory($grp);
	if (!$atf || !is_object($atf) || $atf->isError()) {
		return Rest::error('Could Not Get ArtifactTypeFactory');
	}

	$at_arr = $atf->getArtifactTypes();
	if ($at_arr === false) {
		return Rest::error('Permission denied');
	}

	Rest::json(array_map("Rest::renderArtifactType", $at_arr));
});

$router->map('POST', '/v1/trackers/[i:group_id]', function ($group_id) {
	$input = json_decode(file_get_contents('php://input'));

	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return Rest::error('Could Not Get Project');
	} elseif ($grp->isError()) {
		return Rest::error($grp->getErrorMessage());
	}

	$atf = new ArtifactType($grp);
	if (!$atf || !is_object($atf) || $atf->isError()) {
		return Rest::error('Could Not Get ArtifactTypeFactory');
	}

	$ret = $atf->create(
		$input->name,
		$input->description,
		$input->is_public,
		$input->allow_anon,
		$input->email_all,
		$input->email_address,
		$input->due_period,
		$input->use_resolution,
		$input->submit_instructions,
		$input->browse_instructions,
		$input->datatype = 0);
	if (!$ret) {
		return Rest::error($atf->getErrorMessage());
	}

	return(Rest::json(Rest::renderArtifactType($atf)));
});


$router->map('GET', '/v1/trackers/[i:group_id]/[i:group_artifact_id]', function ($group_id, $group_artifact_id) {
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return Rest::error('Could Not Get Project');
	} elseif ($grp->isError()) {
		return Rest::error($grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at)) {
		return Rest::error('Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return Rest::error($at->getErrorMessage());
	}

	$af = new ArtifactFactory($at);
	if (!$af || !is_object($af)) {
		return Rest::error('Could Not Get ArtifactFactory');
	} elseif ($af->isError()) {
		return Rest::error($af->getErrorMessage());
	}

	$af->setup(0,'','',0,false, '', '');
	$artifacts = $af->getArtifacts();
	if ($artifacts === false) {
		return Rest::error($af->getErrorMessage());
	}

	Rest::json(array_map("Rest::renderArtifact", $artifacts));
});

$router->map('GET', '/v1/tracker/[i:id]', function ($id) {
	$artifact = artifact_get_object($id);
	if (!$artifact) {
		return Rest::error('Could Not Get Artifact: '.$id);
	}

	Rest::json(Rest::renderArtifact($artifact));
});

// Create an artifact, requires (group_id, group_artifact_id, summary, details)
$router->map('POST', '/v1/tracker', function () {
	// get and decode JSON request body
	$input = json_decode(file_get_contents('php://input'));

	$grp = group_get_object($input->group_id);
	if (!$grp || !is_object($grp)) {
		return Rest::error('Could Not Get Project');
	} elseif ($grp->isError()) {
		return Rest::error($grp->getErrorMessage());
	}

	$at = new ArtifactType($grp, $input->group_artifact_id);
	if (!$at || !is_object($at)) {
		return Rest::error('Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return Rest::error($at->getErrorMessage());
	}

	$a = new Artifact($at);
	if (!$a || !is_object($a)) {
		return Rest::error('Could Not Get Artifact');
	} elseif ($a->isError()) {
		return Rest::error($a->getErrorMessage());
	}

	if (!$a->create($input->summary, $input->details)) {
		return Rest::error('Could Not Create Artifact: '.$a->getErrorMessage());
	}

	if ($input->comments) {
		foreach($input->comments as $comment) {
		$res = $a->addMessage($comment->message);
		if (!$res) {
			return Rest::error('Could Not Create Artifact Comment: '.$a->getErrorMessage());
		}

		$msg_id = db_insertid($res, 'artifact_message', 'id');
		$add_date = strtotime($comment->add_date);
		db_query_params('UPDATE artifact_message SET submitted_by=$1, adddate=$2 WHERE id=$3',
			array ($comment->user_id, $add_date, $msg_id));
		}
	}

	Rest::json(Rest::renderArtifact($a));
});

// Add a comment to an artifact, requires (body)
$router->map('POST', '/v1/tracker/[i:id]/comment', function ($id) {
	// get and decode JSON request body
	$input = json_decode(file_get_contents('php://input'));

	$artifact = artifact_get_object($id);
	if (!$artifact) {
		return Rest::error('Could Not Get Artifact: '.$id);
	}

	if (!$artifact->addMessage($input->body)) {
		return Rest::error('Could Not Create Artifact: '.$artifact->getErrorMessage());
	}

	Rest::json(Rest::renderArtifact($artifact));
});

$router->map('GET', '/v1/forums/[i:group_id]', function ($group_id) {
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return Rest::error('Could Not Get Project');
	} elseif ($grp->isError()) {
		return Rest::error($grp->getErrorMessage());
	}

	$ff = new ForumFactory($grp);
	if (!$ff || !is_object($ff)) {
		return Rest::error('Could Not Get ForumFactory');
	} elseif ($ff->isError()) {
		return Rest::error($ff->getErrorMessage());
	}

	Rest::json(array_map("Rest::renderForumType", $ff->getForums()));
});

// match current request url
$router->setBasePath('/plugins/rest');
$match = $router->match();

if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
	session_login_valid($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
}

// call closure or throw 404 status
if( $match && is_callable( $match['target'] ) ) {
	call_user_func_array( $match['target'], $match['params'] );
} else {
	// no route was matched
	header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
}
