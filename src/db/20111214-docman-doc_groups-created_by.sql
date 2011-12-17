alter table doc_groups add column created_by integer DEFAULT 0 NOT NULL;
update doc_groups set created_by = 100;
