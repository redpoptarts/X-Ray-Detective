<?php
if( !strnatcasecmp(trim($db_type),"LB") ){ require_once('src/dbmodule_LB.php'); }
if( !strnatcasecmp(trim($db_type),"GD") ){ require_once('src/dbmodule_GD.php'); }

// -----------------------------------------------
//
// XRay-Detective - In-Depth Statistics On
//                  each player's X-Ray Usage
//
// http://dev.bukkit.org/server-mods/xray-detective/
// 
// -----------------------------------------------




$db_resource = @mysql_connect($db_host, $db_user, $db_pass) or die($_SERVER["SCRIPT_FILENAME"] . "Could not connect to DB host.");
@mysql_selectdb($db_name) or die("Could not select DB");





// Process values from config settings and correct them in necessary
$mine_settings = compact("setting_ignorefirstore_before","setting_mine_max_distance","setting_postbreak_check");
$auth_allow_guest_users = FixInput_Bool($auth_allow_guest_users);

$auth_admin_usernames_exploded = explode(",",$auth_admin_usernames); foreach($auth_admin_usernames_exploded as &$input_fix_item){ $input_fix_item = trim($input_fix_item); }
$auth_mod_usernames_exploded   = explode(",",$auth_mod_usernames); foreach($auth_mod_usernames_exploded as &$input_fix_item){ $input_fix_item = trim($input_fix_item); }
$auth_user_usernames_exploded  = explode(",",$auth_user_usernames); foreach($auth_user_usernames_exploded as &$input_fix_item){ $input_fix_item = trim($input_fix_item); }

$limits["diamond"] = array_fill(0, 10, 0); $limits["lapis"] = array_fill(0, 10, 0); $limits["gold"] = array_fill(0, 10, 0); $limits["mossy"] = array_fill(0, 10, 0); $limits["iron"] = array_fill(0, 10, 0);

// Here are the sensitivity limits for each block type.
// 3 is the LOW value (GREEN)
// 6 is the MID value (YELLOW)
// 9 is the HIGH value (RED)
//
// All other color values will be created for you automatically.
//
/////////////////////////////////////////[   ]///////////[    ]//////////[   ]/////
$limits["diamond"] = array(0 => 0,	3 => "0.5", 	6 => "1.25",	9 => "2");
$limits["lapis"] =   array(0 => 0,	3 => "1",		6 => "2",   	9 => "3");
$limits["gold"] =    array(0 => 0, 	3 => "2.5",		6 => "4", 	9 => "6");
$limits["mossy"] =   array(0 => 0,	3 => "5",   	6 => "10",		9 => "15");
$limits["iron"] =    array(0 => 0,	3 => "15",  	6 => "20",		9 => "30");
/////////////////////////////////////////[   ]///////////[    ]//////////[   ]/////

//echo "LIMITS::<br>"; print_r($limits); echo "<br><br>";

foreach($limits as $limit_type => $limit_array)
{
	//echo "BLOCK TYPE: $limit_block <br>";
	$limits[$limit_type][1] = $limits[$limit_type][3] * 0.33;
	$limits[$limit_type][2] = $limits[$limit_type][3] * 0.66;
	$limits[$limit_type][4] = $limits[$limit_type][3] + ($limits[$limit_type][6] - $limits[$limit_type][3]) * 0.33;
	$limits[$limit_type][5] = $limits[$limit_type][3] + ($limits[$limit_type][6] - $limits[$limit_type][3]) * 0.66;
	$limits[$limit_type][7] = $limits[$limit_type][6] + ($limits[$limit_type][9] - $limits[$limit_type][6]) * 0.33;
	$limits[$limit_type][8] = $limits[$limit_type][6] + ($limits[$limit_type][9] - $limits[$limit_type][6]) * 0.66;
	$limits[$limit_type][10] = $limits[$limit_type][9] + ($limits[$limit_type][9] - $limits[$limit_type][6]) * 1.33;
	asort($limits[$limit_type]);
	//echo "[" . $limit_type . "]<br>"; print_r($limits[$limit_type]); echo "<br>";
}



if($_POST['form']!=""){$_GET = $_POST;}

$world_array = GetWorlds();

$command = $_GET["command"];
$block_type = $_GET["block_type"]; if($block_type==""){ $block_type = 56; }
$stone_threshold = $_GET["stone_threshold"]; if($stone_threshold==""){ $stone_threshold = 500; }
$limit_results = $_GET["limit_results"]; if($limit_results==""){ $limit_results = 100; }
$world_id = $_GET["worldid"]; if($world_id==""){ $world_name = $world_array[0]["worldid"]; }
foreach($world_array as $world_key => $world_item )
{
	if($world_id==$world_item["worldid"]){ $world_name = $world_item["worldname"]; $world_alias = $world_item["worldalias"];}
}

$player_name = $_GET["player"];	$player_id = GetPlayerID_ByName($player_name);
$args = $_GET["args"];
$_GET['authKey'] = "yourpassword";
$receivedMD5 = md5($_GET['authKey']);

switch($block_type){
	case 56: $limit_block = "diamond"; break;
	case 25: $limit_block = "lapis"; break;
	case 14: $limit_block = "gold"; break;
    case 48: $limit_block = "mossy"; break;
    case 15: $limit_block = "iron"; break;
	default: $limit_block = "invalid"; break;
}
//echo "LIMIT BLOCK: $limit_block<BR>";
//echo "WORLD ID: $world_id<BR>";
//echo "WORLD NAME: $world_name<BR>";
//echo "WORLD ALIAS: $world_alias<BR>";

/*
echo "ARGUMENTS [GET] : ----<br>";
print_r($_GET); echo "<br>";
echo "---------------<br>";
echo "ARGUMENTS [POST] : ----<br>";
print_r($_POST); echo "<br>";
echo "---------------<br>";
*/

$system_table_player = "";
$system_table_data[0] = "";
$playerkey = "";
$playerkey_id = "";
$sql_key_before = "";
$sql_key_after = "";
$playerkey_id1 = 0;

if (strtolower($db_type) == "bb")
{
	echo "$db_type DATABASE TYPE NOT YET SUPPORTED<BR>"; exit();
    $system_table_player = "`".$dbprefix."bbusers`";
    $system_table_data[0] = "`".$dbprefix."bbdata`";
    $playerkey_name = "name";
    $playerkey_id   = "player";
    $playerkey_id1   = "id";
    $sql_key_before = "`type`=";
    $sql_key_after = "`action`='1'";
    
} elseif (strtolower($db_type) == "gd")
{
	echo "$db_type DATABASE TYPE NOT YET SUPPORTED<BR>"; exit();
    $system_table_player = "`".$dbprefix."bbusers`";
    $system_table_data[0] = "`".$dbprefix."bbdata`";
    $playerkey_name = "name";
    $playerkey_id   = "player";
    $playerkey_id1   = "id";
    $sql_key_before = "`type`=";
    $sql_key_after = "`action`='1'";
    
} elseif (strtolower($db_type) == "lb")
{
    $system_table_player = "`".$dbprefix."lb-players`";
    $system_table_data[0] = "`".$dbprefix."lb-".$world_array[0]["worldname"]."`";
    $system_table_data[1] = "`".$dbprefix."lb-".$world_array[1]["worldname"]."`";
    $playerkey_name = "playername";
    $playerkey_id1   = "playerid";
    $playerkey_id = $playerkey_id1;
    $sql_key_before = "`replaced`=";
    $sql_key_after = "`type`='0'";
} else
{
	echo "ERROR: INVALID DATABASE TYPE: [$db_type]";
	
}

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
	$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType)
  {
	case "text":
	  $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
	  break;    
	case "long":
	case "int":
	  $theValue = ($theValue != "") ? intval($theValue) : "NULL";
	  break;
	case "double":
	  $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
	  break;
	case "date":
	  $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
	  break;
	case "defined":
	  $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
	  break;
  }
  return $theValue;
} }

