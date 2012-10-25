<?php
require_once('../core_xdetector.php');
require_once('../auth_xray.php');

Global_Init();
//$auth = Do_Auth();

Use_DB("source");

/*
$_GET['world_id']=2;
$_GET['page_num']=1;
$_GET['start_date']="2012-01-01 00:00:00";
*/

/*
// TODO: This causes a timeout
$_GET['world_id']=2;
$_GET['page_num']=2;
$_GET['start_date']="2012-02-19 00:00:00";

$_POST=$_GET;
*/

//if($_SESSION["auth_is_valid"] && !$_SESSION['first_setup'])
//{
	foreach($GLOBALS['worlds'] as $world_index => $world_item)
	{
		if($_POST['world_id'] == $world_item['worldid'])
		{
			//ob_start();
			$response = Add_NewBreaks_ByWorld_ByPage_DateRange($_POST['world_id'], $_POST['page_num'], $_POST['start_date']);
			//ob_end_clean();
			
			//echo $response;
			echo json_encode($response);
			//echo json_encode($_GET['page_num']);
		}
	}
//}

?>