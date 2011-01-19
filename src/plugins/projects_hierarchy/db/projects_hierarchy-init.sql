CREATE TABLE plugin_projects_hierarchy (
	project_id integer DEFAULT 0 NOT NULL,
	sub_project_id integer DEFAULT 0 NOT NULL,
	link_type char(4) DEFAULT 'navi' NOT NULL,
	activated boolean DEFAULT false NOT NULL,
	com char(255) NOT NULL,
	docman boolean DEFAULT false NOT NULL
);
