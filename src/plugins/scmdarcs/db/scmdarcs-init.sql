CREATE TABLE plugin_scmdarcs_create_repos (
	group_id int NOT NULL,
	repo_name character varying(128) NOT NULL,
	clone_repo_name character varying(128),
	FOREIGN KEY (group_id) REFERENCES groups (group_id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT plugin_scmdarcs_personal_repos_unique UNIQUE (group_id, repo_name)
) ;

CREATE INDEX plugin_scmdarcs_create_repos_gid_idx ON plugin_scmdarcs_create_repos (group_id) ;
