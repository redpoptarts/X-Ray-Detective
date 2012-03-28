<?php
function PlayerExists($playerid)
{
	// Get ID of players whose names partially match the search parameter
	$sql_PlayerIDexists  = "SELECT `playerid` FROM `lb-players`";
	$sql_PlayerIDexists .= "    WHERE `playerid` = '$playerid'";
	//echo "SQL QUERY: <BR>" . $sql_PlayerIDexists . "<BR>";
	$res_PlayerIDexists = mysql_query($sql_PlayerIDexists) or die("PlayerIDexists: " . mysql_error());
	if( mysql_num_rows($res_PlayerIDexists) > 0 )
		{ return true; }
	else
		{ return false; }	
}

function World_IsValid($worldname)
{
	// Get ID of players whose names partially match the search parameter
	$sql_World_IsValid  = "SHOW TABLES LIKE 'lb-$worldname'";
	//echo "SQL QUERY: <BR>" . $sql_World_IsValid . "<BR>";
	$res_World_IsValid = mysql_query($sql_World_IsValid) or die("World_IsValid: " . mysql_error());
	if( mysql_num_rows($res_World_IsValid) > 0 )
		{ return true; }
	else
		{ return false; }	
}

function get_PlayerStats($playerid, $worldid)
{
	// Get ID of players whose names partially match the search parameter
	$sql_PlayerStats  = "SELECT * FROM minecraft.`x-stats`";
	$sql_PlayerStats .= "    WHERE `playerid` = $playerid AND `worldid` = $worldid";
	//echo "SQL QUERY: <BR>" . $sql_PlayerIDexists . "<BR>";
	$res_PlayerStats = mysql_query($sql_PlayerStats) or die("get_PlayerStats: " . mysql_error());
	while(($PlayerStatsArray[] = mysql_fetch_assoc($res_PlayerStats)) || array_pop($PlayerStatsArray)); 

	if( mysql_num_rows($res_PlayerStats) > 0 )
		{ return $PlayerStatsArray; }
	else
		{ return false; }
}

function AddNewBreaks()
{
	// Detect datetime of most recent break
	// This prevents omitting any breaks that occurr during this script

	$datetime_now = new DateTime;
	$sql_getdate = "";
	foreach($GLOBALS['worlds'] as $world_index => $world_item)
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

	foreach($GLOBALS['worlds'] as $world_index => $world_item)
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
				if($latest_break_date == ""){  $wdate_item["latest_break_date"] = "NEVER (No logs)"; $latest_break_date = $datetime_now->format( 'Y-m-d H:i:s' ); }
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
			$stats_sql = GetMySQL_ResultStats($resource_newbreaks);
			//echo "STATS_SQL: <BR>"; print_r($stats_sql); echo "<BR>";
			echo "-----------------------------------------------<br>";
			echo "Summary For World [".$world_item["worldalias"]."]<BR>";
			echo "-----------------------------------------------<br>";
			echo "..." . $stats_sql["records"]." Users Processed.<BR>";
			echo "..." . ($stats_sql["records"] - $stats_sql["duplicates"]) . " New Users Found.<BR>";
			echo "..." . $stats_sql["duplicates"]." Users Updated.<BR>";
			echo "-----------------------------------------------<br>";
			
			if($latest_break_date != "")
			{
				// Update World's LAST_PROCESSED_DATE to curent time
				$sql_setdate = "UPDATE `x-worlds` SET `last_date_processed`='".$latest_break_date."' WHERE `worldid`='".$world_item["worldid"]."'";
				//echo "SQL_QUERY: <br>". $sql_setdate . "<br>";
				$res_setdate = mysql_query($sql_setdate) or die("SetDate([".$world_item["worldalias"]."] => '".$latest_break_date."'): " . mysql_error() . " SQL: [$sql_setdate]");
			}
		}
	}
	UpdateTotals();
}

?>