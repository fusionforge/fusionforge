CREATE OR REPLACE FUNCTION pfo_rbac_permissions_from_old (rid integer, nsec text, nref integer) RETURNS integer AS $$
DECLARE
	os role_setting%ROWTYPE ;
	onsec text ;
	onref integer ;
	onval integer ;
	r pfo_role%ROWTYPE ;
	mastergroupid integer := 1 ;
	newsgroupid integer := 0 ;
	statsgroupid integer := 0 ;
	opid integer := 0 ;
	tmp integer := 0 ;
BEGIN
	SELECT group_id INTO newsgroupid FROM groups WHERE unix_group_name = 'newsadmin' ;
	SELECT group_id INTO statsgroupid FROM groups WHERE unix_group_name = 'stats' ;

	SELECT * INTO r FROM pfo_role WHERE old_role_id = rid ;

	IF nsec = 'project_read' AND nref = r.home_group_id THEN
	   RETURN 1 ;
	END IF ;

	FOR os IN SELECT * FROM role_setting WHERE role_id = rid ORDER BY role_id, section_name, ref_id
	LOOP
		SELECT group_id INTO opid FROM role WHERE role_id = os.role_id ;

		IF os.section_name = 'projectadmin' THEN
		   CONTINUE WHEN os.value != 'A' ;
		   IF nsec = 'project_admin' AND nref = opid THEN
		      RETURN 1 ;
		   END IF ;

		ELSIF os.section_name IN ('trackeradmin', 'pmadmin', 'forumadmin') THEN
		   CONTINUE WHEN os.value != '2' ;
		   onsec = CASE WHEN os.section_name = 'trackeradmin' THEN 'tracker_admin'
		   	       WHEN os.section_name = 'pmadmin' THEN 'pm_admin'
		   	       WHEN os.section_name = 'forumadmin' THEN 'forum_admin' END ;
		   IF nsec = onsec AND nref = opid THEN
		      RETURN 1 ;
		   END IF ;

		ELSIF os.section_name IN ('tracker', 'newtracker') THEN
		   CONTINUE WHEN os.value = '-1' ;
		   onsec = CASE WHEN os.section_name = 'tracker' THEN os.section_name
		   	       WHEN os.section_name = 'newtracker' THEN 'new_tracker' END ;
		   onref = CASE WHEN os.section_name = 'tracker' THEN os.ref_id
		   	       WHEN os.section_name = 'newtracker' THEN opid END ;
		   onval = CASE WHEN os.value = '0' THEN 1
		   	       WHEN os.value = '1' THEN 3
		   	       WHEN os.value = '2' THEN 7
		   	       WHEN os.value = '3' THEN 5 END ;
		   IF nsec = onsec AND nref = onref THEN
		      RETURN onval ;
		   END IF ;

		ELSIF os.section_name IN ('pm', 'newpm') THEN
		   CONTINUE WHEN os.value = '-1' ;
		   onsec = CASE WHEN os.section_name = 'pm' THEN os.section_name
		   	       WHEN os.section_name = 'newpm' THEN 'new_pm' END ;
		   onref = CASE WHEN os.section_name = 'pm' THEN os.ref_id
		   	       WHEN os.section_name = 'newpm' THEN opid END ;
		   onval = CASE WHEN os.value = '0' THEN 1
		   	       WHEN os.value = '1' THEN 3
		   	       WHEN os.value = '2' THEN 7
		   	       WHEN os.value = '3' THEN 5 END ;
		   IF nsec = onsec AND nref = onref THEN
		      RETURN onval ;
		   END IF ;

		ELSIF os.section_name = 'forum' THEN
		   CONTINUE WHEN os.value = '-1' ;
		   onsec = os.section_name ;
		   onref = os.ref_id ;
		   SELECT moderation_level INTO tmp FROM forum_group_list WHERE group_forum_id = onref ;
		   onval = CASE WHEN os.value = '0' THEN 1
		   	       WHEN os.value = '1' AND tmp >= 2 THEN 2
		   	       WHEN os.value = '1' AND tmp <= 1 THEN 3
		   	       WHEN os.value = '2' THEN 4 END ;
		   IF nsec = onsec AND nref = onref THEN
		      RETURN onval ;
		   END IF ;

		ELSIF os.section_name = 'newforum' THEN
		   CONTINUE WHEN os.value = '-1' ;
		   onsec = 'new_forum' ;
		   onref = opid ;
		   onval = CASE WHEN os.value = '0' THEN 1
		   	       WHEN os.value = '1' THEN 2
		   	       WHEN os.value = '2' THEN 4 END ;
		   IF nsec = onsec AND nref = onref THEN
		      RETURN onval ;
		   END IF ;

		ELSIF os.section_name = 'docman' THEN
		   onsec = os.section_name ;
		   onref = opid ;
		   onval = CASE WHEN os.value = '0' THEN 1
		   	       WHEN os.value = '1' THEN 4 END ;
		   IF nsec = onsec AND nref = onref THEN
		      RETURN onval ;
		   END IF ;

		ELSIF os.section_name = 'frs' THEN
		   onsec = os.section_name ;
		   onref = opid ;
		   onval = CASE WHEN os.value = '0' THEN 1
		   	       WHEN os.value = '1' THEN 3 END ;
		   IF nsec = onsec AND nref = onref THEN
		      RETURN onval ;
		   END IF ;

		ELSIF os.section_name = 'scm' THEN
		   CONTINUE WHEN os.value = '-1' ;
		   onsec = os.section_name ;
		   onref = opid ;
		   onval = CASE WHEN os.value = '0' THEN 1
		   	       WHEN os.value = '1' THEN 2 END ;
		   IF nsec = onsec AND nref = onref THEN
		      RETURN onval ;
		   END IF ;

		ELSIF os.section_name = 'webcal' THEN
		   CONTINUE WHEN os.value = '0' ;
		   onsec = os.section_name ;
		   onref = opid ;
		   onval = os.value ;
		   IF nsec = onsec AND nref = onref THEN
		      RETURN onval ;
		   END IF ;

		ELSIF os.section_name = 'plugin_mediawiki_edit' THEN
		   CONTINUE WHEN os.value = '0' ;
		   onsec = os.section_name ;
		   onref = opid ;
		   onval = os.value ;
		   IF nsec = onsec AND nref = onref THEN
		      RETURN onval ;
		   END IF ;

		ELSE
		   RAISE EXCEPTION 'Unknown setting % for role %', os.section_name, os.role_id ;
		   CONTINUE WHEN os.value = '0' ;
		   onsec = os.section_name ;
		   onref = os.ref_id ;
		   onval = os.value::integer ;
		   IF nsec = onsec AND nref = onref THEN
		      RETURN onval ;
		   END IF ;

		END IF ;

	END LOOP ;

	RETURN 0 ;

