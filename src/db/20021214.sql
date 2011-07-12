CREATE VIEW frs_file_vw AS
SELECT frs_file.*,
frs_filetype.name AS filetype,
frs_processor.name AS processor,
frs_dlstats_filetotal_agg.downloads AS downloads
FROM frs_filetype,frs_processor,
frs_file LEFT JOIN frs_dlstats_filetotal_agg ON frs_dlstats_filetotal_agg.file_id=frs_file.file_id
WHERE
frs_filetype.type_id=frs_file.type_id
AND frs_processor.processor_id=frs_file.processor_id;
