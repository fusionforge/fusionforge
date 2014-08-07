create table cron_history (
rundate int not null,
job text,
output text
);

CREATE INDEX cronhist_rundate ON cron_history(rundate);
