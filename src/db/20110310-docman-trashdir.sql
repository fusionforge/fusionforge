CREATE OR REPLACE FUNCTION DocmanTrashDir() RETURNS int4 AS '
DECLARE r RECORD;

BEGIN
    FOR r IN select groups.group_id as gid from groups where groups.use_docman = 1 and groups.group_id not in(select doc_groups.group_id from doc_groups where doc_groups.groupname = ''.trash'' and doc_groups.stateid = 2) LOOP
       INSERT into doc_groups (groupname, stateid, group_id) values (''.trash'',2,r.gid); 
    END LOOP;
	return 1;
END;
' LANGUAGE plpgsql;

SELECT DocmanTrashDir() as output;

