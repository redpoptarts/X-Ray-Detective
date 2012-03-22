<?php
//=====================================================
// X-Ray Detective Config - General Settings
//=====================================================
//
// For More Information Visit:
// http://dev.bukkit.org/server-mods/x-ray-detective/
//
// IRC Channel: (irc.esper.net) #xray
//
// Version: 0.01a
//
//=====================================================

//=============================
// AUTHORIZATION MODE
//=============================
// Select a method of granting access to the X-Ray Detection script.
//
// "username" - Users who are listed below are granted access automatically.
//              Do not use this method if your server is a cracked server (online-mode=false in server config)
// "password" - Anyone may login to the script if they enter the correct password. 
// "none"     - NOT RECOMMENDED: Everyone has admin powers

$auth_mode = "username";
$auth_allow_guest_users = "false";

// If AUTH_MODE is set to USERNAME
// List the usernames of all users who will be granted access rights. They will not require a password.
//
// IMPORTANT: They MUST login to your server from that computer before they can be granted access.
// If a valid user does NOT login to your server, they will still receive a message saying they do not have authorization to view the page.
//
// IMPORTANT: Because this method relies on Minecraft.net to validate their user account, do not use this option in offline mode.
//
// Example:
// $auth_admin_usernames = "Username1, Username2, Username3";
// $auth_mod_usernames   = "Username1, Username2, Username3";
// $auth_user_usernames  = "Username1, Username2, Username3";
$auth_admin_usernames = "YourUserName";
$auth_mod_usernames   = "example_mod_name1, example_mod_name2";
$auth_user_usernames  = "";

// If AUTH_MODE is set to PASSWORD
// Anyone may login to the script if they enter the correct password. 
//
// If they enter the Admin password, they are granted admin powers.
// If they enter the Mod password, they are granted mod powers.
// If they enter the User password, they are granted mod powers.
//
// Example: 
// $auth_admin_password = "adminpassword";
// $auth_mod_password   = "modpassword";
// $auth_user_password  = "userpassword";
$auth_admin_password = "adminpassword";
$auth_mod_password   = "modpassword";
$auth_user_password  = "userpassword";

//=============================
// Auto-watch settings
//=============================
// Users stats can be automatically tracked over time
//
//
//

// THESE SETTINGS ARE NOT YET IMPLEMENTED
$setting_autowatch_threshold = "100"; // Minimum number of stone that must be broken before a user can be automatically flagged as Watching (for snapshots)
$setting_autowatch_triggers = "all"; // Which resource types are capable of flagging a user
$setting_autowatch_start = "7"; // Players that have any stat hit this level are autowatched
								// 	Must be 0 thru 10 (0 is no ratio, 1 is low ratio, 10 is very high)
$setting_autowatch_stop = "6"; // Players whose max stat returns to this level are cleared from Watched status
								// 	Must be 0 thru 10 (0 is no ratio, 1 is low ratio, 10 is very high)
								


//=============================
// Mine detection settings
//=============================
$setting_ignorefirstore_before = 2; // If the first block broken in a mine in an ore, the stats will not count any ores broken among the first __ blocks.
$setting_mine_max_distance = 5; // A block must be within __ blocks of all previous blocks to be considered part of the same mine
								//	Recommended 1 to 10, 1 will cause more fragmented statistics, 10 will lump more stats together
$setting_postbreak_check = 3; 	// Number of blocks to check for after a cluster of ores.
								//  For detecting whether or not a player continues mining after finding ores.







?>