END ;
$$ LANGUAGE plpgsql ;

CREATE OR REPLACE FUNCTION migrate_role_observer_to_pfo_rbac () RETURNS void AS $$
DECLARE
	g groups%ROWTYPE ;
	t artifact_group_list%ROWTYPE ;
	f forum_group_list%ROWTYPE ;
	p project_group_list%ROWTYPE ;
	need_loggedin boolean := false ;
BEGIN
	FOR g IN SELECT * FROM groups WHERE is_public = 1
	LOOP
		INSERT INTO role_project_refs VALUES (1, g.group_id) ;
		INSERT INTO role_project_refs VALUES (2, g.group_id) ;
		PERFORM insert_pfo_role_setting (1, 'project_read', g.group_id, 1) ;
		PERFORM insert_pfo_role_setting (1, 'new_tracker', g.group_id, 1) ;
		PERFORM insert_pfo_role_setting (1, 'new_pm', g.group_id, 1) ;
		PERFORM insert_pfo_role_setting (1, 'new_forum', g.group_id, 1) ;
		PERFORM insert_pfo_role_setting (1, 'frs', g.group_id, 1) ;
		PERFORM insert_pfo_role_setting (2, 'project_read', g.group_id, 1) ;
		PERFORM insert_pfo_role_setting (2, 'new_tracker', g.group_id, 1) ;
		PERFORM insert_pfo_role_setting (2, 'new_pm', g.group_id, 1) ;
		PERFORM insert_pfo_role_setting (2, 'new_forum', g.group_id, 1) ;
		PERFORM insert_pfo_role_setting (2, 'frs', g.group_id, 1) ;

		IF g.enable_anonscm = 1 THEN
		   PERFORM insert_pfo_role_setting (1, 'scm', g.group_id, 1) ;
		   PERFORM insert_pfo_role_setting (2, 'scm', g.group_id, 1) ;
		END IF ;

		FOR t IN SELECT * FROM artifact_group_list WHERE group_id = g.group_id AND is_public = 1
		LOOP
			IF t.allow_anon = 1 THEN
			   PERFORM insert_pfo_role_setting (1, 'tracker', t.group_artifact_id, 1) ;
			END IF ;

			PERFORM insert_pfo_role_setting (2, 'tracker', t.group_artifact_id, 1) ;
		END LOOP ;

		FOR p IN SELECT * FROM project_group_list WHERE group_id = g.group_id AND is_public = 1
		LOOP
			PERFORM insert_pfo_role_setting (1, 'pm', p.group_project_id, 1) ;
			PERFORM insert_pfo_role_setting (2, 'pm', p.group_project_id, 1) ;
		END LOOP ;

		FOR f IN SELECT * FROM forum_group_list WHERE group_id = g.group_id AND is_public = 1
		LOOP
			IF f.allow_anonymous = 1 THEN
			   IF f.moderation_level = 0 THEN
			      PERFORM insert_pfo_role_setting (1, 'forum', f.group_forum_id, 3) ;
			   ELSE
			      PERFORM insert_pfo_role_setting (1, 'forum', f.group_forum_id, 2) ;
			   END IF ;
			ELSE
			   PERFORM insert_pfo_role_setting (1, 'forum', f.group_forum_id, 1) ;
			END IF ;

			IF f.moderation_level = 0 THEN
			   PERFORM insert_pfo_role_setting (2, 'forum', f.group_forum_id, 3) ;
			ELSE
			   PERFORM insert_pfo_role_setting (2, 'forum', f.group_forum_id, 2) ;
			END IF ;
		END LOOP ;

	END LOOP ;