function get_mysql_info($linkid = null){
    $linkid? $strInfo = mysql_info($linkid) : $strInfo = mysql_info();
   
    $return = array();
    ereg("Records: ([0-9]*)", $strInfo, $records);
    ereg("Duplicates: ([0-9]*)", $strInfo, $dupes);
    ereg("Warnings: ([0-9]*)", $strInfo, $warnings);
    ereg("Deleted: ([0-9]*)", $strInfo, $deleted);
    ereg("Skipped: ([0-9]*)", $strInfo, $skipped);
    ereg("Rows matched: ([0-9]*)", $strInfo, $rows_matched);
    ereg("Changed: ([0-9]*)", $strInfo, $changed);
   
    $return['records'] = $records[1];
    $return['duplicates'] = $dupes[1];
    $return['warnings'] = $warnings[1];
    $return['deleted'] = $deleted[1];
    $return['skipped'] = $skipped[1];
    $return['rows_matched'] = $rows_matched[1];
    $return['changed'] = $changed[1];
   
    return $return;
}

function GetPlayerID_ByName(&$player_name)
{
	// Get ID of players whose names partially match the search parameter
	$sql_getPlayerID  = "SELECT `playerid` FROM `lb-players`";
	$sql_getPlayerID .= " WHERE `playername` LIKE '%$player_name%'";
	$sql_getPlayerID .= " ORDER BY `lastlogin` DESC LIMIT 1";
	//echo "SQL QUERY: <BR>" . $sql_getWorlds . "<BR>";
	$res_getPlayerID = mysql_query($sql_getPlayerID) or die("getPlayerID: " . mysql_error());
	if( mysql_num_rows($res_getPlayerID) > 0 )
	{
		while(($PlayerID_result[] = mysql_fetch_assoc($res_getPlayerID)) || array_pop($PlayerID_result));
		$playerid = $PlayerID_result[0]["playerid"];
		//echo "PLAYER ID ARRAY: "; print_r($PlayerID_result); echo "<BR>";
		//echo "PLAYER FOUND: ID = [$playerid]<BR>";
	}
	else
	{
		return false;
	}	
	
	return $playerid;
}

function GetPlayerName_ByID(&$playerid)
{
	// Get Username of player whose ID matches the search parameter
	$sql_getPlayerName  = "SELECT `playername` FROM `lb-players`";
	$sql_getPlayerName .= " WHERE `playerid` = ". $playerid;
	$sql_getPlayerName .= " ORDER BY `lastlogin` DESC LIMIT 1";
	//echo "SQL QUERY: <BR>" . $sql_getPlayerName . "<BR>";
	$res_getPlayerName = mysql_query($sql_getPlayerName) or die("getPlayerName: " . mysql_error());
	
	if( mysql_num_rows($res_getPlayerName) > 0 )
	{
		while(($PlayerName_result[] = mysql_fetch_assoc($res_getPlayerName)) || array_pop($PlayerName_result));
		$playername = $PlayerName_result[0]["playername"];
		//echo "PLAYER NAME ARRAY: "; print_r($PlayerName_result); echo "<BR>";
		//echo "PLAYER FOUND: NAME = [$playername]<BR>";
	}
	else
	{
		return false;
	}	
	
	return $playername;
}

function GetWorlds()
{
	$sql_getWorlds = file_get_contents(dirname($_SERVER['SCRIPT_FILENAME']). "/src/sql/getworlds.sql");
	//echo "SQL QUERY: <BR>" . $sql_getWorlds . "<BR>";
	$res_getWorlds = mysql_query($sql_getWorlds) or die("GetWorlds: " . mysql_error());
	while(($WorldsArray[] = mysql_fetch_assoc($res_getWorlds)) || array_pop($WorldsArray));
	
	return $WorldsArray;
}

function GetSingleStats($playerid)
{
	if(!PlayerExists($playerid))
	{
		echo "ERROR: The player you specified does not exist. (Player ID: '$playerid')<BR>";
	} else
	{
		$sql_getSingleStats  = "SELECT * FROM `x-worlds` AS w ";
		$sql_getSingleStats .= " LEFT JOIN (SELECT * FROM `x-stats` ";
		$sql_getSingleStats .= "	WHERE `playerid` = $playerid) AS p ON p.worldid = w.worldid ";
		echo "SQL QUERY: <BR>" . $sql_getSingleStats . "<BR>";
		$res_getSingleStats = mysql_query($sql_getSingleStats) or die("GetSingleStats: " . mysql_error());
		while(($SingleStatsArray[] = mysql_fetch_assoc($res_getSingleStats)) || array_pop($SingleStatsArray));
		
		return $SingleStatsArray;
	}
	
	return false;
}

/*
function PlayerExists($playerid)
{
	// Get ID of players whose names partially match the search parameter
	$sql_PlayerIDexists  = "SELECT `playerid` FROM `lb-players`";
	$sql_PlayerIDexists .= " WHERE `playerid` = '$playerid'";
	//echo "SQL QUERY: <BR>" . $sql_PlayerIDexists . "<BR>";
	$res_PlayerIDexists = mysql_query($sql_PlayerIDexists) or die("PlayerIDexists: " . mysql_error());
	if( mysql_num_rows($res_PlayerIDexists) > 0 )
	{
		return true;
	}
	else
	{
		return false;
	}	
}*/

function TopList($world_id, $limit_results, &$block_type, &$stone_threshold)
{
	if($stone_threshold==""){$stone_threshold = 500;}
	if($world_id=="")
	{
		echo "ERROR: No World ID was specified.<br>";
		return false;
	} else {
		$sql_top = "SELECT `playerid`, `playername`, `firstlogin`, `worldid`, `punish`, `watch`, ";
		$sql_top .= " `diamond_count`, `gold_count`, `lapis_count`, `mossy_count`, `iron_count`, `stone_count`,";
		$sql_top .= " `diamond_ratio`, `gold_ratio`, `lapis_ratio`, `mossy_ratio`, `iron_ratio`, `stone_ratio` ";
		$sql_top .= " FROM `x-stats`";
		$sql_top .= " LEFT JOIN `lb-players` USING (playerid)";
		$sql_top .= " WHERE worldid = '".(integer)$world_id."'";
		$sql_top .= " 	AND stone_count > ". (integer)$stone_threshold;
		$sql_top .= " ORDER BY ";
		switch($block_type){
			case 56: $sql_top .= "`diamond_ratio`"; break;
			case 25: $sql_top .= "`lapis_ratio`"; break;
			case 14: $sql_top .= "`gold_ratio`"; break;
			case 48: $sql_top .= "`mossy_ratio`"; break;
			case 15: $sql_top .= "`iron_ratio`"; break;
			default: $sql_top .= "`diamond_ratio`"; break;
		}
		$sql_top .= " DESC";
		if($limit_results>0){ $sql_top .= " limit $limit_results;"; }
		//echo "SQL_QUERY: <br>". $sql_top . "<br>";
		$res_top = mysql_query($sql_top) or die("top: " . mysql_error());
		while(($TopArray[] = mysql_fetch_assoc($res_top)) || array_pop($TopArray)); 

		return $TopArray;
	}
}

