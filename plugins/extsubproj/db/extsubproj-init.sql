-- Links a project to remote subprojects identified by URLs
CREATE TABLE plugin_extsubproj_subprojects (
	project_id integer DEFAULT 0 NOT NULL,
	sub_project_url text NOT NULL
);
