DROP TABLE IF EXISTS `client`;

CREATE TABLE `client` (
  `key` VARCHAR(255) NOT NULL,
  `value` TEXT DEFAULT NULL,
  `ts` int DEFAULT 0,
  KEY (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `key` VARCHAR(255) NOT NULL,
  `value` TEXT DEFAULT NULL,
  `ts` int DEFAULT 0,
  KEY (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `group`;

CREATE TABLE `group` (
  `key` INT NOT NULL,
  `value` TEXT DEFAULT NULL,
  `ts` int DEFAULT 0,
  KEY (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `cidr`;

CREATE TABLE `cidr` (
  `key` INT NOT NULL,
  `value` TEXT DEFAULT NULL,
  `ts` int DEFAULT 0,
  KEY (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `usertoken`;

CREATE TABLE `usertoken` (
  `key` INT NOT NULL,
  `value` TEXT DEFAULT NULL,
  `ts` int DEFAULT 0,
  KEY (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `token`;

CREATE TABLE `token` (
  `key` varchar(255) NOT NULL,
  `value` TEXT DEFAULT NULL,
  `ts` int DEFAULT 0,
  KEY (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
