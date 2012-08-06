--
--      Making docman binary safe
--
alter table doc_data add column filename text;
alter table doc_data add column filetype text;

--
--	NOTE THE doc_data-migration.php SCRIPT
--

