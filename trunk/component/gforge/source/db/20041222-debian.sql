DELETE FROM project_assigned_to WHERE project_task_id in (SELECT project_task_id FROM project_task WHERE status_id='3');
DELETE FROM project_dependencies WHERE project_task_id in (SELECT project_task_id FROM project_task WHERE status_id='3');
DELETE FROM project_history WHERE project_task_id in (SELECT project_task_id FROM project_task WHERE status_id='3');
DELETE FROM project_messages WHERE project_task_id in (SELECT project_task_id FROM project_task WHERE status_id='3');
DELETE FROM project_task_artifact WHERE project_task_id in (SELECT project_task_id FROM project_task WHERE status_id='3');
-- DELETE FROM rep_time_tracking WHERE project_task_id in (SELECT project_task_id FROM project_task WHERE status_id='3');
DELETE FROM project_task WHERE project_task_id in (SELECT project_task_id FROM project_task WHERE status_id='3');

DELETE FROM artifact_extra_field_data WHERE artifact_id in (SELECT artifact_id FROM artifact WHERE status_id='3');
DELETE FROM artifact_file WHERE artifact_id in (SELECT artifact_id FROM artifact WHERE status_id='3');
DELETE FROM artifact_message WHERE artifact_id in (SELECT artifact_id FROM artifact WHERE status_id='3');
DELETE FROM artifact_history WHERE artifact_id in (SELECT artifact_id FROM artifact WHERE status_id='3');
DELETE FROM artifact_monitor WHERE artifact_id in (SELECT artifact_id FROM artifact WHERE status_id='3');
DELETE FROM artifact WHERE artifact_id in (SELECT artifact_id FROM artifact WHERE status_id='3');

DELETE FROM project_status WHERE status_id='3';
DELETE FROM artifact_status WHERE id='3';
