
USE `%%DATABASE%%` ;

CREATE TABLE IF NOT EXISTS `%%DATABASE%%`.`x-config` (
  `conf_key` varchar(32),
  `conf_val` varchar(32) )
ENGINE=INNODB
DEFAULT CHARACTER SET = utf8;

CREATE  TABLE IF NOT EXISTS `%%DATABASE%%`.`x-mines` (
  `mineid` INT(3) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `playerid` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 ,
  `worldid` SMALLINT(3) UNSIGNED NOT NULL DEFAULT 0 ,
  `volume` INT(5) UNSIGNED NOT NULL DEFAULT 0 ,
  `first_block_ore` TINYINT(1) NOT NULL ,
  `last_break_date` DATETIME NOT NULL DEFAULT '2012-01-01 00:00:00' ,
  `diamond_ratio` DECIMAL(6,2) UNSIGNED NULL DEFAULT NULL ,
  `lapis_ratio` DECIMAL(6,2) UNSIGNED NULL DEFAULT NULL ,
  `iron_ratio` DECIMAL(6,2) UNSIGNED NULL DEFAULT NULL ,
  `gold_ratio` DECIMAL(6,2) UNSIGNED NULL DEFAULT NULL ,
  `mossy_ratio` DECIMAL(6,2) UNSIGNED NULL DEFAULT NULL ,
  PRIMARY KEY (`mineid`, `playerid`) ,
  UNIQUE INDEX `mineid_UNIQUE` (`mineid` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE  TABLE IF NOT EXISTS `%%DATABASE%%`.`x-worlds` (
  `worldid` SMALLINT(3) NOT NULL AUTO_INCREMENT ,
  `worldname` VARCHAR(20) NOT NULL ,
  `worldalias` VARCHAR(45) NULL ,
  `last_date_processed` DATETIME NOT NULL DEFAULT '2012-01-01 00:00:00' ,
  `enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' ,
  PRIMARY KEY (`worldid`, `worldname`) ,
  UNIQUE INDEX `worldid_UNIQUE` (`worldid` ASC) ,
  UNIQUE INDEX `worldname_UNIQUE` (`worldname` ASC) )
ENGINE = InnoDB;

CREATE  TABLE IF NOT EXISTS `%%DATABASE%%`.`x-stats` (
  `playerid` SMALLINT(5) NOT NULL ,
  `worldid` SMALLINT(3) NOT NULL DEFAULT '0' ,
  `watch` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' ,
  `punish` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' ,
  `diamond_count` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
  `gold_count` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
  `lapis_count` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
  `mossy_count` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
  `iron_count` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
  `stone_count` INT(21) UNSIGNED NOT NULL DEFAULT '0' ,
  `diamond_ratio` DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT '0.00' ,
  `gold_ratio` DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT '0.00' ,
  `lapis_ratio` DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT '0.00' ,
  `mossy_ratio` DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT '0.00' ,
  `iron_ratio` DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT '0.00' ,
  `stone_ratio` DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT '0.00' ,
  `postbreak_ratio` DECIMAL(6,2) UNSIGNED NULL DEFAULT NULL ,
  `volume` INT(5) UNSIGNED NULL DEFAULT NULL ,
  `slope_before_neg` DECIMAL(6,2) NULL DEFAULT NULL ,
  `slope_before_pos` DECIMAL(6,2) NULL DEFAULT NULL ,
  `slope_after_neg` DECIMAL(6,2) NULL DEFAULT NULL ,
  `slope_after_pos` DECIMAL(6,2) NULL DEFAULT NULL ,
  `spread_before` TINYINT(2) UNSIGNED NULL DEFAULT NULL ,
  `spread_after` TINYINT(2) UNSIGNED NULL DEFAULT NULL ,
  `ore_begin` TINYINT(2) UNSIGNED NULL DEFAULT NULL ,
  `ore_length` TINYINT(2) UNSIGNED NULL DEFAULT NULL ,
  `first_block_ore` DECIMAL(4,2) NULL DEFAULT NULL ,
  PRIMARY KEY (`playerid`, `worldid`) ,
  INDEX `fk_x-stats_x-worlds1` (`worldid` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `%%DATABASE%%`.`x-snapshots` (
  `playerid` SMALLINT(5) NOT NULL ,
  `worldid` SMALLINT(3) NOT NULL ,
  `diamond_count` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
  `gold_count` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
  `lapis_count` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
  `mossy_count` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
  `iron_count` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
  `stone_count` INT(21) UNSIGNED NOT NULL DEFAULT '0' ,
  `diamond_ratio` DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT '0.00' ,
  `gold_ratio` DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT '0.00' ,
  `lapis_ratio` DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT '0.00' ,
  `mossy_ratio` DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT '0.00' ,
  `iron_ratio` DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT '0.00' ,
  `stone_ratio` DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT '0.00' ,
  PRIMARY KEY (`playerid`, `worldid`) ,
  INDEX `fk_x-stats_x-worlds1` (`worldid` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `%%DATABASE%%`.`x-clusters` (
  `clusterid` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `mineid` INT(10) UNSIGNED NOT NULL ,
  `playerid` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 ,
  `worldid` SMALLINT(3) UNSIGNED NOT NULL DEFAULT 0 ,
  `ore_begin` INT(6) UNSIGNED NOT NULL DEFAULT 0 ,
  `ore_length` TINYINT(2) UNSIGNED NULL ,
  `slope_before` DECIMAL(6,2) NULL DEFAULT NULL ,
  `slope_after` DECIMAL(6,2) NULL DEFAULT NULL ,
  `spread_before` TINYINT(2) UNSIGNED NULL DEFAULT NULL ,
  `spread_after` TINYINT(2) UNSIGNED NULL DEFAULT NULL ,
  PRIMARY KEY (`clusterid`, `mineid`, `playerid`) ,
  UNIQUE INDEX `mineid_UNIQUE` (`clusterid` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;