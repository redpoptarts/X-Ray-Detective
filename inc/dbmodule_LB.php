<?php
function Check_Player_Exists($playerid)
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

function Get_List_DirtyUsers_ByWorld_ByPage_DateRange($world_id, $page_num, $start_date, $end_date="ADD_1_WEEK")
{
	$datetime_now = new DateTime;
	
	if($end_date=="END_OF_MONTH")
	{
		$end_date = date("Y-m-t", strtotime($start_date)) . " 00:00:00";
	}
	if($end_date=="ADD_1_WEEK")
	{
		$end_date = date_format(date_add(new DateTime($start_date), date_interval_create_from_date_string('7 days')), 'Y-m-d H:i:s'); 
	}
	
//	echo "START DATE: [".$start_date."]<BR>";
//	echo "END DATE: [".$end_date."]<BR>";
//	return;
	
	$sql_getlatest = "";
	foreach($GLOBALS['worlds'] as $world_index => $world_item)
	{
		if($world_item['worldid'] == $world_id)
		{
			$sql_getlatest .= " SELECT playerid ";
			$sql_getlatest .= " FROM ";
			$sql_getlatest .= " ( ";
			$sql_getlatest .= " 	SELECT playerid ";
			$sql_getlatest .= " 	FROM `lb-".$world_item["worldname"]."` ";
			$sql_getlatest .= " 	WHERE ";
//			$sql_getlatest .= " 	    (date > '2012-03-01 00:00:00') ";  // TODO: CHANGE THIS VALUE TO KNOWN LATEST_BREAK_DATE
			$sql_getlatest .= " 	    (date BETWEEN '".$start_date."' AND '".$end_date."') ";
			$sql_getlatest .= " 	    AND (replaced = 1 ";
			$sql_getlatest .= " 	    OR replaced = 15 ";
			$sql_getlatest .= " 	    OR replaced = 14 ";
			$sql_getlatest .= " 	    OR replaced = 56 ";
			$sql_getlatest .= " 	    OR replaced = 25 ";
			$sql_getlatest .= " 	    OR replaced = 48 ";
			$sql_getlatest .= " 	    AND type = 0) ";
			$sql_getlatest .= "  ";
			$sql_getlatest .= " 	 GROUP BY playerid ";
			$sql_getlatest .= " LIMIT 10 OFFSET ". ($page_num - 1) * 10 ." ";
		}
	}

	$return_updated = 0;

//	echo "SQL_QUERY: <br>". $sql_getlatest . "<br>";
	$res_getlatest = mysql_query($sql_getlatest) or die("World-DirtyUsers: " . mysql_error());
	while(($DirtyUsersArray[] = mysql_fetch_assoc($res_getlatest)) || array_pop($DirtyUsersArray));
	//echo "DIRTY_USERS_ARRAY: <BR>"; print_r($DirtyUsersArray); echo "<BR>";
	
	return $DirtyUsersArray[0]['player_count'];
}

