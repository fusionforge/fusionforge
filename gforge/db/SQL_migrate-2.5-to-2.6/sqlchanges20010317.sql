-- by: pfalcon
-- purpose: table to store pending mass mailings

CREATE TABLE massmail_queue (
id serial primary key,
type varchar(8) not null,
subject text not null,
message text not null,
queued_date int not null,
last_userid int not null default 0,
failed_date int not null default 0,
finished_date int not null default 0
);
