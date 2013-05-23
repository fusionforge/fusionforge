-- Fix count first

UPDATE artifact_counts_agg
SET count=subquery.count,
  open_count=subquery.open_count
FROM (SELECT group_artifact_id, COUNT(group_artifact_id) AS count,
        SUM(CASE WHEN status_id=1 THEN 1 ELSE 0 END) AS open_count
      FROM artifact
      GROUP BY group_artifact_id) AS subquery
WHERE artifact_counts_agg.group_artifact_id=subquery.group_artifact_id;

-- Fix insert rule (only increase open count if open)

DROP RULE artifact_insert_agg ON artifact;

CREATE OR REPLACE RULE artifact_insert_agg AS
ON INSERT TO artifact DO  UPDATE artifact_counts_agg SET count = artifact_counts_agg.count + 1, open_count =
  CASE
    WHEN new.status_id = 1 THEN artifact_counts_agg.open_count + 1
    ELSE artifact_counts_agg.open_count
  END
WHERE artifact_counts_agg.group_artifact_id = new.group_artifact_id;
