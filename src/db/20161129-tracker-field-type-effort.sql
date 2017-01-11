CREATE TABLE effort_unit_set
(
	unit_set_id serial NOT NULL,
	level integer NOT NULL DEFAULT 1,
	group_id integer DEFAULT NULL,
	group_artifact_id integer DEFAULT NULL,
	created_date integer NOT NULL,
	created_by integer NOT NULL,
	CONSTRAINT effort_unit_set_pk PRIMARY KEY (unit_set_id),
	CONSTRAINT effort_unit_set_group_id_fk FOREIGN KEY (group_id)
		REFERENCES groups (group_id) MATCH SIMPLE
		ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT effort_unit_set_group_artifact_id_fk FOREIGN KEY (group_artifact_id)
		REFERENCES artifact_group_list (group_artifact_id) MATCH SIMPLE
		ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT effort_unit_set_created_by_fk FOREIGN KEY (created_by)
		REFERENCES users (user_id) MATCH FULL
		ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE TABLE effort_unit
(
	unit_id serial NOT NULL,
	unit_set_id integer NOT NULL,
	unit_name text NOT NULL,
	conversion_factor integer NOT NULL,
	to_unit integer,
	unit_position integer NOT NULL,
	is_base_unit integer NOT NULL DEFAULT 0,
	is_deleted integer NOT NULL DEFAULT 0,
	created_date integer NOT NULL,
	created_by integer NOT NULL,
	modified_date integer NOT NULL,
	modified_by integer NOT NULL,
	CONSTRAINT effort_unit_pk PRIMARY KEY (unit_id),
	CONSTRAINT effort_unit_to_unit_fk FOREIGN KEY (to_unit)
		REFERENCES effort_unit (unit_id) MATCH SIMPLE
		ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT effort_unit_created_by_fk FOREIGN KEY (created_by)
		REFERENCES users (user_id) MATCH FULL
		ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT effort_unit_modified_by_fk FOREIGN KEY (modified_by)
		REFERENCES users (user_id) MATCH FULL
		ON UPDATE NO ACTION ON DELETE NO ACTION
);

INSERT INTO effort_unit_set(level, created_date, created_by)
VALUES(1, CAST(EXTRACT(EPOCH FROM TRANSACTION_TIMESTAMP()) AS INTEGER), 100);

INSERT INTO effort_unit(unit_set_id, unit_name, conversion_factor, to_unit, unit_position, is_base_unit, created_date, created_by, modified_date, modified_by)
VALUES(CURRVAL('effort_unit_set_unit_set_id_seq'), 'Hours', 1, CURRVAL('effort_unit_unit_id_seq'), 1, 1, CAST(EXTRACT(EPOCH FROM TRANSACTION_TIMESTAMP()) AS INTEGER), 100, CAST(EXTRACT(EPOCH FROM TRANSACTION_TIMESTAMP()) AS INTEGER), 100);

ALTER TABLE groups
	ADD COLUMN unit_set_id integer NOT NULL DEFAULT 1,
	ADD CONSTRAINT groups_unit_set_id_fk FOREIGN KEY (unit_set_id)
		REFERENCES effort_unit_set (unit_set_id) MATCH SIMPLE
		ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE artifact_group_list
	ADD COLUMN unit_set_id integer NOT NULL DEFAULT 1,
	ADD CONSTRAINT artifact_group_list_unit_set_id_fk FOREIGN KEY (unit_set_id)
		REFERENCES effort_unit_set (unit_set_id) MATCH SIMPLE
		ON UPDATE NO ACTION ON DELETE NO ACTION;

DROP VIEW artifact_group_list_vw;

CREATE OR REPLACE VIEW artifact_group_list_vw AS 
	SELECT agl.group_artifact_id, agl.group_id, agl.name, agl.description, 
			agl.email_all_updates, agl.email_address, agl.due_period, 
			agl.submit_instructions, agl.browse_instructions, agl.browse_list, 
			agl.datatype, agl.status_timeout, agl.custom_status_field, 
			agl.custom_renderer, agl.auto_assign_field, agl.unit_set_id,
			aca.count, aca.open_count
		FROM artifact_group_list agl
		LEFT JOIN artifact_counts_agg aca USING (group_artifact_id);