ALTER TABLE forum_attachment         ADD COLUMN mimetype CHARACTER VARYING(32) DEFAULT 'unknown/unknown';
ALTER TABLE forum_pending_attachment ADD COLUMN mimetype CHARACTER VARYING(32) DEFAULT 'unknown/unknown';
