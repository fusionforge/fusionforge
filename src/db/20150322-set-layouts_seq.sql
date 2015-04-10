SELECT setval('layouts_pk_seq', (SELECT MAX(id) from layouts));
SELECT setval('layouts_rows_pk_seq', (SELECT MAX(id) from layouts_rows));
SELECT setval('layouts_rows_columns_pk_seq', (SELECT MAX(id) from layouts_rows_columns));
