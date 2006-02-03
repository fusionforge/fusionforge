DROP FUNCTION doc_data_search CASCADE;
DROP FUNCTION news_bytes_search CASCADE;
DROP FUNCTION artifact_search CASCADE;
DROP FUNCTION trackers_search CASCADE;

CREATE TRIGGER artifactmessage_ts_update AFTER UPDATE OR INSERT OR DELETE ON artifact_message
FOR EACH ROW EXECUTE PROCEDURE update_vectors('artifact_message');

DELETE FROM artifact_message_idx;

INSERT INTO artifact_message_idx (id, artifact_id, vectors)
SELECT id, artifact_id, to_tsvector('default', coalesce(body,'')) AS vectors
FROM artifact_message ORDER BY id;


