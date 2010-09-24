update users set theme_id = 1 where theme_id = 2 or theme_id = 4;
delete from themes where theme_id = 2;
delete from themes where theme_id = 4;
insert into themes (dirname,fullname,enabled) values ('funky','Funky','t');
