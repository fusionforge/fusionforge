CREATE SEQUENCE "plugin_cvstracker_artifact_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

CREATE TABLE plugin_cvstracker_data_artifact (
	"id" integer DEFAULT nextval ('plugin_cvstracker_artifact_seq'::text) NOT NULL,
	"kind" integer DEFAULT '0' NOT NULL,
	"group_artifact_id" integer ,
	"project_task_id" integer ,
	Constraint "plugin_cvstracker_artifact_pkey" Primary Key ("id")
);



CREATE SEQUENCE "plugin_cvstracker_master_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

CREATE TABLE plugin_cvstracker_data_master (
	"id" integer DEFAULT nextval ('plugin_cvstracker_master_seq'::text) NOT NULL,
	"holder_id" integer NOT NULL,
	"cvs_date" date NOT NULL,
	"log_text" text DEFAULT '',
	"file" text DEFAULT '' NOT NULL,
	"prev_version" text DEFAULT '',
	"actual_version" text DEFAULT '',
	"author" text DEFAULT '' NOT NULL,
	Constraint "plugin_cvstracker_master_pkey" Primary Key ("id"),
	FOREIGN KEY (holder_id) REFERENCES plugin_cvstracker_data_artifact ("id"),
	FOREIGN KEY (author) REFERENCES users (user_name)
);

CREATE INDEX plugin_cvstracker_group_artifact_id ON plugin_cvstracker_data_artifact USING btree (group_artifact_id);
