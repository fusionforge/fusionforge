create table project_sums_agg (
group_id int not null default 0,
type char(4),
count int not null default 0
);

CREATE INDEX projectsumsagg_groupid ON project_sums_agg (group_id);