function AddNewBreaks()
{
	$sql_getWorlds = file_get_contents(dirname($_SERVER['SCRIPT_FILENAME']). "/src/sql/getworlds.sql");
	//echo "SQL QUERY: <BR>" . $sql_getWorlds . "<BR>";
	$res_getWorlds = mysql_query($sql_getWorlds) or die("GetWorlds: " . mysql_error());
	while(($WorldsArray[] = mysql_fetch_assoc($res_getWorlds)) || array_pop($WorldsArray)); 	
	
	// Detect datetime of most recent break
	// This prevents omitting any breaks that occurr during this script

	$sql_getdate = "";
	foreach($WorldsArray as $world_index => $world_item)
	{
		if($world_index>0){ $sql_getdate .= " UNION ALL"; }
		$sql_getdate .= " SELECT '".$world_item["worldid"]."' AS worldid, MAX(date) AS latest_break_date";
		$sql_getdate .= " FROM `lb-".$world_item["worldname"]."`";
		$sql_getdate .= " WHERE  replaced = 1";
		$sql_getdate .= "     OR replaced = 15";
		$sql_getdate .= "     OR replaced = 14";
		$sql_getdate .= "     OR replaced = 56";
		$sql_getdate .= "     OR replaced = 25";
		$sql_getdate .= "     OR replaced = 48";
		$sql_getdate .= "     AND type = 0";
	}

	//echo "SQL_QUERY: <br>". $sql_getdate . "<br>";
	$res_getdate = mysql_query($sql_getdate) or die("top: " . mysql_error());
	while(($LatestDateArray[] = mysql_fetch_assoc($res_getdate)) || array_pop($LatestDateArray));
	//echo "LATEST_DATE_ARRAY: <BR>"; print_r($LatestDateArray); echo "<BR>";
		
	foreach($WorldsArray as $world_index => $world_item)
	{
		echo "====================================================<br>";
		echo "Processing World [".$world_item["worldalias"]."]<BR>";
		// Check for latest date entry in X-Stats Table
		echo "...This world was last checked on: ". $world_item["last_date_processed"] ."<BR>"; //TODO: Change format to TIME AGO
		$found_break = false;
		foreach($LatestDateArray as $wdate_item)
		{
			//echo "WORLD ID COMPARE [". $wdate_item["worldid"] . "] vs [" . $world_item["worldid"] . "]<BR>";
			if($wdate_item["worldid"]==$world_item["worldid"])
			{
				$found_break = true;
				$latest_break_date = $wdate_item["latest_break_date"];
				echo "...The last break in this world occurred on: ". $wdate_item["latest_break_date"] . "<BR>";
			}
		}
		
		if(!$found_break)
		{
			echo "...This world has no mining history. Aborting scan.<br>";
		} else {
			// Get ALL new breaks after latest_date_processed
			echo "...Beginning User Scan, Please Be Patient...";
			$sql_newbreaks  = "INSERT INTO `minecraft`.`x-stats` ";
			$sql_newbreaks .= " (`playerid`, `worldid`, `diamond_count`, `gold_count`, `lapis_count`, `mossy_count`, `iron_count`, `stone_count`) ";
			$sql_newbreaks .= " SELECT p.playerid, '".$world_item["worldid"]."', IFNULL(diamond_info.cnt,0) AS diamond_count, IFNULL(gold_info.cnt,0) AS gold_count, ";
			$sql_newbreaks .= " 	IFNULL(lapis_info.cnt,0) AS lapis_count, IFNULL(mossy_info.cnt,0) AS mossy_count, IFNUlL(iron_info.cnt,0) AS iron_count, IFNULL(stone_info.cnt,0) AS stone_count";
			$sql_newbreaks .= " FROM `lb-players`";
			$sql_newbreaks .= " AS p";
			$sql_newbreaks .= " INNER JOIN (SELECT playerid, count(playerid) AS cnt FROM `lb-".$world_item["worldname"]."` ";
			$sql_newbreaks .= " 	WHERE ((date BETWEEN '". $world_item["last_date_processed"] ."' AND '".$latest_break_date."') AND replaced = 1 AND type = 0 AND y <= 50) ";
			$sql_newbreaks .= " 	GROUP BY playerid) AS stone_info ON p.playerid = stone_info.playerid";
			$sql_newbreaks .= " LEFT JOIN (SELECT playerid, count(playerid) AS cnt FROM `lb-".$world_item["worldname"]."` ";
			$sql_newbreaks .= " 	WHERE ((date BETWEEN '". $world_item["last_date_processed"] ."' AND '".$latest_break_date."') AND replaced = 56 AND type = 0 AND y <= 50) ";
			$sql_newbreaks .= " 	GROUP BY playerid) AS diamond_info ON p.playerid = diamond_info.playerid";
			$sql_newbreaks .= " LEFT JOIN (SELECT playerid, count(playerid) AS cnt FROM `lb-".$world_item["worldname"]."` ";
			$sql_newbreaks .= " 	WHERE ((date BETWEEN '". $world_item["last_date_processed"] ."' AND '".$latest_break_date."') AND replaced = 14 AND type = 0 AND y <= 50) ";
			$sql_newbreaks .= " 	GROUP BY playerid) AS gold_info ON p.playerid = gold_info.playerid";
			$sql_newbreaks .= " LEFT JOIN (SELECT playerid, count(playerid) AS cnt FROM `lb-".$world_item["worldname"]."` ";
			$sql_newbreaks .= " 	WHERE ((date BETWEEN '". $world_item["last_date_processed"] ."' AND '".$latest_break_date."') AND replaced = 15 AND type = 0 AND y <= 50) ";
			$sql_newbreaks .= " 	GROUP BY playerid) AS iron_info ON p.playerid = iron_info.playerid";
			$sql_newbreaks .= " LEFT JOIN (SELECT playerid, count(playerid) AS cnt FROM `lb-".$world_item["worldname"]."` ";
			$sql_newbreaks .= " 	WHERE ((date BETWEEN '". $world_item["last_date_processed"] ."' AND '".$latest_break_date."') AND replaced = 48 AND type = 0 AND y <= 50) ";
			$sql_newbreaks .= " 	GROUP BY playerid) AS mossy_info ON p.playerid = mossy_info.playerid";
			$sql_newbreaks .= " LEFT JOIN (SELECT playerid, count(playerid) AS cnt FROM `lb-".$world_item["worldname"]."` ";
			$sql_newbreaks .= " 	WHERE ((date BETWEEN '". $world_item["last_date_processed"] ."' AND '".$latest_break_date."') AND replaced = 21 AND type = 0 AND y <= 50) ";
			$sql_newbreaks .= " 	GROUP BY playerid) AS lapis_info ON p.playerid = lapis_info.playerid";
			$sql_newbreaks .= " GROUP BY p.playerid";
			$sql_newbreaks .= " ON DUPLICATE KEY UPDATE `diamond_count`=`diamond_count`+VALUES(diamond_count), `gold_count`=`gold_count`+VALUES(gold_count),";
			$sql_newbreaks .= " 	 `lapis_count`=`lapis_count`+VALUES(lapis_count), `iron_count`=`iron_count`+VALUES(iron_count), `stone_count`=`stone_count`+VALUES(stone_count)";
			$res_newbreaks = mysql_query($sql_newbreaks);
			if(mysql_errno())
			{
				die("SQL_QUERY[newbreaks]: " . $sql_newbreaks . "<BR> " . mysql_error() . "<BR>");
			}else { echo "DONE!<BR>"; }
			
	
			//while(($BreaksArray[] = mysql_fetch_assoc($res_newbreaks)) || array_pop($BreaksArray)); 
			$stats_sql = get_mysql_info($resource_newbreaks);
			//echo "STATS_SQL: <BR>"; print_r($stats_sql); echo "<BR>";
			echo "-----------------------------------------------<br>";
			echo "Summary For World [".$world_item["worldalias"]."]<BR>";
			echo "-----------------------------------------------<br>";
			echo "..." . $stats_sql["records"]." Users Processed.<BR>";
			echo "..." . ($stats_sql["records"] - $stats_sql["duplicates"]) . " New Users Found.<BR>";
			echo "..." . $stats_sql["duplicates"]." Users Updated.<BR>";
			echo "-----------------------------------------------<br>";
			
			
			// Update World's LAST_PROCESSED_DATE to curent time
			$sql_setdate = "UPDATE `x-worlds` SET `last_date_processed`='".$latest_break_date."' WHERE `worldid`='".$world_item["worldid"]."'";
			//echo "SQL_QUERY: <br>". $sql_setdate . "<br>";
			$res_setdate = mysql_query($sql_setdate) or die("SetDate([".$world_item["worldalias"]."] => '".$latest_break_date."'): " . mysql_error());
			
		}
	}
	UpdateTotals();
}

