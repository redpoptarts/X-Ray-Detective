<?php
require_once('../core_xdetector.php');
require_once('../auth_xray.php');

Global_Init();
//$auth = Do_Auth();

Use_DB("source");

//return json_encode( json_decode($_POST['player_list'] ));

/*
$_GET['world_id']=2;
$_GET['player_list']="570,618,1337,1386,1594,1613,1677,1715,1777,2827";
//$_GET['page_num']=1;
$_GET['start_date']="2012-01-01 00:00:00";
*/

/*
// TODO: This causes a timeout
$_GET['world_id']=2;
$_GET['page_num']=2;
$_GET['start_date']="2012-02-19 00:00:00";
*/


//$_POST=$_GET;

//$_POST['player_list'] = json_decode($_POST['player_list']);

//if($_SESSION["auth_is_valid"] && !$_SESSION['first_setup'])
//{
	foreach($GLOBALS['worlds'] as $world_index => $world_item)
	{
		if($_POST['world_id'] == $world_item['worldid'])
		{
			//ob_start();
			$response = Add_NewBreaks_ByWorld_PlayerList_DateRange($_POST['world_id'], $_POST['player_list'], $_POST['start_date']);
			//ob_end_clean();
			
			//echo $response;
			echo json_encode($response);
			//echo json_encode($_GET['page_num']);
		}
	}
//}

?>