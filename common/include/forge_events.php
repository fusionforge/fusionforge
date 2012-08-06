<?php

abstract class ForgeEvent extends Plugin {
	function ForgeEvent () {
		$this->Plugin() ;
		$this->name = "event" ;
		$this->text = "event" ;
		$this->hooks[] = 'group_approve';
		$this->hooks[] = 'scm_admin_update';
		$this->hooks[] = 'site_admin_option_hook';
	}

	abstract function trigger_job($name);

	function group_approve($params) {
		return $this->trigger_job('create_scm_repos');
	}

	function scm_admin_update($params) {
		return $this->trigger_job('create_scm_repos');
	}

	function site_admin_option_hook($params) {
		$action = getStringFromRequest('action');
		echo '<li><a name="jobs"></a>'.util_make_link('/admin/?action=listjobs#jobs', _('Jobs'))."\n";
		if ($action == 'listjobs') {
			echo '<ul>';
			echo '<li>'.util_make_link('/admin/?action=runjobs&amp;job=create_scm_repos#jobs', _('Create SCM Repositories')).'</li>'."\n";
			echo '<li>'.util_make_link('/admin/?action=runjobs&amp;job=scm_update#jobs', _('Upgrade Forge Software')).'</li>'."\n";
			echo '</ul>';
		}
		echo '</li>';
		if ($action == 'runjobs') {
			$job = getStringFromRequest('job');
			$job = util_ensure_value_in_set($job, array('create_scm_repos', 'scm_update'));
			$this->trigger_job($job);
		}
		echo '<li><a name="version"></a>'.util_make_link('/admin/?action=version#version', _('Version'))."\n";
		if ($action == 'version') {
			echo '<pre>';
			if (is_dir("/opt/acosforge/.svn")) {
				system("cd /opt/acosforge; svn info --config-dir /tmp 2>&1");
			}
			if (is_dir("/opt/acosforge/.git")) {
				system("cd /opt/acosforge; git svn info 2>&1");
			}
			echo '</pre>'."\n";
		}
		echo '</li>';
	}
}

class PgForgeEvent extends ForgeEvent {
	function trigger_job($name) {
		return db_query_params("NOTIFY $name", array());
	}
}

register_plugin (new PgForgeEvent) ;

$pm = plugin_manager_get_object() ;
$pm->SetupHooks () ;
