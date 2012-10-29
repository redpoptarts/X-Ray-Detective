<?php require_once('inc/core_xdetector.php'); ?>
<?php 
Global_Init();
$player_list = Get_Player_ListAll(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<link href="styles/style_weblinks_global.css" rel="stylesheet" type="text/css" />
<link href="styles/style_borders.css" rel="stylesheet" type="text/css" />
<link href="styles/style_backgrounds.css" rel="stylesheet" type="text/css" />
<link href="styles/style_xray.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php 
Update_Playerinfo();
foreach($player_list as $player_index => $player_item)
{
	if( $player_item['stone_count'] > 0 && ( $player_item['diamond_count'] > 5 || $player_item['gold_count'] > 5 || $player_item['iron_count'] > 20) )
	{
	
?>
<table width="800" border="0" class="borderblack_greybg_light_thick">
  <tr>
    <td align="left"><h2>User #<?php echo $player_item['playerid']; ?>...</th>
    </h2>
  </tr>
  <tr>
    <td align="left"><?php Add_Player_Mines($player_item['playerid']); ?></th>
  </tr>
</table><BR />
<?php } } 
Update_Playerinfo();
?>
</body>
</html>