function UpdateTotals()
{
	$sql_UpdateTotals = file_get_contents(dirname($_SERVER['SCRIPT_FILENAME']). "/src/sql/update_totals.sql");
	//echo "SQL QUERY: <BR>" . $sql_UpdateTotals . "<BR>";
	return $res_UpdateTotals = mysql_query($sql_UpdateTotals) or die("UpdateTotals: " . mysql_error());
}

function AddPlayerMines($playerid, $world_id, $mine_settings)
{
	// Check to make sure player exists
	if(!PlayerExists($playerid))
	{
		echo "ERROR: The player you specified does not exist. (Player ID: '$playerid')<BR>";
	} else
	{

		extract($mine_settings);
		$datetime_now = new DateTime;
		$datetime_hour_ago = new DateTime;
		$datetime_hour_ago->modify( '-1 hour' );
		

		
		$latest_mine_date = GetLatestMine($playerid, 1);
		
		
		// Get all breaks after date
		// ------------------------------
		
		$sql_getPlayerBreaks  = "SELECT `replaced` AS beforeblock, `x`, `y`, `z`, `date` FROM `lb-worldether`";
		$sql_getPlayerBreaks .= " WHERE playerid = '$playerid' ";
		$sql_getPlayerBreaks .= "		AND ( replaced = 1 ";
		$sql_getPlayerBreaks .= "	    OR replaced = 15";
		$sql_getPlayerBreaks .= "	    OR replaced = 14";
		$sql_getPlayerBreaks .= "	    OR replaced = 56";
		$sql_getPlayerBreaks .= "	    OR replaced = 25";
		$sql_getPlayerBreaks .= "	    OR replaced = 48 ) ";
		$sql_getPlayerBreaks .= "	    AND type = 0 ";
		$sql_getPlayerBreaks .= "	    AND y <= 50 ";
		$sql_getPlayerBreaks .= "	    AND date > '$latest_mine_date' ";
		$sql_getPlayerBreaks .= " ORDER BY date DESC";
		
		//echo "SQL QUERY: <BR>" . $sql_getPlayerBreaks . "<BR>";
		$res_getPlayerBreaks = mysql_query($sql_getPlayerBreaks) or die("getPlayerBreaks: " . mysql_error());
		while(($FullBreaksArray[] = mysql_fetch_assoc($res_getPlayerBreaks)) || array_pop($FullBreaksArray));
		
		$FullBreaksArray = array_reverse($FullBreaksArray); //echo "FULL BREAKS ARRAY: "; print_r($FullBreaksArray); echo "<BR>";
		//array_push($FullBreaksArray,$init_prev_break);
		
		// Process all breaks into chunks
		// ------------------------------
		echo "<BR><BR>[========================[WORLD $world_id]========================]<BR>";
		echo "Analyzing mining behavior of player [".GetPlayerName_ByID($playerid)."] in World [$world_id]...<br>";
		echo "<BR><BR>[------------------------PROCESS------------------------]<BR>";
		// Initiate statistic arrays and variables
		$init_prev_break = array("replaced"=>"0", "beforeblock"=>"0", "x"=>"0","y"=>"64","z"=>"0");
		$init_current_mine = array("breaks"=>array(),"ores"=>array(),"stats"=>array("first_block_ore"=>false,"total_volume"=>0,"adjusted_volume"=>0,"total_ores"=>0,"total_notores"=>0,"postbreak_possible"=>0,"postbreak_total"=>0), "clusters"=>array() );
		$init_cluster = array("nearby_before"=>array(), "nearby_after"=>array(), "ore_begin"=>NULL, "ore_length"=>NULL);
		
		$Mine_Array = array();

		$current_mine = $init_current_mine; $prev_break=$init_prev_break;
		$current_cluster = $init_cluster; $prev_cluster=$init_cluster;

		$fullbreaks_item = array();	$fullbreaks_done = false; 
		$recent_depth = array(); $inside_cluster = false;
		$blocks_since_ore = 0;
	
		// Process each break, moving it from the original array into smaller chunks (mines)
		while( !$fullbreaks_done && $fullbreaks_item = array_pop($FullBreaksArray) )
		{
			if(count($current_mine["breaks"]) == 0){ echo "Parsing Mine [".count($Mine_Array)."]...<BR>"; }
			//echo "<".$fullbreaks_item["beforeblock"]."{";
			
			// Add current break to a list of recent breaks history,(Used for calculating slope near clusters)
			if(	$fullbreaks_item["beforeblock"]=="1" || $fullbreaks_item["beforeblock"]=="3" ) // Only keep track of non-ores
			{
				array_push($recent_depth, $fullbreaks_item["y"]);
				if(count($recent_depth)>10){array_shift($recent_depth);}
			}
			
			// First block is an ore
			if(count($current_mine["breaks"]) == 0     && ($fullbreaks_item["beforeblock"]=="56" || 
					 $fullbreaks_item["beforeblock"]=="48" ||  $fullbreaks_item["beforeblock"]=="25" ||
					 $fullbreaks_item["beforeblock"]=="15" ||  $fullbreaks_item["beforeblock"]=="14") )
			{
				//echo "First Block: [".$current_mine["breaks"][0]["beforeblock"]."]";
				$current_mine["stats"]["first_block_ore"]=true;
				$current_mine["stats"]["total_ores"]++;
				//echo "FIRST BLOCK ORE<BR>";
				
				echo "--New cluster detected [".count($current_mine["clusters"])."]";
				array_push($current_mine["ores"],$fullbreaks_item);
				
				$current_cluster["ore_begin"] = count($current_mine["breaks"]);
				$current_cluster["ore_length"] = 1;
				$current_cluster["ore_type"] = $fullbreaks_item["beforeblock"];
				$postbreak_checking = true; $postbreak_check_count = 0;
				$blocks_since_ore = 0;
				$inside_cluster = true;
			}
			
			// 
			if( $blocks_since_ore == 10 && $current_mine["stats"]["total_ores"]>0 )
			{
				//echo "Adding recent breaks to previous cluster history.<BR>";
				$prev_cluster['nearby_after'] = $recent_depth;
				
				//echo "CLUSTER INFO [".count($current_mine["clusters"])."]: "; print_r($prev_cluster); echo "<BR>";
			}
		
			// Current mine is not brand new, has 1 or more breaks
			if(count($current_mine["breaks"]) > 0)
			{	
				$adjacent = false; $distance = 0;
				// Check current blocks distance from all previous breaks in current mine
				foreach($current_mine["breaks"] as $block_index => $block_compare)
				{
					$distance = max( pow($fullbreaks_item["x"] - $block_compare["x"] , 2),
									 pow($fullbreaks_item["y"] - $block_compare["y"] , 2),
									 pow($fullbreaks_item["z"] - $block_compare["z"] , 2) );
					//echo "Dist: [ ". (float) $distance . " ] <br>";
					//echo " | [".$fullbreaks_item["x"]."]>[".pow($fullbreaks_item["x"] - $block_compare["x"] , 2)."]<[".$block_compare["x"]."] ,";
					//echo   " [".$fullbreaks_item["y"]."]>[".pow($fullbreaks_item["y"] - $block_compare["y"] , 2)."]<[".$block_compare["y"]."] ,";
					//echo   " [".$fullbreaks_item["z"]."]>[".pow($fullbreaks_item["z"] - $block_compare["z"] , 2)."]<[".$block_compare["z"]."]<br>";
	
	
					if($distance <= $setting_mine_max_distance) // New break is part of current mine
					{
						$adjacent = true;
						break;
					}
				}
			}
			else // Current mine has 0 breaks, empty set
			{
				//echo "FIRST BREAK IN SET...<BR>";
				
				$current_mine["stats"]["total_volume"]++;
				$current_mine["stats"]["adjusted_volume"]++;
								
				
				// Check current blocks distance from previous block to determine if they are far enough to form new mine
				$adjacent = false; $distance = 0;
				$distance = max( pow($fullbreaks_item["x"] - $prev_break["x"] , 2),
								 pow($fullbreaks_item["y"] - $prev_break["y"] , 2),
								 pow($fullbreaks_item["z"] - $prev_break["z"] , 2) );
				//echo "Dist: [ ". (float) $distance . " ] <br>";
				//echo " | [".$fullbreaks_item["x"]."]>[".sqrt(pow($fullbreaks_item["x"] - $prev_break["x"] , 2))."]<[".$prev_break["x"]."] ,";
				//echo   " [".$fullbreaks_item["y"]."]>[".sqrt(pow($fullbreaks_item["y"] - $prev_break["y"] , 2))."]<[".$prev_break["y"]."] ,";
				//echo   " [".$fullbreaks_item["z"]."]>[".sqrt(pow($fullbreaks_item["z"] - $prev_break["z"] , 2))."]<[".$prev_break["z"]."]<br>";
				
				$inside_cluster = false;
				$blocks_since_ore = 0;

				if($distance <= $setting_mine_max_distance)
				{
					$adjacent = true;
				}	
			}
			
			// New break is part of existing mine
			if(count($current_mine["breaks"]) > 0 && $adjacent)
			{
				$current_mine["stats"]["total_volume"]++;
				//if($current_mine["stats"]["first_block_ore"]){echo "@";}
				
				// Adjusted volume ignores initial blocks if first block was ore
				if(!$current_mine["stats"]["first_block_ore"] || count($current_mine["breaks"])-1>$setting_ignorefirstore_before)
					{$current_mine["stats"]["adjusted_volume"]++;}
				
				// New break is an ore
				if($fullbreaks_item["beforeblock"]=="56" || $fullbreaks_item["beforeblock"]=="48" || 
				   $fullbreaks_item["beforeblock"]=="15" || $fullbreaks_item["beforeblock"]=="14" || $fullbreaks_item["beforeblock"]=="25")
				{
					$current_mine["stats"]["total_ores"]++;
					if(!$current_mine["stats"]["first_block_ore"] || count($current_mine["breaks"])-1>$setting_ignorefirstore_before)
						{$current_mine["stats"]["adjusted_ores"]++;}
					
					array_push($current_mine["ores"],$fullbreaks_item);
					$postbreak_checking = true; $postbreak_check_count = 0;
					
					// New cluster
					if(!$inside_cluster)
					{
						echo "--New cluster detected [".count($current_mine["clusters"])."]";
						$current_cluster["ore_begin"] = count($current_mine["breaks"]);
						$current_cluster["ore_length"] = 1;
						$current_cluster["ore_type"] = $fullbreaks_item["beforeblock"];
						
						$current_cluster["nearby_before"]=$recent_depth;
					}
					else // Existing cluster
					{
						//echo "(Ore Length + ".($blocks_since_ore + 1).")";
						$current_cluster["ore_length"] += $blocks_since_ore + 1; // Add current ore to count, plus additional recent non-ores
						if($current_cluster["ore_length"] > 1)
						{
							$current_mine["stats"]["postbreak_possible"] -= $blocks_since_ore-1;
							$current_mine["stats"]["postbreak_total"] -= $blocks_since_ore-1;
							//echo "(PB: [".$current_mine["stats"]["postbreak_possible"]."][".$current_mine["stats"]["postbreak_total"]."][".$blocks_since_ore."])";
						}
					}

					$inside_cluster = true;
					$blocks_since_ore = 0;
				} 
				else // New break is not an ore
				{ 
					$current_mine["stats"]["total_notores"]++;
					if(!$current_mine["stats"]["first_block_ore"] || count($current_mine["breaks"])-1>$setting_ignorefirstore_before)
						{$current_mine["stats"]["adjusted_notores"]++;}
					
					// Check to see if user continued to mine after finding the last ore
					if($postbreak_checking)
					{
						$current_mine["stats"]["postbreak_possible"]++; $postbreak_check_count++;
						$ore_distance_ok = false;
						foreach(array_slice($current_mine["ores"],-2 ) as $block_index => $block_compare)
						{
							$distance = sqrt( max(	pow($fullbreaks_item["x"] - $block_compare["x"] , 2),
													pow($fullbreaks_item["y"] - $block_compare["y"] , 2),
													pow($fullbreaks_item["z"] - $block_compare["z"] , 2) ) );
							//echo "Dist[$block_index]: [ ". (float) $distance . " ] <br>";
							//echo " | [".$fullbreaks_item["x"]."]>[".sqrt(pow($fullbreaks_item["x"] - $block_compare["x"] , 2))."]<[".$block_compare["x"]."] ,";
							//echo   " [".$fullbreaks_item["y"]."]>[".sqrt(pow($fullbreaks_item["y"] - $block_compare["y"] , 2))."]<[".$block_compare["y"]."] ,";
							//echo   " [".$fullbreaks_item["z"]."]>[".sqrt(pow($fullbreaks_item["z"] - $block_compare["z"] , 2))."]<[".$block_compare["z"]."]<br>";
			
			
							if($distance <= $setting_postbreak_check) // New break is near recent ore
							{
								$current_mine["stats"]["postbreak_total"]++;
								//echo "Postbreak OK([".$current_mine["stats"]["postbreak_possible"]."][".$current_mine["stats"]["postbreak_total"]."][".$blocks_since_ore."]) @ $distance ";
								$ore_distance_ok = true;
								break;
							}
							//else{ echo "x"; }
						}
						if(!$ore_distance_ok)
						{
							//echo "Postbreak MISSED ([".$current_mine["stats"]["postbreak_possible"]."][".$current_mine["stats"]["postbreak_total"]."][".$blocks_since_ore."]) @ $distance ";
						}
					}
					if($postbreak_check_count >= $setting_postbreak_check){ $postbreak_checking = false; $postbreak_check_count = 0;}
					
					// Check for end of cluster
					if($inside_cluster)
					{
						if($blocks_since_ore >= 5) // Current cluster has ended (earlier)
						{
							$inside_cluster = false;
							
							//echo "Cluster core end detected... ";
							//echo "CLUSTER INFO [".count($current_mine["clusters"])."]: "; print_r($current_cluster); echo "<BR>";

							array_push($current_mine["clusters"], $current_cluster);
							$prev_cluster = &$current_mine["clusters"][count($current_mine["clusters"])-1];
							$current_cluster = $init_cluster;
						}

					}
					$blocks_since_ore++;
				}

			}
			
			
			$current_mine["stats"]["depth_total"] += $fullbreaks_item["y"];
			
		
			if($adjacent || count($current_mine["breaks"]) == 0) // New break is part of current mine
			{
				//echo "=";
				array_unshift($current_mine["breaks"], $fullbreaks_item);
				
			}
			else // New break is part of NEW mine
			{
				if($postbreak_checking && !$current_mine["stats"]["first_block_ore"])
				{
					while($postbreak_check_count < $setting_postbreak_check){ $current_mine["stats"]["postbreak_possible"]++;	$postbreak_check_count++; }
				}
				$postbreak_checking = false; $postbreak_check_count = 0;
				
				echo "--End Of Mine Detected [".count($Mine_Array)."] ... (Volume: ".$current_mine["stats"]["total_volume"]." , Adjusted: ".$current_mine["stats"]["adjusted_volume"].")<BR>";
				//echo "(PB Possible: ".$current_mine["stats"]["postbreak_possible"].", PB Actual: ".$current_mine["stats"]["postbreak_total"].")";
				if($current_mine["stats"]["first_block_ore"]==true)
				{
					//echo "*";
					
				}
													
				if(count($current_mine["clusters"])>0)
				{
						echo " -- Mine OK (".count($current_mine["clusters"])." clusters), including in results.<BR>";
						$current_mine["breaks"]=array_reverse($current_mine["breaks"]);
						array_push($Mine_Array, $current_mine);
						
						$current_mine = $init_current_mine; $prev_break = $init_prev_break; $current_cluster = $init_cluster; $recent_depth = array();
						array_push($FullBreaksArray,$fullbreaks_item);
				}
				else
				{
					if($current_mine["stats"]["first_block_ore"])
					{
						echo " -- Mine OK (firstblock ore), including in results.<BR>";
						$current_mine["breaks"]=array_reverse($current_mine["breaks"]);
						array_push($Mine_Array, $current_mine);
						
						$current_mine = $init_current_mine; $prev_break = $init_prev_break; $current_cluster = $init_cluster; $recent_depth = array();
						array_push($FullBreaksArray,$fullbreaks_item);
					}
					else
					{
						echo " -- Mine BAD (0 clusters). Omitting from results.<BR>";
						$current_mine = $init_current_mine; $prev_break = $init_prev_break; $current_cluster = $init_cluster; $recent_depth = array();
						array_push($FullBreaksArray,$fullbreaks_item);
					}
				}

				// If mine is large enough, keep it.  If it's too small, omit from final results
				/*
				if(count($current_mine["breaks"])>10)
				{
					if($current_mine["stats"]["adjusted_volume"]>10)
					{
						echo " -- Mine volume OK, including in results.";
						$current_mine["breaks"]=array_reverse($current_mine["breaks"]);
						array_push($Mine_Array, $current_mine);
						
						$current_mine = $init_current_mine; $prev_break = $init_prev_break; $current_cluster = $init_cluster; $recent_depth = array();
						array_push($FullBreaksArray,$fullbreaks_item);
					}
					else
					{
						echo " -- Adjusted volume too small. Omitting from results.";
						$current_mine = $init_current_mine; $prev_break = $init_prev_break; $current_cluster = $init_cluster; $recent_depth = array();
						array_push($FullBreaksArray,$fullbreaks_item);
					}
				}
				else
				{
					if($current_mine["stats"]["first_block_ore"])
					{
						echo " -- Mine OK (firstblock ore), including in results.";
						$current_mine["breaks"]=array_reverse($current_mine["breaks"]);
						array_push($Mine_Array, $current_mine);
						
						$current_mine = $init_current_mine; $prev_break = $init_prev_break; $current_cluster = $init_cluster; $recent_depth = array();
						array_push($FullBreaksArray,$fullbreaks_item);
					}
					else
					{
						echo " -- Mine volume too small. Omitting from results.";
						$current_mine = $init_current_mine; $prev_break = $init_prev_break; $current_cluster = $init_cluster; $recent_depth = array();
						array_push($FullBreaksArray,$fullbreaks_item);
					}
				}*/
		
				$prev_break = $fullbreaks_item;
			}
			
			// When last block is reached, finalize last mine
			if(count($FullBreaksArray) == 0)
			{
				$fullbreaks_done = true;
				echo "LAST BLOCK!<BR>";
				
				// Check to see if player is still mining (last break is within past 10 minutes)
				// TODO

				//if($adjacent){ array_unshift($current_mine["breaks"], $fullbreaks_item); }
				
				/*
				if($current_mine["breaks"][0]["date"] < $datetime_hour_ago)
				{
					echo "--New Mine Detected ... (Volume: ".$current_mine["stats"]["total_volume"]." , Adjusted: ".$current_mine["stats"]["adjusted_volume"].") <BR><BR>";
					$current_mine["breaks"]=array_reverse($current_mine["breaks"]);
					array_push($Mine_Array, $current_mine);
				}
				else
				{
					echo "--New Mine Detected ... <BR>";
				}*/
			}
			
			//echo "}".$fullbreaks_item["beforeblock"]."> <BR>";
			if(count($current_mine["breaks"]) != 0)
			{
				//echo "FULL BREAK SIZE: [" . count($FullBreaksArray)  . "] | MINE BREAK SIZE: [". count($current_mine["breaks"]) ."][".$current_mine["stats"]["total_volume"]."]";
				//echo "_ores[".$current_mine["stats"]["total_ores"]."]";
				//echo "<BR>";
			}
			//else { echo "<BR><BR>==========================<BR>"; }
		}
		
		echo "<BR><BR>[--------------------ADD TO DATABASE--------------------]<BR>";
	
		// Insert new mines/clusters into database
		// ------------------------------
		
		//echo "FULL MINE_ARRAY: "; print_r($Mine_Array); echo "<BR>";
		
		//$sql_newmine  = "START TRANSACTION; ";
		echo "New Mines Found: ". count($Mine_Array). " <BR>";
		if(count($Mine_Array) > 0)
			{ echo "Adding mines and clusters to player's records...<br>"; }
		else
			{ echo "WARNING: User does not have enough mining data. No changes will be made.<br>"; }
		
		foreach($Mine_Array as $mine_index => $mine_item)
		{
			//echo "<BR><BR>==========================<BR>";
			foreach($mine_item["clusters"] as $cluster_index => &$cluster_item)
			{
				//echo "CLUSTER INFO [$cluster_index]: "; print_r($cluster_item); echo "<BR><BR>";
								
				if(count($cluster_item["nearby_before"])>0)
				{
					$cluster_item["slope_before"] = number_format( ( ($cluster_item["nearby_before"][0] - $cluster_item["nearby_before"][ count($cluster_item["nearby_before"])-1 ] ) / (0 - count($cluster_item["nearby_before"])-1 ) ), 2);
					$cluster_item["spread_before"] = max($cluster_item["nearby_before"]) - min($cluster_item["nearby_before"]);
				} else { $cluster_item["slope_before"] = NULL; $cluster_item["spread_before"] = NULL; }
				
				if(count($cluster_item["nearby_after"])>0)
				{
					$cluster_item["slope_after"] = number_format( ( ($cluster_item["nearby_after"][0] - $cluster_item["nearby_after"][ count($cluster_item["nearby_after"])-1 ] ) / (0 - count($cluster_item["nearby_after"])-1 ) ), 2);
					$cluster_item["spread_after"] = max($cluster_item["nearby_after"]) - min($cluster_item["nearby_after"]);
				} else { $cluster_item["slope_after"] = NULL; $cluster_item["spread_after"] = NULL; }
				
				//echo "RAW CLUSTER INFO [$mine_index]>>[$cluster_index]: "; print_r($cluster_item); echo "<BR><BR>";

				$cluster_item["nearby_before"] = count($cluster_item["nearby_before"]);
				$cluster_item["nearby_after"] = count($cluster_item["nearby_after"]);
				
				//echo "SQL CLUSTER INFO [$mine_index]>>[$cluster_index]: "; print_r($cluster_item); echo "<BR><BR>";
				
				
			}
			
			$mine_item["stats"]["last_break_date"] = $mine_item["breaks"][0]["date"];
			$mine_item["stats"]["depth_avg"] = $mine_item["stats"]["depth_total"] / $mine_item["stats"]["total_volume"];
			
			//echo "STATS[$mine_index]: "; print_r($mine_item["stats"]); echo "<BR><BR>";

			/*
			echo "...Adding Mine ($mine_index of ".count($Mine_Array).")...";
			$sql_newmine = "INSERT INTO `x-mines` ";
			$sql_newmine .= " 	( `playerid`, `worldid`, `volume`, `first_block_ore`, `last_break_date`, `diamond_ratio`, `lapis_ratio`, `iron_ratio`, `gold_ratio`, `mossy_ratio`) ";
			$sql_newmine .= " VALUES ";
			$sql_newmine .= " 	( $playerid, 1, ".$mine_item["stats"]["total_volume"].", ".$mine_item["stats"]["first_block_ore"].", '".$mine_item["stats"]["last_break_date"]."', 2.5, 1.2, 5.7, 4.3, 0 ); ";
			$sql_newmine .= "  ";
			if( count($mine_item["clusters"]) > 0)
			{
				$sql_newmine .= "INSERT INTO `minecraft`.`x-clusters` ";
				$sql_newmine .= " 	( `mineid`, `playerid`, `worldid`, `ore_begin`, `ore_length`, `slope_before`, `slope_after`, `spread_before`, `spread_after`) ";
				$sql_newmine .= " VALUES ";
				foreach($mine_item["clusters"] as $cluster_index => $cluster_item)
				{
					$sql_newmine .= " ( last_insert_id(), $playerid, 1, ".$cluster_item["ore_begin"].", ".$cluster_item["ore_length"].", ";
					$sql_newmine .= $cluster_item["slope_before"].", ".$cluster_item["slope_after"].", ".$cluster_item["spread_before"].", ".$cluster_item["spread_after"]." ) ";
	
					if($cluster_index < count($mine_item["clusters"])-1 ){ $sql_newmine .= " , "; } // Join clusters together with a single query
				}
			}
			$sql_newmine .= ";";
			*/
			
			//echo "...Adding Mine ($mine_index of ".count($Mine_Array).")...";
			$sql_newmine = "INSERT INTO `x-mines` ";
			$sql_newmine .= " 	( `playerid`, `worldid`, `volume`, `first_block_ore`, `last_break_date`, `diamond_ratio`, `lapis_ratio`, `iron_ratio`, `gold_ratio`, `mossy_ratio`) ";
			$sql_newmine .= " VALUES ";
			$sql_newmine .= sprintf(" 	( %s, %s, %s, %s, %s, %s, %s, %s, %s, %s); ",
									GetSQLValueString($playerid,"int"),
									GetSQLValueString(1,"int"),
									GetSQLValueString($mine_item["stats"]["total_volume"],"int"),
									GetSQLValueString($mine_item["stats"]["first_block_ore"],"defined",1,0),
									GetSQLValueString($mine_item["stats"]["last_break_date"],"date"),
									GetSQLValueString(NULL,"int"),
									GetSQLValueString(NULL,"int"),
									GetSQLValueString(NULL,"int"),
									GetSQLValueString(NULL,"int"),
									GetSQLValueString(NULL,"int") );
			$sql_newmine .= "  ";
			//echo "SQL NEWMINE[$mine_index]: <BR> $sql_newmine <BR><BR>";
			$res_newbreaks = mysql_query($sql_newmine) or die("SQL_QUERY[newmine - $mine_index]: " . $sql_newmine . "<BR> " . mysql_error() . "<BR>");
			//if(!mysql_errno()){ echo "DONE!<BR>"; }
			
			if( count($mine_item["clusters"]) > 0)
			{
				$sql_newmine = "INSERT INTO `x-clusters` ";
				$sql_newmine .= " 	( `mineid`, `playerid`, `worldid`, `ore_begin`, `ore_length`, `slope_before`, `slope_after`, `spread_before`, `spread_after`) ";
				$sql_newmine .= " VALUES ";
				foreach($mine_item["clusters"] as $cluster_index => $cluster_item)
				{
					$sql_newmine .= sprintf(" 	( %s, %s, %s, %s, %s, %s, %s, %s, %s) ",
						"last_insert_id()",
						GetSQLValueString($playerid,"int"),
						GetSQLValueString(1,"int"),
						GetSQLValueString($cluster_item["ore_begin"],"int"),
						GetSQLValueString($cluster_item["ore_length"],"int"),
						GetSQLValueString($cluster_item["slope_before"],"double"),
						GetSQLValueString($cluster_item["slope_after"],"double"),
						GetSQLValueString($cluster_item["spread_before"],"double"),
						GetSQLValueString($cluster_item["spread_after"],"double") );
					if($cluster_index < count($mine_item["clusters"])-1 ){ $sql_newmine .= " , "; } // Join clusters together with a single query
				}
			}

			//echo "SQL NEWCLUSTERS[$mine_index]: <BR> $sql_newmine <BR><BR>";
			$res_newbreaks = mysql_query($sql_newmine) or die("SQL_QUERY[newmine - $mine_index]: " . $sql_newmine . "<BR> " . mysql_error() . "<BR>");
			//if(!mysql_errno()){ echo "DONE!<BR>"; }
			
			// Overall stats
			$final_postbreak_total += $mine_item["stats"]["postbreak_total"];
			$final_postbreak_possible += $mine_item["stats"]["postbreak_possible"];
			
			if(count($Mine_Array) > 100 && ($mine_index % (count($Mine_Array) / 10) ) == 0 )
			{
				//echo "[=====]";
				echo "Processing... " . round( ($mine_index / (count($Mine_Array) / 10)) * 10 ) . "%...<BR>";
				//echo "[$mine_index]";
			}
		}
		
		echo "DONE! Database update complete! <BR>";
		
		/*
		$sql_newmine .= " COMMIT; ";
		echo "SQL NEWMINE: <BR> $sql_newmine <BR><BR>";
		$res_newbreaks = mysql_query($sql_newmine);
		if(mysql_errno())
		{
			die("SQL_QUERY[newmine]: " . $sql_newmine . "<BR> " . mysql_error() . "<BR>");
		}else { echo "DONE!<BR>"; }
		*/
		
		echo "<BR><BR>[------------------------STATS------------------------]<BR>";
		if($final_postbreak_possible != 0)
			{	$final_postbreak_ratio = $final_postbreak_total / $final_postbreak_possible; } 
			else { $final_postbreak_ratio = "0"; }
		echo "POSTBREAK RATIO: " . number_format($final_postbreak_ratio * 100, 2) . "%<BR>";

	}
	
}

