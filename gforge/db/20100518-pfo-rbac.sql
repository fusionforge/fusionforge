BEGIN ;

CREATE SEQUENCE pfo_role_class_seq ;
CREATE TABLE pfo_role_class (
       class_id integer DEFAULT nextval ('pfo_role_class_seq') NOT NULL,
       class_name text DEFAULT '' NOT NULL,
       CONSTRAINT pfo_role_class_pkey PRIMARY KEY (class_id),
       CONSTRAINT pfo_role_class_name_unique UNIQUE (class_name)
) ;

INSERT INTO pfo_role_class (class_id, class_name) VALUES (1, 'PFO_RoleExplicit') ;
INSERT INTO pfo_role_class (class_id, class_name) VALUES (2, 'PFO_RoleAnonymous') ;
INSERT INTO pfo_role_class (class_id, class_name) VALUES (3, 'PFO_RoleLoggedIn') ;

SELECT setval ('pfo_role_class_seq', 3) ;

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

INSERT INTO pfo_role (role_id, role_name, role_class, is_public) VALUES (1, 'Anonymous', '2', true) ;
INSERT INTO pfo_role (role_id, role_name, role_class, is_public) VALUES (2, 'LoggedIn', '3', true) ;

SELECT setval ('pfo_role_seq', 2) ;

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

INSERT INTO pfo_role (SELECT nextval ('pfo_role_seq'), role_name, 1, group_id, false, role_id FROM role) ;

CREATE OR REPLACE FUNCTION insert_pfo_role_setting (role_id integer, section_name text, ref_id integer, perm_val integer) RETURNS void AS $$
BEGIN
	-- RAISE NOTICE 'insert_pfo_role_setting (%,%,%,%)', role_id, section_name, ref_id, perm_val ;
	INSERT INTO pfo_role_setting VALUES (role_id, section_name, ref_id, perm_val) ;
END ;
$$ LANGUAGE plpgsql ;

CREATE OR REPLACE FUNCTION migrate_rbac_permissions_to_pfo_rbac () RETURNS void AS $$
DECLARE
	os role_setting%ROWTYPE ;
	nrid integer := 0 ;
	nsec text := '' ;
	nref integer := 0 ;
	nval integer := 0 ;
	mastergroupid integer := 1 ;
	newsgroupid integer := 0 ;
	statsgroupid integer := 0 ;
	opid integer := 0 ;
	tmp integer := 0 ;
