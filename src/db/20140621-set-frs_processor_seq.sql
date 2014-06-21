SELECT setval('frs_processor_pk_seq', (SELECT MAX(processor_id) FROM frs_processor));
