<?php require_once('inc/core_xdetector.php'); ?>
<?php 
Global_Init();
$player_list = Get_Player_ListAll(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>
<body>
<?php 
foreach($player_list as $player_index => $player_item)
{
?>
<table width="100%" border="0">
  <tr>
    <th scope="row"><?php echo $player_item['playerid']; ?></th>
  </tr>
  <tr>
    <th scope="row"><?php Add_Player_Mines($player_item['playerid']); ?></th>
  </tr>
</table>
<?php } ?>



?>
</body>
</html>