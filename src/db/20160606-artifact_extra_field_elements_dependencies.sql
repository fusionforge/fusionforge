CREATE TABLE artifact_extra_field_elements_dependencies
(
  parent_element_id integer NOT NULL,
  child_element_id integer NOT NULL,
  CONSTRAINT artifact_extra_field_elements_dependencies_pkey PRIMARY KEY (parent_element_id, child_element_id),
  CONSTRAINT artifact_extra_field_elements_parent_element_id FOREIGN KEY (parent_element_id)
      REFERENCES artifact_extra_field_elements (element_id) MATCH FULL
      ON DELETE CASCADE,
  CONSTRAINT artifact_extra_field_elements_child_element_id FOREIGN KEY (child_element_id)
      REFERENCES artifact_extra_field_elements (element_id) MATCH FULL
      ON DELETE CASCADE
);