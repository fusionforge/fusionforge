--
-- Add element_pos column in artifact_extra_field_elements
-- for AC_FD0424
--
-- Roger Guignard May-2008
--
ALTER TABLE "artifact_extra_field_elements" ADD COLUMN "element_pos" integer;
