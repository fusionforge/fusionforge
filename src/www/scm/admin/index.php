<?php
/**
 * SCM Frontend
 *
 * Copyright 2004 (c) Roland Mas, Tim Perdue GForge LLC
 * Copyright 2018, Franck Villaume - TrivialDev
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'project/admin/project_admin_utils.php';
require_once $gfwww.'scm/include/scm_utils.php';
require_once $gfcommon.'scm/SCMFactory.class.php';
require_once $gfcommon.'include/SysTasksQ.class.php';

global $HTML;

html_use_jquery();
html_use_coolfieldset();

$group_id = getIntFromRequest('group_id');
$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
    exit_no_group();
} elseif ($group->isError()) {
	exit_error($group->getErrorMessage(), 'scm');
}

// Check permissions
session_require_perm('project_admin', $group_id);

if (getStringFromRequest('form_create_repo')) {
	$hook_params = array();
	$hook_params['group_id'] = $group_id;
	plugin_hook('scm_admin_form', $hook_params);
	exit;
}

$systasksq = new SysTasksQ();

if (getStringFromRequest('create_repository') && getStringFromRequest('submit')) {
	$repo_name = trim(getStringFromRequest('repo_name'));
	$description = preg_replace('/[\r\n]/', ' ', getStringFromRequest('description'));
	$clone = getStringFromRequest('clone');
	$hook_params = array () ;
	$hook_params['group_id'] = $group_id;
	$hook_params['repo_name'] = $repo_name;
	$hook_params['description'] = $description;
	$hook_params['clone'] = $clone;
	$hook_params['error_msg'] = '';
	$hook_params['scm_plugin'] = getStringFromRequest('scm_plugin');
	plugin_hook_by_reference('scm_add_repo', $hook_params);
	if ($hook_params['error_msg']) {
		$error_msg = $hook_params['error_msg'];
	}
	else {
		$feedback = sprintf(_('New repository %s registered, will be created shortly.'), $repo_name);
		$systasksq->add(SYSTASK_CORE, 'SCM_REPO', $group_id);
	}
} elseif (getStringFromRequest('delete_repository') && getStringFromRequest('submit')) {
	$repo_name = trim(getStringFromRequest('repo_name'));

	$hook_params = array () ;
	$hook_params['group_id'] = $group_id;
	$hook_params['repo_name'] = $repo_name;
	$hook_params['error_msg'] = '';
	$hook_params['scm_plugin_id'] = getIntFromRequest('scm_plugin_id');
	plugin_hook_by_reference('scm_delete_repo', $hook_params);
	if ($hook_params['error_msg']) {
		$error_msg = $hook_params['error_msg'];
	}
	else {
		$feedback = sprintf(_('Repository %s is marked for deletion (actual deletion will happen shortly).'), $repo_name);
		$systasksq->add(SYSTASK_CORE, 'SCM_REPO', $group_id);
	}
} elseif (getStringFromRequest('submit')) {
	$hook_params = array();
	$hook_params['group_id'] = $group_id;

	$scmarray = array();
	$scmvars = array_keys(_getRequestArray());
	foreach (_getRequestArray() as $key => $value) {
		foreach ($scm_list as $scm) {
			if ($key == strstr($key, $scm . "_")) {
				$hook_params[$key] = $value;
			}
			else {
				$hook_params[$scm] = getArrayFromRequest($scm);
			}
		}
		if ($key == strstr($key, "scm_")) {
			$hook_params[$key] = $value;
		} elseif ($key == 'scmengine') {
			if (is_array($value)) {
				$scmarray = $value;
			} else {
				$scmarray = array($value);
			}
		}
	}

	$SCMFactory = new SCMFactory();
	$scm_plugins = $SCMFactory->getSCMs();

	$scm_changed = false;

	foreach ($scm_plugins as $plugin) {
		$myPlugin = plugin_get_object($plugin);
		if (in_array($myPlugin->name, $scmarray)) {
			if (!$group->usesPlugin($myPlugin->name)) {
				$group->setPluginUse($myPlugin->name, 1);
				if ($myPlugin->getDefaultServer()) {
					$group->setSCMBox($myPlugin->getDefaultServer());
				}
				$scm_changed = true;
			}
		} else {
			if ($group->usesPlugin($myPlugin->name)) {
				$group->setPluginUse($myPlugin->name, 0);
				$scm_changed = true;
			}
		}
	}

	if (!$scm_changed) {
		// Don't call scm plugin update if their form wasn't displayed
		// to avoid processing an apparently empty form and reset configuration
		plugin_hook("scm_admin_update", $hook_params);
	}
} elseif (getStringFromRequest('scmhook_submit')) {
	$hook_params = array();
	$hook_params['group_id'] = $group_id;
	$repos = getArrayFromRequest('repository', array());
	foreach ($repos as $repo => $hook_elements) {
		$hook_params['repository_name'] = $repo;
		$hook_params['hooks'] = $hook_elements;
		$hook_params['scm_plugin'] = getStringFromRequest('scm_plugin');
		$hook_options = array();
		foreach ($hook_elements as $hook_element) {
			$hook_params['hooks']['options'] = array();
			$options = getArrayFromRequest($hook_element, array());
			if (isset($options[$repo])) {
				$hook_params['hooks']['options'] = $options[$repo];
			}
		}
		$scmhookPlugin = plugin_get_object('scmhook');
		$scmhookPlugin->update($hook_params);
	}
}

scm_header(array('title'=>_('SCM Repository'),'group'=>$group_id));
echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
	$(document).ready(function() {
		$("input[name='scmengine[]']").change(function() {
			$("input[name='scmengine[]']").each(function () {
				$('#div_'+$(this).val()).hide();
			});
			$('#div_'+$("input[name='scmengine[]']:checked").val()).show();
		});
	});
//]]>
<?php
echo html_ac(html_ap() - 1);
echo $HTML->openForm(array('method' => 'post', 'action' => '/scm/admin/?group_id='.$group_id));
$hook_params = array () ;
$hook_params['group_id'] = $group_id ;

$SCMFactory = new SCMFactory();
$scm_plugins = $SCMFactory->getSCMs();
$scmPluginObjects = array();
if (!empty($scm_plugins)) {
	echo $HTML->information(_('Note: Changing the repository does not delete the previous repository. It only affects the information displayed under the SCM tab.'));
	if (count($scm_plugins) == 1) {
		$myPlugin = plugin_get_object($scm_plugins[0]);
		echo html_e('input', array('type' => 'hidden', 'name' => 'scmengine[]', 'value' => $myPlugin->name));
		echo html_e('p', array(), html_e('input', array('type' => 'radio', 'name' => 'fake', 'disabled' => 'disabled', 'checked' => 'checked')).$myPlugin->text);
		$scm = $myPlugin->name;
	} else {
		echo html_e('h2', array(), _('SCM Repository'));
		foreach ($scm_plugins as $plugin) {
			$myPlugin = plugin_get_object($plugin);
			$inputAttr = array('name' => 'scmengine[]', 'value' => $myPlugin->name);
			if (forge_get_config('allow_multiple_scm')) {
					$inputAttr['type'] = 'checkbox';
			} else {
					$inputAttr['type'] = 'radio';
			}
			if ($group->usesPlugin($myPlugin->name)) {
				$scmPluginObjects[] = $myPlugin;
				$scm = $myPlugin->name;
				$inputAttr['checked'] = 'checked';
			}
			echo html_e('input', $inputAttr).$myPlugin->text;
		}
	}
} else {
	echo $HTML->error_msg(_('Error')._(': ')._('Site has SCM but no plugins registered'));
}

(isset($scm)) ? $hook_params['scm_plugin'] = $scm : $hook_params['scm_plugin'] = 0;
$hook_params['allow_multiple_scm'] = forge_get_config('allow_multiple_scm');
plugin_hook_by_reference("scm_admin_page", $hook_params);
echo html_e('input', array('type' => 'hidden', 'name' => 'group_id', 'value' => $group_id));
echo html_e('p', array(), html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Update'))));
echo $HTML->closeForm();

if (forge_get_config('allow_multiple_scm') && (count($scmPluginObjects) > 1)) {
	$elementsLi = array();
	foreach ($scmPluginObjects as $scmPluginObject) {
		$elementsLi[] = array('content' => util_make_link('#tabber-'.$scmPluginObject->name, $scmPluginObject->text, false, true));
	}
	echo html_ao('div', array('id' => 'tabberid'));
	echo $HTML->html_list($elementsLi);
}

$hook_params['allow_multiple_scm'] = count($scmPluginObjects);
plugin_hook('scm_admin_form', $hook_params);

if (forge_get_config('allow_multiple_scm') && (count($scmPluginObjects) > 1)) {
	echo html_ac(html_ap() - 1);
}

echo html_e('script', array('type'=>'text/javascript'), '//<![CDATA['."\n".'jQuery("[id^=tabber]").tabs();'."\n".'//]]>');
scm_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