/////////////////////////////////////////////////////////////////////
// Deprecated -- Use: Get_List_DirtyUsers_ByWorld_ByPage_DateRange
/////////////////////////////////////////////////////////////////////
function Get_Count_DirtyUsers_ByWorld($world_id, $start_date, $end_date="ADD_1_WEEK")
{
	$datetime_now = new DateTime;
	
	if($end_date=="END_OF_MONTH")
	{
		$end_date = date("Y-m-t", strtotime($start_date)) . " 00:00:00";
	}
	if($end_date=="ADD_1_WEEK")
	{
		$end_date = date_format(date_add(new DateTime($start_date), date_interval_create_from_date_string('7 days')), 'Y-m-d H:i:s'); 
	}
	
//	echo "START DATE: [".$start_date."]<BR>";
//	echo "END DATE: [".$end_date."]<BR>";
//	return;
	
	$sql_getlatest = "";
	foreach($GLOBALS['worlds'] as $world_index => $world_item)
	{
		if($world_item['worldid'] == $world_id)
		{
			$sql_getlatest .= " SELECT count(playerid) AS player_count ";
			$sql_getlatest .= " FROM ";
			$sql_getlatest .= " ( ";
			$sql_getlatest .= " 	SELECT playerid ";
			$sql_getlatest .= " 	FROM `lb-".$world_item["worldname"]."` ";
			$sql_getlatest .= " 	WHERE ";
//			$sql_getlatest .= " 	    (date > '2012-03-01 00:00:00') ";  // TODO: CHANGE THIS VALUE TO KNOWN LATEST_BREAK_DATE
			$sql_getlatest .= " 	    (date BETWEEN '".$start_date."' AND '".$end_date."') ";
			$sql_getlatest .= " 	    AND (replaced = 1 ";
			$sql_getlatest .= " 	    OR replaced = 15 ";
			$sql_getlatest .= " 	    OR replaced = 14 ";
			$sql_getlatest .= " 	    OR replaced = 56 ";
			$sql_getlatest .= " 	    OR replaced = 25 ";
			$sql_getlatest .= " 	    OR replaced = 48 ";
			$sql_getlatest .= " 	    AND type = 0) ";
			$sql_getlatest .= "  ";
			$sql_getlatest .= " 	 GROUP BY playerid ";
			$sql_getlatest .= " ) AS world_dirty_users ";
		}
	}

	$return_updated = 0;

//	echo "SQL_QUERY: <br>". $sql_getlatest . "<br>";
	$res_getlatest = mysql_query($sql_getlatest) or die("World-DirtyUsers: " . mysql_error());
	while(($DirtyUsersArray[] = mysql_fetch_assoc($res_getlatest)) || array_pop($DirtyUsersArray));
	//echo "DIRTY_USERS_ARRAY: <BR>"; print_r($DirtyUsersArray); echo "<BR>";
	
	return $DirtyUsersArray[0]['player_count'];
}

/////////////////////////////////////////////////////////////////////
// Deprecated -- Use: Get_List_DirtyUsers_ByWorld_ByPage_DateRange
/////////////////////////////////////////////////////////////////////
function Get_List_DirtyUsers_ByWorld_ByPage($world_id, $page_num)
{
	$datetime_now = new DateTime;
	$sql_getlatest = "";
	foreach($GLOBALS['worlds'] as $world_index => $world_item)
	{
		if($world_item['worldid'] == $world_id)
		{
			$sql_getlatest  = " SELECT playerid ";
			$sql_getlatest .= " 	FROM `lb-".$world_item["worldname"]."` ";
			$sql_getlatest .= " 	WHERE ";
			$sql_getlatest .= " 	    (date > '2012-02-15 00:00:00') ";  // TODO: CHANGE THIS VALUE TO KNOWN LATEST_BREAK_DATE
			$sql_getlatest .= " 	    AND (replaced = 1 ";
			$sql_getlatest .= " 	    OR replaced = 15 ";
			$sql_getlatest .= " 	    OR replaced = 14 ";
			$sql_getlatest .= " 	    OR replaced = 56 ";
			$sql_getlatest .= " 	    OR replaced = 25 ";
			$sql_getlatest .= " 	    OR replaced = 48 ";
			$sql_getlatest .= " 	    AND type = 0) ";
			$sql_getlatest .= "  ";
			$sql_getlatest .= " GROUP BY playerid ";
			$sql_getlatest .= " LIMIT 10 OFFSET ". ($page_num - 1) * 10 ." ";
		}
	}

	$return_updated = 0;

	//echo "SQL_QUERY: <br>". $sql_getlatest . "<br>";
	$res_getlatest = mysql_query($sql_getlatest) or die("World-DirtyUsers: " . mysql_error());
	while(($DirtyUsersArray[] = mysql_fetch_assoc($res_getlatest)) || array_pop($DirtyUsersArray));
	
	$func_strip_playerid_array = function($input)
	{
		return $input['playerid'];
	};
	
	
	
	$DirtyUsers_list = array_map($func_strip_playerid_array, $DirtyUsersArray);
	//echo "DIRTY_USERS_LIST: <BR>"; print_r($DirtyUsers_list); echo "<BR>";
	
	return $DirtyUsers_list;
}