function UpdatePlayerMinesStats($playerid)
{
	if(!PlayerExists($playerid))
	{
		echo "ERROR: The player you specified does not exist. (Player ID: '$playerid')<BR>";
	} else
	{
		$sql_updatemine  = "INSERT INTO `x-stats` ";
		$sql_updatemine .= " (`playerid`, `volume`, `first_block_ore`, `slope_before_neg`, `slope_before_pos`, `slope_after_neg`, `slope_after_pos`, `spread_before`, `spread_after`, `ore_begin`, `ore_length`)  ";
		$sql_updatemine .= " SELECT p.playerid, c6.volume, first_block_ore, slope_before_neg, slope_before_pos, slope_after_neg, slope_after_pos, spread_before, spread_after, ore_begin, ore_length FROM `lb-players` AS p ";
		$sql_updatemine .= " INNER JOIN ";
		$sql_updatemine .= " ( ";
		$sql_updatemine .= "     SELECT AVG(slope_before) AS slope_before_neg FROM `x-clusters` ";
		$sql_updatemine .= "     WHERE slope_before < 0 ";
		$sql_updatemine .= "         AND `playerid` = $playerid ";
		$sql_updatemine .= " ) AS c1 ";
		$sql_updatemine .= " INNER JOIN ";
		$sql_updatemine .= " ( ";
		$sql_updatemine .= "     SELECT AVG(slope_before) AS slope_before_pos FROM `x-clusters` ";
		$sql_updatemine .= "     WHERE slope_before >= 0 ";
		$sql_updatemine .= "         AND `playerid` = $playerid ";
		$sql_updatemine .= " ) AS c2 ";
		$sql_updatemine .= " INNER JOIN ";
		$sql_updatemine .= " ( ";
		$sql_updatemine .= "     SELECT AVG(slope_after) AS slope_after_neg FROM `x-clusters` ";
		$sql_updatemine .= "     WHERE slope_after < 0 ";
		$sql_updatemine .= "         AND `playerid` = $playerid ";
		$sql_updatemine .= " ) AS c3 ";
		$sql_updatemine .= " INNER JOIN ";
		$sql_updatemine .= " ( ";
		$sql_updatemine .= "     SELECT AVG(slope_after) AS slope_after_pos FROM `x-clusters` ";
		$sql_updatemine .= "     WHERE slope_after >= 0 ";
		$sql_updatemine .= "         AND `playerid` = $playerid ";
		$sql_updatemine .= " ) AS c4 ";
		$sql_updatemine .= " INNER JOIN ";
		$sql_updatemine .= " ( ";
		$sql_updatemine .= "     SELECT AVG(spread_before) AS spread_before, AVG(spread_after) AS spread_after, AVG(ore_begin) AS ore_begin, AVG(ore_length) AS ore_length FROM `x-clusters` ";
		$sql_updatemine .= "     WHERE `playerid` = $playerid ";
		$sql_updatemine .= " ) AS c5 ";
		$sql_updatemine .= " INNER JOIN ";
		$sql_updatemine .= " ( ";
		$sql_updatemine .= "     SELECT AVG(volume) AS volume, AVG(first_block_ore) * 100 AS first_block_ore FROM `x-mines` ";
		$sql_updatemine .= "     WHERE `playerid` = $playerid ";
		$sql_updatemine .= " ) AS c6 ";
		$sql_updatemine .= " WHERE `playerid` = $playerid ";
		$sql_updatemine .= " ON DUPLICATE KEY UPDATE ";
		$sql_updatemine .= " `volume`=c6.volume, `first_block_ore`=TRUNCATE(c6.first_block_ore,2), `slope_before_neg`=TRUNCATE(c1.slope_before_neg,2), `slope_before_pos`=TRUNCATE(c2.slope_before_pos,2), `slope_after_pos`=TRUNCATE(c3.slope_after_neg,2), `slope_after_pos`=TRUNCATE(c4.slope_after_pos,2), `spread_before`=c5.spread_before, `spread_after`=c5.spread_after, `ore_begin`=c5.ore_begin, `ore_length`=c5.ore_length ";
				
		$res_updatemine = mysql_query($sql_updatemine);
		if(mysql_errno())
		{
			die("SQL_QUERY[updatemine]: " . $sql_updatemine . "<BR> " . mysql_error() . "<BR>");
		}else { echo "DONE!<BR>"; }
	}
}

