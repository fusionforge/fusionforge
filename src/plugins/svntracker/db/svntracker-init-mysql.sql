CREATE TABLE plugin_svntracker_data_artifact (
	`id` integer NOT NULL auto_increment,
	`kind` integer DEFAULT '0' NOT NULL,
	`group_artifact_id` integer ,
	`project_task_id` integer ,
	PRIMARY KEY (`id`),
	UNIQUE KEY `plugin_svntracker_group_artifact_id` (`group_artifact_id`)
);

CREATE TABLE plugin_svntracker_data_master (
	`id` integer NOT NULL auto_increment,
	`holder_id` integer NOT NULL,
	`svn_date` date NOT NULL,
	`log_text` text DEFAULT '',
	`file` text DEFAULT '' NOT NULL,
	`prev_version` text DEFAULT '',
	`actual_version` text DEFAULT '',
	`author` text DEFAULT '' NOT NULL,
	PRIMARY KEY (`id`)
);