function Add_NewBreaks_ByWorld_ByPage($world_id, $page_num)
{
	$datetime_now = new DateTime;
	$return_updated = 0;
	
	foreach($GLOBALS['worlds'] as $world_index => $world_item)
	{
		if($world_item['worldid'] == $world_id)
		{
			$dirtyusers_array = Get_List_DirtyUsers_ByWorld_ByPage($world_id, $page_num);
			$dirtyusers_list = implode(",",$dirtyusers_array);
			
			if(count($dirtyusers_array) > 0)
			{
				Use_DB("xray");
				$sql_newbreaks  = "INSERT INTO `x-stats` ";
				$sql_newbreaks .= " (`playerid`, `worldid`, `diamond_count`, `gold_count`, `lapis_count`, `mossy_count`, `iron_count`, `stone_count`) ";
				$sql_newbreaks .= " SELECT p.playerid, '".$world_item["worldid"]."', IFNULL(diamond_info.cnt,0) AS diamond_count, IFNULL(gold_info.cnt,0) AS gold_count, ";
				$sql_newbreaks .= " 	IFNULL(lapis_info.cnt,0) AS lapis_count, IFNULL(mossy_info.cnt,0) AS mossy_count, IFNUlL(iron_info.cnt,0) AS iron_count, IFNULL(stone_info.cnt,0) AS stone_count";
				$sql_newbreaks .= " FROM `lb-players`";
				$sql_newbreaks .= " AS p";
				$sql_newbreaks .= " INNER JOIN (SELECT playerid, count(playerid) AS cnt FROM `lb-".$world_item["worldname"]."` ";
				$sql_newbreaks .= " 	WHERE ((`playerid` IN (".$dirtyusers_list.")) AND replaced = 1 ";
				$sql_newbreaks .= " 		AND type = 0 AND y <= 50) ";
				$sql_newbreaks .= " 	GROUP BY playerid) AS stone_info ON p.playerid = stone_info.playerid";
				$sql_newbreaks .= " LEFT JOIN (SELECT playerid, count(playerid) AS cnt FROM `lb-".$world_item["worldname"]."` ";
				$sql_newbreaks .= " 	WHERE ((`playerid` IN (".$dirtyusers_list.")) AND replaced = 56 ";
				$sql_newbreaks .= " 		AND type = 0 AND y <= 50) ";
				$sql_newbreaks .= " 	GROUP BY playerid) AS diamond_info ON p.playerid = diamond_info.playerid";
				$sql_newbreaks .= " LEFT JOIN (SELECT playerid, count(playerid) AS cnt FROM `lb-".$world_item["worldname"]."` ";
				$sql_newbreaks .= " 	WHERE ((`playerid` IN (".$dirtyusers_list.")) AND replaced = 14 ";
				$sql_newbreaks .= " 		AND type = 0 AND y <= 50) ";
				$sql_newbreaks .= " 	GROUP BY playerid) AS gold_info ON p.playerid = gold_info.playerid";
				$sql_newbreaks .= " LEFT JOIN (SELECT playerid, count(playerid) AS cnt FROM `lb-".$world_item["worldname"]."` ";
				$sql_newbreaks .= " 	WHERE ((`playerid` IN (".$dirtyusers_list.")) AND replaced = 15 ";
				$sql_newbreaks .= " 		AND type = 0 AND y <= 50) ";
				$sql_newbreaks .= " 	GROUP BY playerid) AS iron_info ON p.playerid = iron_info.playerid";
				$sql_newbreaks .= " LEFT JOIN (SELECT playerid, count(playerid) AS cnt FROM `lb-".$world_item["worldname"]."` ";
				$sql_newbreaks .= " 	WHERE ((`playerid` IN (".$dirtyusers_list.")) AND replaced = 48 ";
				$sql_newbreaks .= " 		AND type = 0 AND y <= 50) ";
				$sql_newbreaks .= " 	GROUP BY playerid) AS mossy_info ON p.playerid = mossy_info.playerid";
				$sql_newbreaks .= " LEFT JOIN (SELECT playerid, count(playerid) AS cnt FROM `lb-".$world_item["worldname"]."` ";
				$sql_newbreaks .= " 	WHERE ((`playerid` IN (".$dirtyusers_list.")) AND replaced = 21 ";
				$sql_newbreaks .= " 		AND type = 0 AND y <= 50) ";
				$sql_newbreaks .= " 	GROUP BY playerid) AS lapis_info ON p.playerid = lapis_info.playerid";
				$sql_newbreaks .= " GROUP BY p.playerid";
				$sql_newbreaks .= " ON DUPLICATE KEY UPDATE `diamond_count`=`diamond_count`+VALUES(diamond_count), `gold_count`=`gold_count`+VALUES(gold_count),";
				$sql_newbreaks .= " 	 `lapis_count`=`lapis_count`+VALUES(lapis_count), `iron_count`=`iron_count`+VALUES(iron_count), `stone_count`=`stone_count`+VALUES(stone_count)";
				$res_newbreaks = mysql_query($sql_newbreaks);
				if(mysql_errno())
				{
					die("SQL_QUERY[newbreaks]: " . $sql_newbreaks . "<BR> " . mysql_error() . "<BR>");
				}
				//else{ echo "DONE!<BR>"; }
				
		
				//while(($BreaksArray[] = mysql_fetch_assoc($res_newbreaks)) || array_pop($BreaksArray)); 
				$stats_sql = GetMySQL_ResultStats();
				//echo "STATS_SQL: <BR>"; print_r($stats_sql); echo "<BR>";
				//echo "-----------------------------------------------<br>";
				//echo "Summary For World [".$world_item["worldalias"]."]<BR>";
				//echo "-----------------------------------------------<br>";
				//echo "..." . $stats_sql["records"]." Users Processed.<BR>";
				//echo "..." . ($stats_sql["records"] - $stats_sql["duplicates"]) . " New Users Found.<BR>";
				//echo "..." . $stats_sql["duplicates"]." Users Updated.<BR>";
				//echo "-----------------------------------------------<br>";
				$return_updated = $stats_sql["records"];
			}
			
		}
		

	}
	Update_Stats_RatioTotals();
	return $return_updated;
//	return count($dirtyusers_array);
}

