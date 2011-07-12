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

   	IF nsec = 'forge_admin' AND nref = -1 AND rid = 1 THEN
	   SELECT count(*) INTO tmp FROM role_setting WHERE role_id = rid ;
	   IF tmp = 0 THEN
	      RETURN 1 ;
	   END IF ;
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
		      RETURN 2 ;
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

		ELSIF os.section_name = 'plugin_mediawiki_read' THEN
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

		ELSIF os.section_name = 'plugin_mediawiki_upload' THEN
		   CONTINUE WHEN os.value = '0' ;
		   onsec = os.section_name ;
		   onref = opid ;
		   onval = os.value ;
		   IF nsec = onsec AND nref = onref THEN
		      RETURN onval ;
		   END IF ;

		ELSIF os.section_name = 'plugin_mediawiki_admin' THEN
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

SELECT pfo_rbac_full_migration () ;
