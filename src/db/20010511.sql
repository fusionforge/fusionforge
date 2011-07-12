-- by: pfalcon
-- purpose: table with history of users' rating, for tracking changes/graphing

CREATE TABLE user_metric_history(
month int not null,
day  int not null,
user_id int not null,
ranking int not null,
metric float not null);

CREATE UNIQUE INDEX user_metric_history_date_userid
ON user_metric_history(month,day,user_id);
