ALTER TABLE artifact_votes DROP CONSTRAINT artifact_votes_fk_aid;
ALTER TABLE artifact_votes ADD CONSTRAINT artifact_votes_fk_aid FOREIGN KEY (artifact_id) REFERENCES artifact(artifact_id) ON DELETE CASCADE;
