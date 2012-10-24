<?php
//////////////////////////////////////////////////////////////
// THIS FUNCTION IS DEPRECATED
//
// REASON: The execution time is too high on some systems
//
// REPLACED BY: inc/live/count_dirtyusers_byworld_bydate.php
//////////////////////////////////////////////////////////////
require_once('../core_xdetector.php');

Global_Init();

Use_DB("source");

foreach($GLOBALS['worlds'] as $world_index => $world_item)
{
	$dirty_users_count[$world_index]['world_id'] = $world_item["worldid"];
	$dirty_users_count[$world_index]['player_count'] = Get_Count_DirtyUsers_ByWorld($world_index);
	//echo Get_Count_DirtyUsers_ByWorld($world_index);
}
echo json_encode($dirty_users_count);
?>