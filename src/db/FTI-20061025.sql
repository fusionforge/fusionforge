SET client_min_messages TO warning;

DROP FUNCTION export_groups_search(text) CASCADE;
DROP FUNCTION forum_search(text, integer) CASCADE;
DROP FUNCTION frs_search(text, integer, text, boolean) CASCADE;
DROP FUNCTION users_search(text) CASCADE;
DROP FUNCTION groups_search(text) CASCADE;
DROP FUNCTION skills_data_search(text) CASCADE;
DROP FUNCTION project_task_search(text, integer, text, boolean) CASCADE;
