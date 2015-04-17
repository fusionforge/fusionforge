-- migrate RBAC FRS settings
CREATE OR REPLACE FUNCTION FRSmigrateRBAC() RETURNS int4 AS '
DECLARE r RECORD;
DECLARE s RECORD;
DECLARE t RECORD;
DECLARE u RECORD;

BEGIN
	create table temptable_frsrole (
		roleid integer NOT NULL,
		refid integer NOT NULL,
		permval integer NOT NULL
	);
	insert into temptable_frsrole (roleid, refid, permval) select pfo_role_setting.role_id, pfo_role_setting.ref_id, pfo_role_setting.perm_val from pfo_role_setting where pfo_role_setting.section_name = ''frs'';
	delete from pfo_role_setting where section_name = ''frs'';
	FOR r IN select * from temptable_frsrole LOOP
		CASE r.permval
			WHEN 0 THEN
				insert into pfo_role_setting (role_id, section_name, ref_id, perm_val) values (r.roleid, ''new_frs'', r.refid, 0);
				insert into pfo_role_setting (role_id, section_name, ref_id, perm_val) values (r.roleid, ''frs_admin'', r.refid, 0);
				FOR s IN select frs_package.package_id as packid from frs_package where frs_package.group_id = r.refid LOOP
					insert into pfo_role_setting (role_id, section_name, ref_id, perm_val) values (r.roleid, ''frs'', s.packid, 0);
				END LOOP;
			WHEN 1, 2 THEN
				insert into pfo_role_setting (role_id, section_name, ref_id, perm_val) values (r.roleid, ''new_frs'', r.refid, 1);
				insert into pfo_role_setting (role_id, section_name, ref_id, perm_val) values (r.roleid, ''frs_admin'', r.refid, 1);
				FOR t IN select frs_package.package_id as packid from frs_package where frs_package.group_id = r.refid LOOP
					insert into pfo_role_setting (role_id, section_name, ref_id, perm_val) values (r.roleid, ''frs'', t.packid, 1);
				END LOOP;
			WHEN 3 THEN
				insert into pfo_role_setting (role_id, section_name, ref_id, perm_val) values (r.roleid, ''new_frs'', r.refid, 2);
				insert into pfo_role_setting (role_id, section_name, ref_id, perm_val) values (r.roleid, ''frs_admin'', r.refid, 2);
				FOR u IN select frs_package.package_id as packid from frs_package where frs_package.group_id = r.refid LOOP
					insert into pfo_role_setting (role_id, section_name, ref_id, perm_val) values (r.roleid, ''frs'', u.packid, 4);
				END LOOP;
		END CASE;
	END LOOP;
	drop table temptable_frsrole;
	return 1;
END;
' LANGUAGE plpgsql;

SELECT FRSmigrateRBAC() as output;
