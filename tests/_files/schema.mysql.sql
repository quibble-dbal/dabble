
CREATE TABLE test (
    id BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(32),
    status BIGINT UNSIGNED NOT NULL DEFAULT 0,
    datecreated TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE INDEX(name),
    INDEX(status),
    INDEX(datecreated)
) ENGINE=MEMORY DEFAULT CHARSET='UTF8';

