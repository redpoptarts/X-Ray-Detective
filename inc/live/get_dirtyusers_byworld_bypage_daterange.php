<?php
require_once('../core_xdetector.php');

Global_Init();

Use_DB("source");

/*
$_GET['world_id']=2;
$_GET['page_num']=1;
$_GET['start_date']="2012-01-01 00:00:00";

$_POST=$_GET;*/

foreach($GLOBALS['worlds'] as $world_index => $world_item)
{
	if($_POST['world_id'] == $world_item['worldid'])
	{
		//$dirty_users_return[$world_index]['world_id'] = $world_item['worldid'];
		$dirty_users_return[$world_index]['player_list'] = Get_List_DirtyUsers_ByWorld_ByPage_DateRange( $_POST['world_id'], $_POST['page_num'], $_POST['start_date']);
		//echo Get_Count_DirtyUsers_ByWorld($world_index);
	}
}
echo json_encode($dirty_users_return);
?>