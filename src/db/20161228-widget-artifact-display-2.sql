ALTER TABLE artifact_display_widget DROP COLUMN cols;
ALTER TABLE artifact_display_widget_field ADD COLUMN width integer;
ALTER TABLE artifact_display_widget_field ADD COLUMN section text;

