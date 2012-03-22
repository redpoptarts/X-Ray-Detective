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

?>