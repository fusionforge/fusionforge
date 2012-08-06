CREATE TABLE plugin_wiki_config
(
  group_id integer NOT NULL,
  config_name character varying(40) NOT NULL,
  config_value integer NOT NULL DEFAULT 0,
  CONSTRAINT plugin_wiki_config_pkey PRIMARY KEY (group_id, config_name)
)
WITH OIDS;
ALTER TABLE plugin_wiki_config OWNER TO gforge;

-- For existing wikis, we enable wikiwords as before.
-- Not doing it could break links.
INSERT INTO plugin_wiki_config
  SELECT group_id AS group_id, 'DISABLE_MARKUP_WIKIWORD' AS config_name, '0' AS config_value
  FROM group_plugin, plugins
  WHERE group_plugin.plugin_id = plugins.plugin_id AND plugin_name = 'wiki';

-- For existing wikis, we disable spam prevention.
-- This is a change, but cannot be a problem.
INSERT INTO plugin_wiki_config
  SELECT group_id AS group_id, 'NUM_SPAM_LINKS' AS config_name, '0' AS config_value
  FROM group_plugin, plugins
  WHERE group_plugin.plugin_id = plugins.plugin_id AND plugin_name = 'wiki';

