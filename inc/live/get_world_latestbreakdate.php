<?php
require_once('../core_xdetector.php');

Global_Init();

Use_DB("source");

if(!isset($_POST['world_id'])){ $_POST['world_id'] = "ALL"; }

$world_latestbreakdate = Get_World_LatestBreakDate($_POST['world_id']);



echo json_encode($world_latestbreakdate);
?>