function GetLatestMine($playerid, $worldid)
{
	// Get ID of players whose names partially match the search parameter
	$sql_GetLatestMine  = "SELECT MAX(`last_break_date`) as latest_mine FROM `x-mines`";
	$sql_GetLatestMine .= " WHERE `playerid` = $playerid AND `worldid` = $worldid";
	//echo "SQL QUERY: <BR>" . $sql_getWorlds . "<BR>";
	$res_GetLatestMine = mysql_query($sql_GetLatestMine) or die("GetLatestMine: " . mysql_error());
	while(($LatestMine_result[] = mysql_fetch_assoc($res_GetLatestMine)) || array_pop($LatestMine_result));

	$latest_mine_date = $LatestMine_result[0]["latest_mine"];
	
	if($latest_mine_date == NULL)
	{
		$latest_mine_date = "2010-01-01 00:00:00";
	}	
	
	//echo "LATESTMINE ARRAY: "; print_r($LatestMine_result); echo "<BR>";
	//echo "LATEST MINE FOUND: [$latest_mine_date]<BR>";
	
	return $latest_mine_date;
}

function GetAllMines($playerid, $worldid)
{
	// Get ID of players whose names partially match the search parameter
	$sql_GetLatestMine  = "SELECT MAX(`last_break_date`) as latest_mine FROM `x-mines`";
	$sql_GetLatestMine .= " WHERE `playerid` = $playerid AND `worldid` = $worldid";
	//echo "SQL QUERY: <BR>" . $sql_getWorlds . "<BR>";
	$res_GetLatestMine = mysql_query($sql_GetLatestMine) or die("GetLatestMine: " . mysql_error());
	while(($LatestMine_result[] = mysql_fetch_assoc($res_GetLatestMine)) || array_pop($LatestMine_result));

	$latest_mine_date = $LatestMine_result[0]["latest_mine"];
	
	if($latest_mine_date == NULL)
	{
		$latest_mine_date = "2010-01-01 00:00:00";
	}	
	
	//echo "LATESTMINE ARRAY: "; print_r($LatestMine_result); echo "<BR>";
	//echo "LATEST MINE FOUND: [$latest_mine_date]<BR>";
	
	return $latest_mine_date;
}

