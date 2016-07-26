SELECT setval('artifact_extra_field_list_extra_field_id_seq', (SELECT MAX(extra_field_id) from artifact_extra_field_list));

