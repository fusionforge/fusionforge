CREATE TABLE plugin_scmgit_personal_repos (
	group_id int NOT NULL,
	user_id int NOT NULL,
	FOREIGN KEY (group_id) REFERENCES groups (group_id) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT plugin_scmgit_personal_repos_unique UNIQUE (group_id, user_id)
) ;

CREATE INDEX plugin_scmgit_personal_repos_gid_idx ON plugin_scmgit_personal_repos (group_id) ;
