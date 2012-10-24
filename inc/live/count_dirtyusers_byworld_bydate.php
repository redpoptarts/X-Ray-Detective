<?php
require_once('../core_xdetector.php');

Global_Init();

Use_DB("source");

foreach($GLOBALS['worlds'] as $world_index => $world_item)
{
	if($_POST['world_id'] == "ALL" || $_POST['world_id'] == $world_item['worldid'])
	{
		$dirty_users_count[$world_index]['world_id'] = $world_item['worldid'];
		$dirty_users_count[$world_index]['player_count'] = Get_Count_DirtyUsers_ByWorld($world_item['worldid'], $_POST['start_date']);
		//echo Get_Count_DirtyUsers_ByWorld($world_index);
	}
}
echo json_encode($dirty_users_count);
?>