ALTER TABLE artifact_extra_field_list
   ADD is_disabled integer NOT NULL DEFAULT 0,
   ADD is_hidden_on_submit integer NOT NULL DEFAULT 0;