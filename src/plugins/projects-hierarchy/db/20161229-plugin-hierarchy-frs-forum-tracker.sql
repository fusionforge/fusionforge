ALTER TABLE plugin_projects_hierarchy ADD COLUMN frs boolean DEFAULT false;
ALTER TABLE plugin_projects_hierarchy ADD COLUMN forum boolean DEFAULT false;
ALTER TABLE plugin_projects_hierarchy ADD COLUMN tracker boolean DEFAULT false;
ALTER TABLE plugin_projects_hierarchy_global ADD COLUMN frs boolean DEFAULT false;
ALTER TABLE plugin_projects_hierarchy_global ADD COLUMN forum boolean DEFAULT false;
ALTER TABLE plugin_projects_hierarchy_global ADD COLUMN tracker boolean DEFAULT false;
