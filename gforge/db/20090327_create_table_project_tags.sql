--
-- Create table for project's tags
--
DROP TABLE project_tags ;

CREATE TABLE project_tags
(
   group_id integer NOT NULL, 
   name text NOT NULL
) WITH OIDS;
