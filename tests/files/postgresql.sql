
DROP TABLE IF EXISTS test CASCADE;

CREATE TABLE test (
    id SERIAL NOT NULL PRIMARY KEY,
    name character varying(32),
    status integer DEFAULT 0 NOT NULL,
    datecreated timestamp with time zone DEFAULT now() NOT NULL
);

DROP TABLE IF EXISTS test2 CASCADE;

CREATE TABLE test2 (
    id SERIAL NOT NULL PRIMARY KEY,
    test integer NOT NULL REFERENCES test(id) ON DELETE CASCADE,
    data character varying(255)
);

INSERT INTO test VALUES
    (1, 'foo', 15, '2015-03-20 10:00:00 GMT'),
    (2, 'bar', 11, '1978-07-13 12:42:42 GMT'),
    (3,	NULL, 0, '2000-01-01 00:00:00 GMT');

CREATE INDEX test2_test_key ON test2 USING btree (test);
CREATE INDEX test_datecreated_key ON test USING btree (datecreated);
CREATE UNIQUE INDEX test_name_key ON test USING btree (name);
CREATE INDEX test_status_key ON test USING btree (status);

INSERT INTO test2 VALUES (1, 1, 'lorem ipsum');

