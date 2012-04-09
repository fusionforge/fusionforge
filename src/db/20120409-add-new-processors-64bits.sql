insert into frs_processor (processor_id, name) VALUES ((select max(processor_id)+1 from frs_processor), 'AMD64');
insert into frs_processor (processor_id, name) VALUES ((select max(processor_id)+1 from frs_processor), 'x86-64');
insert into frs_processor (processor_id, name) VALUES ((select max(processor_id)+1 from frs_processor), 'EM64T');
insert into frs_processor (processor_id, name) VALUES ((select max(processor_id)+1 from frs_processor), 'Intel 64');