function Get_World_LatestBreakDate($world_id="ALL")
{
	$sql_getdate = "";
	foreach($GLOBALS['worlds'] as $world_index => $world_item)
	{
		if($world_id == "ALL" || $world_item['worldid'] == $world_id)
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
	}
	
	//echo "SQL_QUERY: <br>". $sql_getdate . "<br>";
	$res_getdate = mysql_query($sql_getdate) or die("World-LatestBreakDate: " . mysql_error());
	while(($LatestDateArray[] = mysql_fetch_assoc($res_getdate)) || array_pop($LatestDateArray));
	//echo "LATEST_DATE_ARRAY: <BR>"; print_r($LatestDateArray); echo "<BR>";	

	foreach($LatestDateArray as $world_index => &$world_item)
	{
		foreach($GLOBALS['worlds'] as $gworld_index => $gworld_item)
		{
			if($world_item['worldid'] == $gworld_item['worldid'])
			{
				$world_item['last_date_processed'] = $gworld_item['last_date_processed'];
			}
		}
	}

	return $LatestDateArray;
}


// Deprecated function
function Add_NewBreaks()
{
	// Detect datetime of most recent break
	// This prevents omitting any breaks that occurr during this script

	$datetime_now = new DateTime;
	$return_updated = 0;

	$LatestDateArray = Get_World_LatestBreakDate();

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
		}
		if($latest_break_date == $world_item["last_date_processed"])
		{
			echo "...There is no new information to process in this world. Skipping scan.<BR>";
		}
		else if($found_break) {
			// Get ALL new breaks after latest_date_processed
			echo "...Beginning User Scan, Please Be Patient...";
			Use_DB("xray");
			$sql_newbreaks  = "INSERT INTO `x-stats` ";
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
			$stats_sql = GetMySQL_ResultStats();
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
			
			$return_updated += $stats_sql["records"];
		}
	}
	Update_Stats_RatioTotals();
	return $return_updated;
}

?>