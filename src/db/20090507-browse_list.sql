--
-- Add the browse_list field in the artifact_group_list
--
-- This is to support the customization of columns in the
-- browse list.
--
-- Alain Peyrat Sep-2006

DROP VIEW "artifact_group_list_vw";

ALTER TABLE "artifact_group_list" ADD COLUMN "browse_list" text;
UPDATE "artifact_group_list"
  SET browse_list='summary,open_date,assigned_to,submitted_by';
ALTER TABLE "artifact_group_list" ALTER COLUMN browse_list SET NOT NULL;
ALTER TABLE "artifact_group_list" ALTER COLUMN browse_list
  SET DEFAULT 'summary,open_date,assigned_to,submitted_by';

CREATE VIEW "artifact_group_list_vw" AS
  SELECT agl.group_artifact_id, agl.group_id, agl.name, agl.description,
    agl.is_public, agl.allow_anon, agl.email_all_updates, agl.email_address,
    agl.due_period, agl.submit_instructions, agl.browse_instructions,
    agl.browse_list, agl.datatype, agl.status_timeout, agl.custom_status_field,
    agl.custom_renderer, aca.count, aca.open_count
  FROM artifact_group_list agl
  LEFT JOIN artifact_counts_agg aca USING (group_artifact_id);
