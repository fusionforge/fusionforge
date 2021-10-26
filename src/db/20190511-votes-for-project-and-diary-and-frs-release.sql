-- Add a table to count diary votes.

CREATE TABLE diary_votes (
	diary_id	integer		NOT NULL,
	user_id		integer		NOT NULL,
	CONSTRAINT diarynotes_votes_fk_did
		FOREIGN KEY (diary_id) REFERENCES user_diary (id) ON DELETE CASCADE,
	CONSTRAINT diarynotes_votes_fk_uid
		FOREIGN KEY (user_id) REFERENCES users (user_id),
	CONSTRAINT diarynotes_votes_pk
		PRIMARY KEY (diary_id, user_id)
);

-- Add a table to count project votes.

CREATE TABLE group_votes (
	group_id	integer		NOT NULL,
	user_id		integer		NOT NULL,
	CONSTRAINT groups_votes_fk_gid
		FOREIGN KEY (group_id) REFERENCES groups (group_id) ON DELETE CASCADE,
	CONSTRAINT groups_votes_fk_uid
		FOREIGN KEY (user_id) REFERENCES users (user_id),
	CONSTRAINT groups_votes_pk
		PRIMARY KEY (group_id, user_id)
);

-- Add a table to count frs release votes.

CREATE TABLE frs_release_votes (
	release_id	integer		NOT NULL,
	user_id		integer		NOT NULL,
	CONSTRAINT frsrelease_votes_fk_fid
		FOREIGN KEY (release_id) REFERENCES frs_release (release_id) ON DELETE CASCADE,
	CONSTRAINT frsrelease_votes_fk_uid
		FOREIGN KEY (user_id) REFERENCES users (user_id),
	CONSTRAINT frsrelease_votes_pk
		PRIMARY KEY (release_id, user_id)
);
