create table artifact_type_monitor (
group_artifact_id int not null REFERENCES artifact_group_list(group_artifact_id) ON DELETE CASCADE,
user_id int not null REFERENCES users(user_id),
PRIMARY KEY (group_artifact_id,user_id)
);
