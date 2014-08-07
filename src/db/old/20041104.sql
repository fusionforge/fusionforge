ALTER TABLE GROUPS rename column scm_box TO scm_box_old;
ALTER TABLE GROUPS add column scm_box text;
update groups set scm_box=scm_box_old;
--
--	NOTE - scm_box needs to be a fully qualified domain
--	name - probably requires a manual SQL update.
--
ALTER TABLE GROUPS drop column scm_box_old;