BEGIN
	SELECT group_id INTO newsgroupid FROM groups WHERE unix_group_name = 'newsadmin' ;
	SELECT group_id INTO statsgroupid FROM groups WHERE unix_group_name = 'stats' ;

	INSERT INTO pfo_role_setting (SELECT role_id, 'project_read', home_group_id, 1 FROM pfo_role WHERE home_group_id IS NOT NULL) ;

	FOR os IN SELECT * FROM role_setting ORDER BY role_id, section_name, ref_id
	LOOP
		SELECT role_id INTO nrid FROM pfo_role WHERE old_role_id = os.role_id ;
		SELECT group_id INTO opid FROM role WHERE role_id = os.role_id ;
		-- RAISE NOTICE '% > %, %/%/%', os.role_id, nrid, os.section_name, os.ref_id, os.value ;

		IF os.section_name = 'projectadmin' THEN
		   CONTINUE WHEN os.value != 'A' ;
		   nsec = 'project_admin' ;
		   nref = opid ;
		   nval = 1 ;
		   PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		   
		   nref = -1 ;
		   IF opid = mastergroupid THEN
		   	  nsec = 'forge_admin' ;
		   	  PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		   END IF ;
		   IF opid = newsgroupid THEN
		   	  nsec = 'approve_news' ;
		   	  PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		   END IF ;
		   IF opid = statsgroupid THEN
		   	  nsec = 'forge_stats' ;
		   	  PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;
		   END IF ;

		ELSIF os.section_name IN ('trackeradmin', 'pmadmin', 'forumadmin') THEN
		   CONTINUE WHEN os.value != '2' ;
		   nsec = CASE WHEN os.section_name = 'trackeradmin' THEN 'tracker_admin'
		   	       WHEN os.section_name = 'pmadmin' THEN 'pm_admin'
		   	       WHEN os.section_name = 'forumadmin' THEN 'forum_admin' END ;
		   nref = opid ;
		   nval = 1 ;
		   PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;

		ELSIF os.section_name IN ('tracker', 'newtracker') THEN
		   CONTINUE WHEN os.value = '-1' ;
		   nsec = CASE WHEN os.section_name = 'tracker' THEN os.section_name
		   	       WHEN os.section_name = 'newtracker' THEN 'new_tracker' END ;
		   nref = CASE WHEN os.section_name = 'tracker' THEN os.ref_id
		   	       WHEN os.section_name = 'newtracker' THEN opid END ;
		   nval = CASE WHEN os.value = '0' THEN 1
		   	       WHEN os.value = '1' THEN 3
		   	       WHEN os.value = '2' THEN 7
		   	       WHEN os.value = '3' THEN 5 END ;
		   PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;

		ELSIF os.section_name IN ('pm', 'newpm') THEN
		   CONTINUE WHEN os.value = '-1' ;
		   nsec = CASE WHEN os.section_name = 'pm' THEN os.section_name
		   	       WHEN os.section_name = 'newpm' THEN 'new_pm' END ;
		   nref = CASE WHEN os.section_name = 'pm' THEN os.ref_id
		   	       WHEN os.section_name = 'newpm' THEN opid END ;
		   nval = CASE WHEN os.value = '0' THEN 1
		   	       WHEN os.value = '1' THEN 3
		   	       WHEN os.value = '2' THEN 7
		   	       WHEN os.value = '3' THEN 5 END ;
		   PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;

		ELSIF os.section_name = 'forum' THEN
		   CONTINUE WHEN os.value = '-1' ;
		   nsec = os.section_name ;
		   nref = os.ref_id ;
		   SELECT moderation_level INTO tmp FROM forum_group_list WHERE group_forum_id = nref ;
		   nval = CASE WHEN os.value = '0' THEN 1
		   	       WHEN os.value = '1' AND tmp >= 2 THEN 2
		   	       WHEN os.value = '1' AND tmp <= 1 THEN 3
		   	       WHEN os.value = '2' THEN 4 END ;
		   PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;

		ELSIF os.section_name = 'newforum' THEN
		   CONTINUE WHEN os.value = '-1' ;
		   nsec = 'new_forum' ;
		   nref = opid ;
		   nval = CASE WHEN os.value = '0' THEN 1
		   	       WHEN os.value = '1' THEN 2
		   	       WHEN os.value = '2' THEN 4 END ;
		   PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;

		ELSIF os.section_name = 'docman' THEN
		   nsec = os.section_name ;
		   nref = opid ;
		   nval = CASE WHEN os.value = '0' THEN 1
		   	       WHEN os.value = '1' THEN 4 END ;
		   PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;

		ELSIF os.section_name = 'frs' THEN
		   nsec = os.section_name ;
		   nref = opid ;
		   nval = CASE WHEN os.value = '0' THEN 1
		   	       WHEN os.value = '1' THEN 3 END ;
		   PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;

		ELSIF os.section_name = 'scm' THEN
		   CONTINUE WHEN os.value = '-1' ;
		   nsec = os.section_name ;
		   nref = opid ;
		   nval = CASE WHEN os.value = '0' THEN 1
		   	       WHEN os.value = '1' THEN 2 END ;
		   PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;

		ELSIF os.section_name = 'webcal' THEN
		   CONTINUE WHEN os.value = '0' ;
		   nsec = os.section_name ;
		   nref = opid ;
		   nval = os.value ;
		   PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;

		ELSIF os.section_name = 'plugin_mediawiki_edit' THEN
		   CONTINUE WHEN os.value = '0' ;
		   nsec = os.section_name ;
		   nref = opid ;
		   nval = os.value ;
		   PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;

		ELSE
		   RAISE EXCEPTION 'Unknown setting % for role %', os.section_name, os.role_id ;
		   CONTINUE WHEN os.value = '0' ;
		   nsec = os.section_name ;
		   nref = os.ref_id ;
		   nval = os.value::integer ;
		   PERFORM insert_pfo_role_setting (nrid, nsec, nref, nval) ;

		END IF ;

	END LOOP ;

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
		PERFORM insert_pfo_role_setting (1, 'project_read', g.group_id, 1) ;

		IF g.enable_anonscm = 1 THEN
		   PERFORM insert_pfo_role_setting (1, 'scm', g.group_id, 1) ;
		END IF ;

		FOR t IN SELECT * FROM artifact_group_list WHERE group_id = g.group_id AND is_public = 1
		LOOP
			IF t.allow_anon = 1 THEN
			   PERFORM insert_pfo_role_setting (1, 'tracker', t.group_artifact_id, 1) ;
			ELSE
			   need_loggedin = true ;
			   PERFORM insert_pfo_role_setting (2, 'tracker', t.group_artifact_id, 1) ;
			END IF ;
		END LOOP ;
		
		FOR p IN SELECT * FROM project_group_list WHERE group_id = g.group_id AND is_public = 1
		LOOP
			PERFORM insert_pfo_role_setting (1, 'pm', p.group_project_id, 1) ;
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
			   need_loggedin = true ;
			   IF f.moderation_level = 0 THEN
			      PERFORM insert_pfo_role_setting (2, 'forum', f.group_forum_id, 3) ;
			   ELSE
			      PERFORM insert_pfo_role_setting (2, 'forum', f.group_forum_id, 2) ;
			   END IF ;
			END IF ;
		END LOOP ;
		
		IF need_loggedin THEN
		   INSERT INTO role_project_refs VALUES (2, g.group_id) ;
		END IF ;

	END LOOP ;

END ;
$$ LANGUAGE plpgsql ;


SELECT migrate_rbac_permissions_to_pfo_rbac () ;
-- SELECT g.unix_group_name, r.role_name, s.section_name, s.ref_id, s.perm_val FROM groups g, pfo_role r, pfo_role_setting s WHERE g.group_id = r.home_group_id AND r.role_id = s.role_id ORDER BY s.role_id, s.ref_id ;

SELECT migrate_role_observer_to_pfo_rbac () ;
-- SELECT r.role_name, s.section_name, s.ref_id, s.perm_val FROM pfo_role r, pfo_role_setting s WHERE r.home_group_id IS NULL AND r.role_id = s.role_id ORDER BY s.role_id, s.ref_id ;
-- SELECT count(*) FROM pfo_role_setting ;

ROLLBACK ;
