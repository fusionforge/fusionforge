CREATE RULE artifact_delete_agg AS
	ON DELETE TO artifact DO
	UPDATE artifact_counts_agg SET
		count = (artifact_counts_agg.count - 1),
		open_count = (CASE WHEN old.status_id=1 THEN artifact_counts_agg.open_count - 1 ELSE artifact_counts_agg.open_count END)
		WHERE (artifact_counts_agg.group_artifact_id = old.group_artifact_id);