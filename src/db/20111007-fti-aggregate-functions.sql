CREATE OR REPLACE FUNCTION ff_tsvector_add (tsvector, tsvector)
RETURNS tsvector
AS '
   SELECT $1 || $2
' LANGUAGE SQL
IMMUTABLE
RETURNS NULL ON NULL INPUT;

DROP AGGREGATE IF EXISTS ff_tsvector_agg (tsvector);
CREATE AGGREGATE ff_tsvector_agg (tsvector) (
       sfunc = ff_tsvector_add,
       stype = tsvector
);
