alter table forum_attachment alter column mimetype type text;
alter table forum_attachment alter column mimetype set default 'application/octet-stream';
alter table forum_pending_attachment alter column mimetype type text;
alter table forum_pending_attachment alter column mimetype set default 'application/octet-stream';