function AutoFlagWatching()
{
	$error .= "WARNING: AutoWatch flagging feature not yet implemented.<BR>";
	
}


function TakeSnapshots()
{
	$error .= "WARNING: Snapshots feature not yet implemented.<BR>";
	
	
}

function FixInput_Bool($input_item)
{
	if($input_item === true){ return true; } else
	if($input_item === false){ return false; }
	switch( trim( strtolower($input_item) ) )
	{
		case "yes":
		case "on":
		case "true":
		case "1":
			return true;
			break;
		case "no":
		case "off":
		case "false":
		case "0":
		default:
			return false;
			break;
	}

}

function Clear_XStats()
{
	echo "Removing all statistics from X-Ray Stats table...<BR>";
	echo "Resetting world tables so all previous statistics may be reprocessed...<BR>";
	// Remove all data from xray statistics table.
	$sql_Clear_XStats  = "TRUNCATE `x-stats` ";

		//echo "SQL QUERY: <BR>" . $sql_Clear_XStats . "<BR>";
		$res_Clear_XStats = mysql_query($sql_Clear_XStats) or die("Clear_XStats1: " . mysql_error());
	
	// Reset world process dates so that users statistics can be reprocessed.
	$sql_Clear_XStats  = "UPDATE `x-worlds` SET `last_date_processed` = ('2012-01-01 00:00:00') ";

		//echo "SQL QUERY: <BR>" . $sql_Clear_XStats . "<BR>";
		$res_Clear_XStats = mysql_query($sql_Clear_XStats) or die("Clear_XStats2: " . mysql_error());

}



?>