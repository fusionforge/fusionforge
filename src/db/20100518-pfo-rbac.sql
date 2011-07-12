CREATE SEQUENCE pfo_role_class_seq ;
CREATE TABLE pfo_role_class (
       class_id integer DEFAULT nextval ('pfo_role_class_seq') NOT NULL,
       class_name text DEFAULT '' NOT NULL,
       CONSTRAINT pfo_role_class_pkey PRIMARY KEY (class_id),
       CONSTRAINT pfo_role_class_name_unique UNIQUE (class_name)
) ;

CREATE SEQUENCE pfo_role_seq ;
CREATE TABLE pfo_role (
       role_id integer DEFAULT nextval ('pfo_role_seq') NOT NULL,
       role_name text DEFAULT '' NOT NULL,
       role_class integer DEFAULT 1 NOT NULL REFERENCES pfo_role_class (class_id),
       home_group_id integer,
       is_public boolean DEFAULT false NOT NULL,
       old_role_id integer DEFAULT 0 NOT NULL,
       CONSTRAINT pfo_role_pkey PRIMARY KEY (role_id),
       CONSTRAINT pfo_role_name_unique UNIQUE (role_id, role_name)
) ;

CREATE TABLE role_project_refs (
       role_id integer DEFAULT 0 NOT NULL REFERENCES pfo_role,
       group_id integer DEFAULT 0 NOT NULL REFERENCES groups,
       CONSTRAINT role_project_refs_unique UNIQUE (role_id, group_id)
) ;

CREATE TABLE pfo_role_setting (
       role_id integer DEFAULT 0 NOT NULL REFERENCES pfo_role,
       section_name text DEFAULT '' NOT NULL,
       ref_id integer DEFAULT 0 NOT NULL,
       perm_val integer DEFAULT 0 NOT NULL,
       CONSTRAINT pfo_role_setting_unique UNIQUE (role_id, section_name, ref_id)
) ;

CREATE TABLE pfo_user_role (
       user_id integer DEFAULT 0 NOT NULL REFERENCES users,
       role_id integer DEFAULT 0 NOT NULL REFERENCES pfo_role,
       CONSTRAINT pfo_user_role_unique UNIQUE (user_id, role_id)
) ;

CREATE FUNCTION insert_pfo_role_setting (role_id integer, section_name text, ref_id integer, perm_val integer) RETURNS void AS $$
BEGIN
	IF perm_val != 0 THEN
	   INSERT INTO pfo_role_setting VALUES (role_id, section_name, ref_id, perm_val) ;
	END IF ;
END ;
$$ LANGUAGE plpgsql ;

CREATE FUNCTION migrate_rbac_permissions_to_pfo_rbac () RETURNS void AS $$
DECLARE
	r role%ROWTYPE ;
	nrid integer := 0 ;
	nsec text := '' ;
	nref integer := 0 ;
	nval integer := 0 ;
	opid integer := 0 ;
	agl artifact_group_list%ROWTYPE ;
	pgl project_group_list%ROWTYPE ;
	fgl forum_group_list%ROWTYPE ;
BEGIN
	FOR r IN SELECT * FROM role
	LOOP
		SELECT role_id INTO nrid FROM pfo_role WHERE old_role_id = r.role_id ;
		SELECT group_id INTO opid FROM role WHERE role_id = r.role_id ;

		PERFORM insert_pfo_role_setting (nrid, 'project_read', opid, 1) ;

		nsec = 'project_admin' ;
		nref = opid ;
		nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
		PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;

		nsec = 'forge_admin' ;
		nref = -1 ;
		nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
		PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		nsec = 'approve_news' ;
		nref = -1 ;
		nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
		PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		nsec = 'forge_stats' ;
		nref = opid ;
		nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
		PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;

		nsec = 'tracker_admin' ;
		nref = opid ;
		nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
		PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		nsec = 'new_tracker' ;
		nref = opid ;
		nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
		PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		nsec = 'tracker' ;
		FOR agl IN SELECT * FROM artifact_group_list WHERE group_id = opid
		LOOP
			nref = agl.group_artifact_id ;
			nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
			PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		END LOOP ;

		nsec = 'pm_admin' ;
		nref = opid ;
		nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
		PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		nsec = 'new_pm' ;
		nref = opid ;
		nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
		PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		nsec = 'pm' ;
		FOR pgl IN SELECT * FROM project_group_list WHERE group_id = opid
		LOOP
			nref = pgl.group_project_id ;
			nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
			PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		END LOOP ;

		nsec = 'forum_admin' ;
		nref = opid ;
		nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
		PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		nsec = 'new_forum' ;
		nref = opid ;
		nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
		PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		nsec = 'forum' ;
		FOR fgl IN SELECT * FROM forum_group_list WHERE group_id = opid
		LOOP
			nref = fgl.group_forum_id ;
			nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
			PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		END LOOP ;

		nsec = 'docman' ;
		nref = opid ;
		nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
		PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		nsec = 'scm' ;
		nref = opid ;
		nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
		PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		nsec = 'frs' ;
		nref = opid ;
		nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
		PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		nsec = 'webcal' ;
		nref = opid ;
		nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
		PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		nsec = 'plugin_mediawiki_edit' ;
		nref = opid ;
		nval = pfo_rbac_permissions_from_old (r.role_id, nsec, nref) ;
		PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;

	END LOOP ;

END ;
$$ LANGUAGE plpgsql ;

CREATE FUNCTION migrate_role_observer_to_pfo_rbac () RETURNS void AS $$
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

			PERFORM insert_pfo_role_setting (1, 'tracker', t.group_artifact_id, 1) ;
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

CREATE FUNCTION pfo_rbac_permissions_from_old (rid integer, nsec text, nref integer) RETURNS integer AS $$
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

		   IF nsec = 'forge_admin' AND nref = -1 AND opid = mastergroupid THEN
		      RETURN 1 ;
		   END IF ;
		   IF nsec = 'approve_news' AND nref = -1 AND opid = newsgroupid THEN
		      RETURN 1 ;
		   END IF ;
		   IF nsec = 'forge_stats' AND nref = -1 AND opid = statsgroupid THEN
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

CREATE FUNCTION pfo_rbac_full_migration () RETURNS void AS $$
DECLARE
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

	INSERT INTO pfo_role (role_id, role_name, role_class, is_public) VALUES (1, 'Anonymous', '2', true) ;
	INSERT INTO pfo_role (role_id, role_name, role_class, is_public) VALUES (2, 'LoggedIn', '3', true) ;

	PERFORM setval ('pfo_role_seq', 2) ;

	INSERT INTO pfo_role (SELECT nextval ('pfo_role_seq'), role_name, 1, group_id, false, role_id FROM role) ;

	INSERT INTO pfo_user_role (SELECT ug.user_id, r.role_id FROM user_group ug, pfo_role r WHERE ug.role_id = r.old_role_id) ;

	PERFORM migrate_rbac_permissions_to_pfo_rbac () ;
	PERFORM migrate_role_observer_to_pfo_rbac () ;
END ;
$$ LANGUAGE plpgsql ;

SELECT pfo_rbac_full_migration () ;
