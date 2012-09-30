alter table plugin_headermenu add COLUMN linkmenu character varying(256);
update plugin_headermenu set linkmenu = 'headermenu';
alter table plugin_headermenu add COLUMN linktype character varying(256);
update plugin_headermenu set linktype = 'url';
alter table plugin_headermenu add COLUMN htmlcode text;
