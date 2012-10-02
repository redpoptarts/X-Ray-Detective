<?php
require_once('../core_xdetector.php');
require_once('../auth_xray.php');

Global_Init();

$auth = Do_Auth();

if($_SESSION["auth_is_valid"] && !$_SESSION['first_setup'])
{
	ob_start();
	$num_records = Add_NewBreaks();
	ob_end_clean();
	
	echo json_encode($num_records);
}
/*
Use_DB("source");
$sql_Get_All_Players  = "SELECT `playername` FROM ".$GLOBALS['db']['table_players']." WHERE `playername` LIKE '%".$_GET['term']."%' LIMIT 10";
//echo "SQL QUERY: <BR>" . $sql_Get_All_Players . "<BR>";
$res_Get_All_Players = @mysql_query($sql_Get_All_Players) or die( json_encode(array()) );

$playername_array = array();
if( mysql_num_rows($res_Get_All_Players) > 0 )
{
    while(($Player_result[] = mysql_fetch_assoc($res_Get_All_Players)) || array_pop($Player_result));
    foreach($Player_result as $playername_item)
	{
		array_push($playername_array, $playername_item["playername"]);
	}
	
	//echo "Playernames: "; print_r($playername_array); echo "<BR>";
}
else
{
    echo json_encode(array());
}	

echo json_encode($playername_array);
*/

?>