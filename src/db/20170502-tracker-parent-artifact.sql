ALTER TABLE artifact_extra_field_list
   ADD COLUMN aggregation_rule integer NOT NULL DEFAULT 0,
   ADD COLUMN distribution_rule integer NOT NULL DEFAULT 0;