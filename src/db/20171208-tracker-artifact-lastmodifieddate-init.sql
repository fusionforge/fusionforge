CREATE OR REPLACE FUNCTION SetArtifactLastModifiedDate() RETURNS int4 AS '
DECLARE r RECORD;

BEGIN
    FOR r IN select artifact_id,open_date from artifact where last_modified_date IS NULL LOOP
       UPDATE artifact set last_modified_date = open_date where artifact_id = r.artifact_id;
    END LOOP;
	return 1;
END;
' LANGUAGE plpgsql;

SELECT SetArtifactLastModifiedDate() as output; 
