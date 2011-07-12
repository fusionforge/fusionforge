-- select user_group.user_group_id,user_group.group_id,user_group.user_id
-- ,ug.user_group_id,ug.group_id, ug.user_id
-- from user_group, user_group ug
-- where user_group.group_id=ug.group_id
-- and user_group.user_id=ug.user_id
-- and user_group.user_group_id != ug.user_group_id

select user_group.*
from user_group, user_group ug
where user_group.group_id=ug.group_id
and user_group.user_id=ug.user_id
and user_group.user_group_id != ug.user_group_id

-- select user_group_id,group_id,user_id from user_group
-- order by user_group_id,group_id,user_id
