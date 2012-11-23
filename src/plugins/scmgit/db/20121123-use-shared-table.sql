INSERT INTO scm_secondary_repos (SELECT o.group_id, p.plugin_id, o.repo_name, o.clone_url, o.description, o.next_action FROM plugin_scmgit_secondary_repos o, plugins p WHERE p.plugin_name='scmgit');
INSERT INTO scm_personal_repos (SELECT o.group_id, p.plugin_id, o.user_id, 0 FROM plugin_scmgit_personal_repos o, plugins p WHERE p.plugin_name='scmgit');
