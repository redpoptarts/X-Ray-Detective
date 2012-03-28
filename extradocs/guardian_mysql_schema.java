package org.guardian.bridge.mysql;

import java.util.ArrayList;

public class TableChecker {

    static boolean checkTables(MySQLBridge bridge) {
        final ArrayList<String> sql = new ArrayList<String>();
        sql.add("CREATE TABLE IF NOT EXISTS `gd_main` ("
                + "`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,"
                + "`date` DATETIME NOT NULL ,"
                + "`player_id` SMALLINT NOT NULL ,"
                + "`action` TINYINT NOT NULL ,"
                + "`world_id` TINYINT NOT NULL ,"
                + "`x` MEDIUMINT NOT NULL ,"
                + "`y` TINYINT NOT NULL ,"
                + "`z` MEDIUMINT NOT NULL ,"
                + "`plugin_id` TINYINT NOT NULL ,"
                + "`children` BOOLEAN NOT NULL ,"
                + "`parent_id` INT ,"
                + "`rbacked` BOOLEAN NOT NULL, "
                + "KEY `player_action_world` ( `player_id` , `action` , `world_id` ), KEY `x_y_z` ( `x` , `y` , `z` )) ENGINE = MYISAM ");
        sql.add("CREATE TABLE IF NOT EXISTS `gd_players` ("
                + "`player_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, "
                + "`player` VARCHAR( 16 ) NOT NULL UNIQUE,"
                + "`first_login` DATETIME, "
                + "`last_login` DATETIME, "
                + "`online_time` INT, "
                + "`last_ip` VARCHAR(64)"
                + ") ENGINE = MYISAM ;");
        sql.add("CREATE TABLE IF NOT EXISTS `gd_worlds` ("
                + "`world_id` TINYINT NOT NULL AUTO_INCREMENT PRIMARY KEY,"
                + "`world` VARCHAR( 255 ) NOT NULL UNIQUE"
                + ") ENGINE = MYISAM ");
        sql.add("CREATE TABLE IF NOT EXISTS `gd_plugins` ("
                + "`plugin_id` TINYINT NOT NULL AUTO_INCREMENT PRIMARY KEY,"
                + "`plugin` VARCHAR( 255 ) NOT NULL UNIQUE"
                + ") ENGINE = MYISAM ");
        sql.add("CREATE TABLE IF NOT EXISTS `gd_sub_text` ("
                + "`main_id` INT NOT NULL ,"
                + "`text` VARCHAR( 255 ) NOT NULL"
                + ") ENGINE = MYISAM ");
        sql.add("CREATE TABLE IF NOT EXISTS `gd_sub_item` ("
                + "`main_id` INT NOT NULL ,"
                + "`item_id` SMALLINT NOT NULL ,"
                + "`data` TINYINT NOT NULL ,"
                + "`enchantment_id` TINYINT NOT NULL ,"
                + "`enchantment_power` TINYINT NOT NULL ,"
                + "`amount` TINYINT NOT NULL"
                + ") ENGINE = MYISAM ");
        sql.add("CREATE TABLE IF NOT EXISTS `gd_sub_block` ("
                + "`main_id` INT NOT NULL ,"
                + "`from` TINYINT NOT NULL ,"
                + "`from_data` TINYINT NOT NULL ,"
                + "`to` TINYINT NOT NULL ,"
                + "`to_data` TINYINT NOT NULL"
                + ") ENGINE = MYISAM ");
        sql.add("CREATE TABLE IF NOT EXISTS `gd_sub_death` ("
                + "`main_id` INT NOT NULL ,"
                + "`cause` VARCHAR( 255 ) NOT NULL ,"
                + "`killer` VARCHAR( 32 ) NOT NULL"
                + ") ENGINE = MYISAM ");
        return bridge.executeSQLBatch(sql);
    }
}
