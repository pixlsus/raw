CREATE TABLE `raws` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` char(255) NOT NULL,
  `validated` tinyint(4) NOT NULL,
  `make` char(255) NOT NULL,
  `model` char(255) NOT NULL,
  `remark` mediumtext NOT NULL,
  `filesize` bigint(64) UNSIGNED NOT NULL,
  `checksum` char(64) NOT NULL,
  `mode` char(255) NOT NULL,
  `license` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `checksum` (`checksum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` char(255) NOT NULL,
  `password` char(255) NOT NULL,
  `email` char(255) NOT NULL,
  `notify` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
