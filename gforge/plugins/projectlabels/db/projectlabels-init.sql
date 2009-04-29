CREATE SEQUENCE plugin_projectlabels_labelid_seq ;

CREATE TABLE plugin_projectlabels_labels (
	label_id int NOT NULL default nextval('plugin_projectlabels_labelid_seq'),
	label_name varchar(32) UNIQUE NOT NULL,
	label_text text NOT NULL,
	PRIMARY KEY(label_id)
) ;

CREATE SEQUENCE plugin_projectlabels_grlabid_seq ;

CREATE TABLE plugin_projectlabels_group_labels (
	grlab_id int NOT NULL default nextval('plugin_projectlabels_grlabid_seq'),
	group_id int UNIQUE NOT NULL,
	label_id int NOT NULL,
	PRIMARY KEY(grlab_id),
	CONSTRAINT plugin_projectlabels_grlabid_gid_fk FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL,
	CONSTRAINT plugin_projectlabels_grlabid_lid_fk FOREIGN KEY (label_id) REFERENCES plugin_projectlabels_labels(label_id) MATCH FULL
) ;
