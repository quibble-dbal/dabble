
CREATE TABLE test (
    id SERIAL PRIMARY KEY,
    name VARCHAR(32),
    status INTEGER NOT NULL DEFAULT 0,
    datecreated TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
);
CREATE UNIQUE INDEX test_name_key ON test(name);
CREATE INDEX test_status_key ON test(status);
CREATE INDEX test_datecreated_key ON test(datecreated);

