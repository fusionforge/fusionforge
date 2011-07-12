DROP VIEW patch;
CREATE VIEW patch AS
	SELECT a.artifact_id AS patch_id,
	g.group_id,
	a.status_id,
	a.priority,
	a.category_id,
	a.submitted_by,
	a.assigned_to,
	a.open_date,
	a.summary,a.details,
	a.close_date,
	a.group_artifact_id AS patch_group_id,
	a.resolution_id AS resolution
	FROM artifact a, groups g, artifact_group_list agl
	WHERE a.group_artifact_id=agl.group_artifact_id
	AND agl.group_id=g.group_id
	AND agl.name='Patches';
