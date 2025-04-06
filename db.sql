CREATE TABLE `raws` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` char(255) NOT NULL,
  `validated` tinyint(4) NOT NULL,
  `make` char(255) NOT NULL,
  `model` char(255) NOT NULL,
  `remark` mediumtext NOT NULL,
  `checksum` char(64) NOT NULL,
  `mode` char(255) NOT NULL,
  `license` char(20) NOT NULL,
  `aspectratio` char(6) DEFAULT NULL,
  `bitspersample` tinyint(4) DEFAULT NULL,
  `pixels` float DEFAULT NULL,
  `masterset` tinyint(4) DEFAULT NULL,
  `state` char(10) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `filesize` bigint(20) DEFAULT NULL,
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
