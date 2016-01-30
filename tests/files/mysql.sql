
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `test` CASCADE;

CREATE TABLE `test` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `status` bigint(20) unsigned NOT NULL DEFAULT '0',
  `datecreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `status` (`status`),
  KEY `datecreated` (`datecreated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `test2` CASCADE;

CREATE TABLE `test2` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `test` bigint(20) unsigned NOT NULL,
  `data` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `test` (`test`),
  CONSTRAINT FOREIGN KEY(test) REFERENCES test(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO test VALUES
    (1, 'foo', 15, '2015-03-20 10:00:00'),
    (2, 'bar', 11, '1978-07-13 12:42:42'),
    (3, null, 0, '2000-01-01 00:00:00');

INSERT INTO test2 VALUES (1, 1, 'lorem ipsum');

