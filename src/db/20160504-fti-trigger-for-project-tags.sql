DROP TRIGGER IF EXISTS project_tags_ts_update ON project_tags;
CREATE TRIGGER project_tags_ts_update AFTER INSERT OR DELETE OR UPDATE ON project_tags FOR EACH ROW EXECUTE PROCEDURE update_vectors('groups');

-- Rebuild all indices
SELECT rebuild_fti_indices();
