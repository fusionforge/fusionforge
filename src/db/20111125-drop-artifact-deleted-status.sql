UPDATE artifact SET status_id = 2 WHERE status_id = 3;
DELETE FROM artifact_status WHERE id=3 AND status_name='Deleted';
