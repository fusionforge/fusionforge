DELETE from artifact_extra_field_data where extra_field_id NOT IN
(select aefl.extra_field_id FROM artifact_extra_field_list aefl, artifact a
WHERE a.group_artifact_id=aefl.group_artifact_id and a.artifact_id=artifact_extra_field_data.artifact_id);

