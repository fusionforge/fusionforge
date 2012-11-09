CREATE TABLE plugin_scmgit_secondary_repos (
	group_id int NOT NULL,
	repo_name text NOT NULL,
	clone_url text NOT NULL,
	description text NOT NULL,
	next_action int DEFAULT 0 NOT NULL,
	FOREIGN KEY (group_id) REFERENCES groups (group_id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT plugin_scmgit_secondary_repos_unique UNIQUE (group_id, repo_name)
) ;

CREATE INDEX plugin_scmgit_secondary_repos_gid_idx ON plugin_scmgit_secondary_repos (group_id) ;
