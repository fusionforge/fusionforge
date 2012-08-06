---
--- This file does away with the bits of db_stats_agg.php that have to
--- do with file download stats.
---

---
--- No longer needed tables
---
DROP TABLE frs_dlstats_file_agg;
DROP TABLE frs_dlstats_grouptotal_agg;
DROP TABLE frs_dlstats_group_agg;

---
--- Get up-to-date info
---
DELETE FROM frs_dlstats_filetotal_agg;
INSERT INTO frs_dlstats_filetotal_agg
	SELECT file_id, 0 as downloads
	FROM frs_file;
UPDATE frs_dlstats_filetotal_agg
	SET downloads = (SELECT count(*)
	FROM frs_dlstats_file WHERE frs_dlstats_file.file_id = frs_dlstats_filetotal_agg.file_id);

---
--- Whenever a new file is inserted into the frs_file table, add an entry
--- to the _agg tables too.
---

CREATE FUNCTION "frs_dlstats_filetotal_insert_agg" () RETURNS OPAQUE AS '
BEGIN
	INSERT INTO frs_dlstats_filetotal_agg (file_id, downloads) VALUES (NEW.file_id, 0);
	RETURN NEW;
END;
' LANGUAGE 'plpgsql';

CREATE TRIGGER "frs_file_insert_trig" AFTER INSERT ON "frs_file" FOR EACH ROW EXECUTE PROCEDURE frs_dlstats_filetotal_insert_agg();

---
--- Whenever a file gets downloaded, increment stats
---
CREATE RULE frs_dlstats_file_rule AS ON INSERT TO frs_dlstats_file DO
	UPDATE frs_dlstats_filetotal_agg
	SET downloads = (frs_dlstats_filetotal_agg.downloads + 1)
	WHERE (frs_dlstats_filetotal_agg.file_id = new.file_id);

---
--- Create a view to get file downloads by month
---
CREATE VIEW frs_dlstats_file_agg_vw AS
	SELECT month, day, file_id, count(*) AS downloads
	FROM frs_dlstats_file
	GROUP BY month, day, file_id;

---
--- Create a view to get group total downloads
---
CREATE VIEW frs_dlstats_grouptotal_vw AS
	SELECT frs_package.group_id, sum(frs_dlstats_filetotal_agg.downloads) AS downloads
	FROM frs_package,frs_release,frs_file,frs_dlstats_filetotal_agg
	WHERE frs_package.package_id=frs_release.package_id
		AND frs_release.release_id=frs_file.release_id
		AND frs_file.file_id=frs_dlstats_filetotal_agg.file_id
	GROUP BY frs_package.group_id;

---
--- Create a view to get group aggregate stats by month
---
CREATE VIEW frs_dlstats_group_vw AS
	SELECT frs_package.group_id, fdfa.month, fdfa.day, sum(fdfa.downloads) AS downloads
	FROM frs_package, frs_release, frs_file, frs_dlstats_file_agg_vw fdfa
	WHERE frs_package.package_id=frs_release.package_id
		AND frs_release.release_id=frs_file.release_id
		AND frs_file.file_id=fdfa.file_id
	GROUP BY frs_package.group_id, fdfa.month, fdfa.day;

---
--- Add Latin as a supported language
---
INSERT INTO supported_languages (name, filename, classname, language_code) values ('Latin', 'Latin.class', 'Latin', 'la');
