ALTER TABLE artifact_display_widget DROP COLUMNS cols;
ALTER TABLE artifact_display_widget_field ADD COLUMN width integer;
ALTER TABLE artifact_display_widget_field ADD COLUMN section text;

