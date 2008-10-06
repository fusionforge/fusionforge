create table group_join_request (
group_id int not null references groups(group_id) ON DELETE CASCADE,
user_id int not null references users(user_id),
comments text,
request_date int,
PRIMARY KEY (group_id,user_id)
);


