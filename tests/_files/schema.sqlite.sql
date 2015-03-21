
CREATE TABLE test (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(32),
    status INTEGER NOT NULL DEFAULT 0,
    datecreated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX test_name_key ON test(name);
CREATE INDEX test_status_key ON test(status);
CREATE INDEX test_datecreated_key ON test(datecreated);

