<?php
require_once('../core_xdetector.php');

Global_Init();

Use_DB("source");

/*
$_GET['world_id']=2;
$_GET['start_date']="2012-01-01 00:00:00";

$_POST=$_GET;*/

//echo "Count Data ~~ " . $_POST['world_id'] ." : ". $_POST['start_date'];


foreach($GLOBALS['worlds'] as $world_index => $world_item)
{
	if($_POST['world_id'] == "ALL" || $_POST['world_id'] == $world_item['worldid'])
	{
		$dirty_users_count[$world_index]['world_id'] = $world_item['worldid'];
		$dirty_users_count[$world_index]['player_count'] = Get_Count_DirtyUsers_ByWorld($_POST['world_id'], $_POST['start_date']);
		//echo Get_Count_DirtyUsers_ByWorld($world_index);
	}
}
echo json_encode($dirty_users_count);
?>