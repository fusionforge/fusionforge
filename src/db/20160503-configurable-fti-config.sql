-- This only clones a text search configuration
-- Its actual content is handled by a PHP script

CREATE TEXT SEARCH CONFIGURATION fusionforge (COPY='simple');
CREATE EXTENSION IF NOT EXISTS unaccent;
