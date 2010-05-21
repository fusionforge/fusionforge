ALTER TABLE role ADD COLUMN is_public boolean ;
ALTER TABLE role ALTER COLUMN group_id DROP NOT NULL ;

CREATE TABLE role_project_refs (
       role_id integer DEFAULT 0 NOT NULL REFERENCES role,
       group_id integer DEFAULT 0 NOT NULL REFERENCES groups
) ;
