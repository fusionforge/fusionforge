CREATE TABLE scm_secondary_repos (
	group_id int NOT NULL REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE,
	plugin_id int NOT NULL REFERENCES plugins ON DELETE CASCADE ON UPDATE CASCADE,
	repo_name text NOT NULL,
	clone_url text NOT NULL,
	description text NOT NULL,
	next_action int DEFAULT 0 NOT NULL,
	CONSTRAINT scm_secondary_repos_unique UNIQUE (group_id, plugin_id, repo_name)
) ;
CREATE INDEX scm_secondary_repos_gid_idx ON scm_secondary_repos (group_id) ;

CREATE TABLE scm_personal_repos (
	group_id int NOT NULL REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE,
	plugin_id int NOT NULL REFERENCES plugins ON DELETE CASCADE ON UPDATE CASCADE,
	user_id int NOT NULL REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE,
	next_action int DEFAULT 0 NOT NULL,
	CONSTRAINT scm_personal_repos_unique UNIQUE (group_id, plugin_id, user_id)
) ;
CREATE INDEX scm_personal_repos_uid_idx ON scm_personal_repos (group_id) ;
