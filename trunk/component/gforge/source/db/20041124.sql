--DROP TABLE project_task_external_order;

CREATE TABLE project_task_external_order (
project_task_id int not null references project_task(project_task_id) MATCH FULL ON DELETE CASCADE,
external_id int not null
);

CREATE INDEX projecttaskexternal_projtaskid ON project_task_external_order(project_task_id,external_id);
