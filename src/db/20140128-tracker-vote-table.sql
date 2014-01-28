-- Add a table to count tracker votes.

CREATE TABLE artifact_votes (
	artifact_id	integer		NOT NULL,
	user_id		integer		NOT NULL,
	CONSTRAINT artifact_votes_fk_aid
		FOREIGN KEY (artifact_id) REFERENCES artifact (artifact_id),
	CONSTRAINT artifact_votes_fk_uid
		FOREIGN KEY (user_id) REFERENCES users (user_id),
	CONSTRAINT artifact_votes_pk
		PRIMARY KEY (artifact_id, user_id)
);
