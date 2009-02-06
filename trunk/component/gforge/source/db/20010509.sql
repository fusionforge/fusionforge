-- by: pfalcon
-- purpose: Referential integrity constraints for FRS schema

BEGIN;

INSERT INTO frs_filetype VALUES (100,'None');
INSERT INTO frs_processor VALUES (100,'None');

-- if file is not attched to a release, it can't be seen anyway
DELETE FROM frs_file
WHERE NOT EXISTS(
	SELECT release_id
	FROM frs_release
	WHERE frs_file.release_id=frs_release.release_id
)
;

UPDATE frs_file
SET type_id=100
WHERE NOT EXISTS(
	SELECT type_id
	FROM frs_filetype
	WHERE frs_file.type_id=frs_filetype.type_id
)
;

UPDATE frs_file
SET processor_id=100
WHERE NOT EXISTS(
	SELECT processor_id
	FROM frs_processor
	WHERE frs_file.processor_id=frs_processor.processor_id
)
;

COMMIT;

ALTER TABLE frs_file ADD CONSTRAINT frsfile_releaseid_fk
	FOREIGN KEY (release_id) REFERENCES frs_release(release_id) MATCH FULL;
ALTER TABLE frs_file ADD CONSTRAINT frsfile_typeid_fk
	FOREIGN KEY (type_id) REFERENCES frs_filetype(type_id) MATCH FULL;
ALTER TABLE frs_file ADD CONSTRAINT frsfile_processorid_fk
	FOREIGN KEY (processor_id) REFERENCES frs_processor(processor_id) MATCH FULL;

ALTER TABLE frs_package ADD CONSTRAINT frspackage_groupid_fk
	FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL;
ALTER TABLE frs_package ADD CONSTRAINT frspackage_statusid_fk
	FOREIGN KEY (status_id) REFERENCES frs_status(status_id) MATCH FULL;

ALTER TABLE frs_release ADD CONSTRAINT frsrelease_packageid_fk
	FOREIGN KEY (package_id) REFERENCES frs_package(package_id) MATCH FULL;
ALTER TABLE frs_release ADD CONSTRAINT frsrelease_statusid_fk
	FOREIGN KEY (status_id) REFERENCES frs_status(status_id) MATCH FULL;
ALTER TABLE frs_release ADD CONSTRAINT frsrelease_releasedby_fk
	FOREIGN KEY (released_by) REFERENCES users(user_id) MATCH FULL;

-- Tracker 'Pending' patch sql changes
-- by: zelphyr
ALTER TABLE artifact_group_list ADD COLUMN status_timeout integer;
UPDATE artifact_group_list SET status_timeout='1209600' WHERE status_timeout=NULL;
INSERT INTO artifact_status VALUES('4','Pending');
