ALTER TABLE forum_attachment         ADD COLUMN mimetype CHARACTER VARYING(32) DEFAULT 'application/octet-stream';
ALTER TABLE forum_pending_attachment ADD COLUMN mimetype CHARACTER VARYING(32) DEFAULT 'application/octet-stream';
