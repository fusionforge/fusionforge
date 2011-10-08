CREATE OR REPLACE FUNCTION ff_tsvector_add (v1 tsvector, v2 tsvector)
RETURNS tsvector
AS $$
BEGIN
  RETURN v1 || v2 ;
END;
$$ LANGUAGE plpgsql
IMMUTABLE
RETURNS NULL ON NULL INPUT;

DROP AGGREGATE IF EXISTS ff_tsvector_agg (tsvector);
CREATE AGGREGATE ff_tsvector_agg (tsvector) (
       sfunc = ff_tsvector_add,
       stype = tsvector
);
