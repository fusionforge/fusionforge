DROP TABLE IF EXISTS plugin_cvstracker_data_artifact;

CREATE TABLE plugin_cvstracker_data_artifact (
	`id` integer NOT NULL auto_increment,
	`kind` integer DEFAULT '0' NOT NULL,
	`group_artifact_id` integer ,
	`project_task_id` integer ,
	PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS plugin_cvstracker_data_master;

CREATE TABLE plugin_cvstracker_data_master (
	`id` integer NOT NULL auto_increment,
	`holder_id` integer NOT NULL,
	`cvs_date` date NOT NULL,
	`log_text` text DEFAULT '',
	`file` text DEFAULT '' NOT NULL,
	`prev_version` text DEFAULT '',
	`actual_version` text DEFAULT '',
	`author` text DEFAULT '' NOT NULL,
	PRIMARY KEY (`id`)
);