END ;
$$ LANGUAGE plpgsql ;

CREATE OR REPLACE FUNCTION pfo_rbac_full_migration () RETURNS void AS $$
DECLARE
	mastergroupid integer := 1 ;
	newsgroupid integer := 0 ;
	statsgroupid integer := 0 ;
BEGIN
	DELETE FROM pfo_user_role ;
	DELETE FROM pfo_role_setting ;
	DELETE FROM role_project_refs ;
	DELETE FROM pfo_role ;
	DELETE FROM pfo_role_class ;

	INSERT INTO pfo_role_class (class_id, class_name) VALUES (1, 'PFO_RoleExplicit') ;
	INSERT INTO pfo_role_class (class_id, class_name) VALUES (2, 'PFO_RoleAnonymous') ;
	INSERT INTO pfo_role_class (class_id, class_name) VALUES (3, 'PFO_RoleLoggedIn') ;

	PERFORM setval ('pfo_role_class_seq', 3) ;

	INSERT INTO pfo_role (role_id, role_name, role_class, is_public) VALUES (1, 'Anonymous', 2, true) ;
	INSERT INTO pfo_role (role_id, role_name, role_class, is_public) VALUES (2, 'LoggedIn', 3, true) ;
	INSERT INTO pfo_role (role_id, role_name, role_class, is_public) VALUES (3, 'Forge administrators', 1, false) ;
	INSERT INTO pfo_role (role_id, role_name, role_class, is_public) VALUES (4, 'News moderators', 1, false) ;
	INSERT INTO pfo_role (role_id, role_name, role_class, is_public) VALUES (5, 'Stats administrators', 1, false) ;

	PERFORM setval ('pfo_role_seq', 5) ;

	INSERT INTO pfo_role (SELECT nextval ('pfo_role_seq'), role_name, 1, group_id, false, role_id FROM role) ;

	INSERT INTO pfo_user_role (SELECT ug.user_id, r.role_id FROM user_group ug, pfo_role r WHERE ug.role_id = r.old_role_id AND r.old_role_id != 1) ;

	PERFORM migrate_rbac_permissions_to_pfo_rbac () ;
	PERFORM migrate_role_observer_to_pfo_rbac () ;

	-- Set up members and permissions for Forge administrators
	INSERT INTO pfo_user_role (SELECT user_id, 3 FROM user_group WHERE group_id = mastergroupid AND admin_flags = 'A') ;
	PERFORM insert_pfo_role_setting (3, 'forge_admin', -1, 1) ;

	-- Set up members and permissions for News moderators
	SELECT group_id INTO newsgroupid FROM groups WHERE unix_group_name = 'newsadmin' ;
	INSERT INTO pfo_user_role (SELECT user_id, 4 FROM user_group WHERE group_id = newsgroupid AND admin_flags = 'A') ;
	PERFORM insert_pfo_role_setting (4, 'approve_news', -1, 1) ;

	-- Set up members and permissions for Stats administrators
	SELECT group_id INTO statsgroupid FROM groups WHERE unix_group_name = 'stats' ;
	INSERT INTO pfo_user_role (SELECT user_id, 5 FROM user_group WHERE group_id = statsgroupid AND admin_flags = 'A') ;
	PERFORM insert_pfo_role_setting (5, 'forge_stats', -1, 2) ;

END ;
$$ LANGUAGE plpgsql ;

SELECT pfo_rbac_full_migration () ;
