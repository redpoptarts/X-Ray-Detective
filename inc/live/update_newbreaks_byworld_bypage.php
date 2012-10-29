<?php
require_once('../core_xdetector.php');
require_once('../auth_xray.php');

Global_Init();

$auth = Do_Auth();

if(!isset($_GET['world_id'])){ $_GET['world_id'] = 0;  $_GET['page_num'] = 1; }

if($_SESSION["auth_is_valid"] && !$_SESSION['first_setup'])
{
	ob_start();
	$response = Add_NewBreaks_ByWorld_ByPage($_GET['world_id'], $_GET['page_num']);
	ob_end_clean();
	
	echo $response;
	//echo json_encode($response);
	//echo json_encode($_GET['page_num']);
}

?>