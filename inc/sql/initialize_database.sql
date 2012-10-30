SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `minecraft` DEFAULT CHARACTER SET utf8 ;
USE `minecraft` ;

-- -----------------------------------------------------
-- Table `minecraft`.`x-mines`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `minecraft`.`x-mines` (
  `mineid` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `playerid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `worldid` smallint(3) unsigned NOT NULL DEFAULT '0',
  `volume` int(5) unsigned NOT NULL DEFAULT '0',
  `first_block_ore` tinyint(1) NOT NULL,
  `last_break_date` datetime NOT NULL DEFAULT '2012-01-01 00:00:00',
  `postbreak_possible` int(5) unsigned DEFAULT '0',
  `postbreak_total` int(5) unsigned DEFAULT '0',
  PRIMARY KEY (`mineid`,`playerid`),
  UNIQUE KEY `mineid_UNIQUE` (`mineid`)
) ENGINE=InnoDB AUTO_INCREMENT=342 DEFAULT CHARSET=utf8$$


-- -----------------------------------------------------
-- Table `minecraft`.`x-worlds`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `minecraft`.`x-worlds` (
  `worldid` SMALLINT(3) NOT NULL AUTO_INCREMENT ,
  `worldname` VARCHAR(20) NOT NULL ,
  `worldalias` VARCHAR(45) NULL ,
  `last_date_processed` DATETIME NOT NULL DEFAULT '2012-01-01 00:00:00' ,
  `enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' ,
  PRIMARY KEY (`worldid`, `worldname`) ,
  UNIQUE INDEX `worldid_UNIQUE` (`worldid` ASC) ,
  UNIQUE INDEX `worldname_UNIQUE` (`worldname` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `minecraft`.`x-settings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `minecraft`.`x-settings`;


-- -----------------------------------------------------
-- Table `minecraft`.`x-stats`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `minecraft`.`x-stats` (
  `playerid` smallint(5) NOT NULL,
  `worldid` smallint(3) NOT NULL DEFAULT '0',
  `watch` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `punish` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `diamond_count` int(10) unsigned NOT NULL DEFAULT '0',
  `gold_count` int(10) unsigned NOT NULL DEFAULT '0',
  `lapis_count` int(10) unsigned NOT NULL DEFAULT '0',
  `mossy_count` int(10) unsigned NOT NULL DEFAULT '0',
  `iron_count` int(10) unsigned NOT NULL DEFAULT '0',
  `stone_count` int(21) unsigned NOT NULL DEFAULT '0',
  `diamond_ratio` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `gold_ratio` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `lapis_ratio` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `mossy_ratio` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `iron_ratio` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `stone_ratio` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `postbreak_ratio` decimal(6,2) unsigned DEFAULT NULL,
  `volume` int(5) unsigned DEFAULT NULL,
  `slope_before_neg` decimal(6,2) DEFAULT NULL,
  `slope_before_pos` decimal(6,2) DEFAULT NULL,
  `slope_after_neg` decimal(6,2) DEFAULT NULL,
  `slope_after_pos` decimal(6,2) DEFAULT NULL,
  `spread_before` tinyint(2) unsigned DEFAULT NULL,
  `spread_after` tinyint(2) unsigned DEFAULT NULL,
  `ore_begin` tinyint(2) unsigned DEFAULT NULL,
  `ore_length` tinyint(2) unsigned DEFAULT NULL,
  `first_block_ore` decimal(4,2) DEFAULT NULL,
  PRIMARY KEY (`playerid`,`worldid`),
  KEY `fk_x-stats_x-worlds1` (`worldid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8$$


-- -----------------------------------------------------
-- Table `minecraft`.`x-snapshots`
-- -----------------------------------------------------
-- Not yet implemented


-- -----------------------------------------------------
-- Table `minecraft`.`x-clusters`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `minecraft`.`x-clusters` (
  `clusterid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mineid` int(10) unsigned NOT NULL,
  `playerid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `worldid` smallint(3) unsigned NOT NULL DEFAULT '0',
  `ore_begin` int(6) unsigned NOT NULL DEFAULT '0',
  `ore_length` tinyint(2) unsigned DEFAULT NULL,
  `slope_before` decimal(6,2) DEFAULT NULL,
  `slope_after` decimal(6,2) DEFAULT NULL,
  `spread_before` tinyint(2) unsigned DEFAULT NULL,
  `spread_after` tinyint(2) unsigned DEFAULT NULL,
  PRIMARY KEY (`clusterid`,`mineid`,`playerid`),
  UNIQUE KEY `mineid_UNIQUE` (`clusterid`)
) ENGINE=InnoDB AUTO_INCREMENT=275 DEFAULT CHARSET=utf8$$


-- -----------------------------------------------------
-- Table `minecraft`.`x-playerinfo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `minecraft`.`x-playerinfo` (
  `playerid` smallint(5) NOT NULL,
  `watch` varchar(45) DEFAULT NULL,
  `punish` varchar(45) DEFAULT NULL,
  `firstlogin` datetime DEFAULT NULL,
  `lastlogin` datetime DEFAULT NULL,
  `onlinetime` datetime DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `total_stone` int(21) unsigned DEFAULT NULL,
  `total_diamond` int(10) unsigned DEFAULT NULL,
  `total_lapis` int(10) unsigned DEFAULT NULL,
  `total_gold` int(10) unsigned DEFAULT NULL,
  `total_mossy` int(10) unsigned DEFAULT NULL,
  `total_iron` int(10) unsigned DEFAULT NULL,
  `max_ratio_diamond` decimal(6,2) unsigned DEFAULT NULL,
  `max_ratio_lapis` decimal(6,2) unsigned DEFAULT NULL,
  `max_ratio_gold` decimal(6,2) unsigned DEFAULT NULL,
  `max_ratio_mossy` decimal(6,2) unsigned DEFAULT NULL,
  `max_ratio_iron` decimal(6,2) unsigned DEFAULT NULL,
  `avg_ratio_diamond` decimal(6,2) unsigned DEFAULT NULL,
  `avg_ratio_lapis` decimal(6,2) unsigned DEFAULT NULL,
  `avg_ratio_gold` decimal(6,2) unsigned DEFAULT NULL,
  `avg_ratio_mossy` decimal(6,2) unsigned DEFAULT NULL,
  `avg_ratio_iron` decimal(6,2) unsigned DEFAULT NULL,
  `postbreak_ratio` decimal(6,2) unsigned DEFAULT NULL,
  `max_slope_before_pos` decimal(6,2) DEFAULT NULL,
  `max_slope_before_neg` decimal(6,2) DEFAULT NULL,
  `max_slope_after_pos` decimal(6,2) DEFAULT NULL,
  `max_slope_after_neg` decimal(6,2) DEFAULT NULL,
  `avg_slope_before_pos` decimal(6,2) DEFAULT NULL,
  `avg_slope_before_neg` decimal(6,2) DEFAULT NULL,
  `avg_slope_after_pos` decimal(6,2) DEFAULT NULL,
  `avg_slope_after_neg` decimal(6,2) DEFAULT NULL,
  `count_slope_before_pos` int(5) DEFAULT '0',
  `count_slope_before_neg` int(5) DEFAULT '0',
  `count_slope_after_pos` int(5) DEFAULT '0',
  `count_slope_after_neg` int(5) DEFAULT '0',
  `avg_spread_before` tinyint(2) unsigned DEFAULT NULL,
  `avg_spread_after` tinyint(2) unsigned DEFAULT NULL,
  `avg_mine_volume` int(5) unsigned DEFAULT NULL,
  `avg_ore_begin` tinyint(2) unsigned DEFAULT NULL,
  `avg_ore_length` tinyint(2) unsigned DEFAULT NULL,
  `ratio_first_block_ore` decimal(4,2) unsigned DEFAULT NULL,
  `slope_before_preference` decimal(5,2) unsigned DEFAULT NULL,
  `slope_after_preference` decimal(5,2) unsigned DEFAULT NULL,
  `total_ores` int(5) unsigned DEFAULT '0',
  `total_clusters` int(5) unsigned DEFAULT '0',
  PRIMARY KEY (`playerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8$$

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
