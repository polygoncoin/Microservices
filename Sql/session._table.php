DROP TABLE IF EXISTS `session`;
CREATE TABLE `session` (
    `sessionId` VARCHAR(250) NOT NULL,
    `sessionData` TEXT NOT NULL,
    `lastAccessed` DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY (`sessionId`)
) ENGINE = InnoDB;
