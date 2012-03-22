<?php include_once('config/config_settings.php'); ?>
<?php include_once('config/config_database.php'); ?>
<?php require_once('src/core_xdetector.php'); ?>
<?php include_once('src/auth_xray.php'); ?>
<?php

//echo "Begin script...<br>";
if($_SESSION["auth_is_valid"])
{
//	echo "Continuing...<br>";
	@mysql_connect($db_host, $db_user, $db_pass) or die($_SERVER["REQUEST_URI"] . "Could not connect to DB host.");
	@mysql_selectdb($db_name) or die($_SERVER["REQUEST_URI"] . "Could not select DB");

	if ($command == 'xsingle')
	{
//		echo "XCHECK";
		if($_GET['xr_submit']=="Check" || $_GET['xr_submit']=="")
		{
			// Check user's totals from stats table
			$player_world_stats = GetSingleStats($player_id);
	
			foreach($player_world_stats as $pw_index => $pw_item)
			{
				
				foreach($limits as $limit_type => $limit_array)
				{
					//echo "BLOCK: "; print_r($limit_type); echo "<br>";
					//echo "ARRAY: "; print_r($limt_array); echo "<br>";
					$tempcolor = 10;
					$player_world_stats[$pw_index]["color_" . $limit_type] = -3;
					while($pw_item[$limit_type . "_ratio"] < $limits[$limit_type][$tempcolor] && $tempcolor > 0)
					{
						//echo "$limit_type >> " . $limits[$limit_type][$tempcolor] . " [" . ($tempcolor) . "]<br>";
						$tempcolor--;	
					}
					$player_world_stats[$pw_index]["color_" . $limit_type] = $tempcolor;
				}
				$player_world_stats[$pw_index]["color_max"] = 
					max(	$player_world_stats[$pw_index]["color_diamond"],
							$player_world_stats[$pw_index]["color_lapis"],
							$player_world_stats[$pw_index]["color_gold"],
							$player_world_stats[$pw_index]["color_mossy"],
							$player_world_stats[$pw_index]["color_iron"]);
			}
		}
		if($_GET['xr_submit']=="Analyze")
		{
			$command = "xanalyze"; $show_process = true;
		}
	} elseif ($command == 'xglobal')
	{
		// Check average ratios from stats table

		// Calculate a ratio based on totals
		if ($dias > 0) { $findrate["diamond"] = number_format($dias * 100 / $stones,2); } else { $findrate["diamond"] = number_format(0,4); }
		if ($mossy > 0) { $findrate["mossy"] = number_format($mossy * 100 / $stones,2); } else { $findrate["mossy"] = number_format(0,4); }
		if ($lapis > 0) { $findrate["lapis"] = number_format($lapis * 100 / $stones,2); } else { $findrate["lapis"] = number_format(0,4); }
		if ($gold > 0) { $findrate["gold"] = number_format($gold * 100 / $stones,2); } else { $findrate["gold"] = number_format(0,4); }
		if ($iron > 0) { $findrate["iron"] = number_format($iron * 100 / $stones,2); } else { $findrate["iron"] = number_format(0,4); }

		foreach($limits as $limit_type => $limit_array)
		{
			//echo "BLOCK: "; print_r($limit_type); echo "<br>";
			//echo "ARRAY: "; print_r($limt_array); echo "<br>";
			$tempcolor = 10;
			$color[$limit_type] = -3;
			while($findrate[$limit_type] < $limits[$limit_type][$tempcolor] && $tempcolor > 0)
			{
				//echo "$limit_type >> " . $limits[$limit_type][$tempcolor] . " [" . ($tempcolor) . "]<br>";
				$tempcolor--;	
			}
			$color[$limit_type] = $tempcolor;
		}
	} elseif ($command == 'xtoplist')
	{
		if($world_id==""){$world_id=1;}
		if($block_type==""){$block_type=56;}
		if($limit_results==""){$limit_results=50;}
		if($stone_threshold==""){$stone_threshold=500;}
		
		$TopArray = TopList($world_id, $limit_results, $block_type, $stone_threshold);

		foreach($limits as $limit_type => $limit_array)
		{
			//echo "BLOCK: "; print_r($limit_type); echo "<br>";
			//echo "ARRAY: "; print_r($limt_array); echo "<br>";
			$tempcolor = 10;
			$color[$limit_type] = -3;
			while($findrate[$limit_type] < $limits[$limit_type][$tempcolor] && $tempcolor > 0)
			{
				//echo "$limit_type >> " . $limits[$limit_type][$tempcolor] . " [" . ($tempcolor) . "]<br>";
				$tempcolor--;	
			}
			$color[$limit_type] = $tempcolor;
		}
	} elseif ($command == 'xscan')
	{
		$show_process = true;
	} elseif ($command == 'xupdate')
	{
		$show_process = true;
	} elseif ($command == 'xanalyze')
	{
		$show_process = true;
	} elseif ($command == 'xclear')
	{
		$show_process = true;
		$require_confirmation = true;
		$msg_confirmation = "You are about to delete all collected x-ray statistics (block counts) for all users!";
	}

}

$datetime_now = new DateTime;
$datetime_week_ago = new DateTime;
$datetime_week_ago->modify( '-14 day' );

//echo $datetime_week_ago->format( 'Y-m-d H:i:s' );

?>

<style type="text/css">
a:link {
	color: #FFF;
}
a:visited {
	color: #FFF;
}
a:hover {
	color: #CCC;
}
a:active {
	color: #CCC;
}
body {
	background-image: url(img/bg/xrd_bg.jpg);
	background-repeat: repeat-y;
	margin-left: 100px;
	margin-top: 25px;
	margin-right: 50px;
	margin-bottom: 50px;
	background-color: #000;
}
body,td,th { font-family: Tahoma, Geneva, sans-serif; }
</style>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>X-Ray Detective</title>
<link href="styles/style_weblinks_global.css" rel="stylesheet" type="text/css" />
<link href="styles/style_borders.css" rel="stylesheet" type="text/css" />
<link href="styles/style_backgrounds.css" rel="stylesheet" type="text/css" />
<link href="styles/style_xray.css" rel="stylesheet" type="text/css" />
</head>

<body>
<?php //echo "AUTHMODE: $auth_mode<BR>"; ?>
<?php if(!$_SESSION["auth_is_valid"]){ ?>
<table width="800" border="0" class="border_greybg_light_thick">
  <tr>
    <td><form id="loginform" name="loginform" method="post" action="">
      <table width="100%" border="0">
        <tr>
          <td><table width="100%" height="90" border="0" class="xray_header">
            <tr>
              <td><h1>&nbsp;</h1></td>
            </tr>
          </table></td>
        </tr>
        <tr>
          <td><table width="100%" border="0" class="border_greybg_dark_thick">
            <tr>
              <td align="right">&nbsp;</td>
            </tr>
          </table></td>
        </tr>
        <tr>
          <td><table width="100%" border="0" class="border_greybg_dark_thick">
            <tr>
              <td>&nbsp;</td>
              </tr>
            <tr>
              <td align="right"><?php if($logout_success!=""){ ?><table width="100%" border="0" class="bg_I_4 border_black_thick">
                <tr>
                  <td align="center" valign="middle"><h1 class="success"><?php echo $logout_success; ?></h1></td>
                </tr>
                </table>
                <br />
                <?php } if($login_error!=""){ ?>
                <table width="100%" border="0" class="bg_I_-3 border_black_thick">
                  <tr>
                    <td align="center" valign="middle"><h1 class="error"> <?php echo $login_error; ?></h1></td>
                  </tr>
                </table>
                <br />
                <?php } ?>
                <?php if($auth_mode == "username"){ ?>
                <?php if($totalRows_IP_Users > 0) { // Show if recordset not empty ?>
                <table width="100%" border="0">
                  <tr>
                    <td align="center" valign="middle"><h1>Please Login...</h1></td>
                    <td><table width="100%" border="0" class="border_greybg_light_thick">
                      <tr>
                        <td class="border_greybg_norm_thin"><strong>Select Your Username</strong></td>
                        </tr>
                      <tr>
                        <td><table width="100%" border="0">
                          <tr>
                            <td width="200" valign="top" nowrap="nowrap"><strong>Your Username:</strong></td>
                            <td valign="top"><select name="my_username" id="my_username">
                              <?php foreach($IP_Users_list as $ip_index => $ip_item) { ?>
                              <option value="<?php echo $ip_item['playername']; ?>"><?php echo $ip_item['playername']; ?></option>
                              <?php } ?>
                              </select></td>
                            </tr>
                          <tr>
                            <td>&nbsp;</td>
                            <td><input name="Submit" type="submit" id="Submit" value="Login" />
                              <input name="form" type="hidden" id="form" value="loginform" /></td>
                            </tr>
                          </table></td>
                        </tr>
                      </table></td>
                    </tr>
                  </table>
                <?php } else { ?>
                <table width="100%" border="0" class="bg_I_-3 border_black_thick">
                  <tr>
                    <td align="center" valign="middle"><h1 class="error">You are not authorized to view this page.</h1></td>
                  </tr>
                </table>
                <?php } } if ($auth_mode == "password"){ ?>
                <table width="100%" border="0">
                  <tr>
                    <td align="center" valign="middle"><h1>Please Login...</h1></td>
                    <td align="center" valign="middle"><table width="100%" border="0" class="border_greybg_light_thick">
                      <tr>
                        <td class="border_greybg_norm_thin"><strong>Enter Your Password</strong></td>
                        </tr>
                      <tr>
                        <td><table  border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <td nowrap="nowrap"><strong>Password</strong></td>
                            <td><input name="login_password" type="password" id="login_password" size="30" maxlength="30" /></td>
                            </tr>
                          <tr>
                            <td nowrap="nowrap">&nbsp;</td>
                            <td align="right"><input name="Submit" type="submit" id="Submit" value="Login" />
                              <input name="form" type="hidden" id="form" value="loginform" /></td>
                            </tr>
                          </table></td>
                        </tr>
                      </table></td>
                    </tr>
              </table>
                <?php } ?>
                <br /></td>
              </tr>
            <tr>
              <td align="right">&nbsp;</td>
              </tr>
            </table></td>
        </tr>
        <tr>
          <td><table width="100%" border="0" class="border_greybg_dark_thick">
            <tr>
              <td align="right">&nbsp;</td>
              </tr>
            </table></td>
        </tr>
      </table>
    </form></td>
  </tr>
</table>
<br />
<?php } if($_SESSION["auth_is_valid"] && $show_process==true){ ?>
<table width="800" border="0" class="border_greybg_light_thick">
  <tr>
    <td><table width="100%" border="0">
      <tr>
        <td><table width="100%" height="90" border="0" class="xray_header">
          <tr>
            <td><h1>&nbsp;</h1></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width="100%" border="0" class="border_greybg_dark_thick">
          <tr>
            <td>&nbsp;</td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php if($require_confirmation && $_GET['confirm']!="1"){ ?>
          <table width="100%" border="0" class="border_greybg_dark_thick">
          <tr>
            <td><table width="100%" border="0" cellpadding="25">
              <tr>
                <td><table width="100%" border="0" cellpadding="15" class="border_greybg_dark_thick">
                  <tr>
                    <td colspan="2" class="bg_I_10"><h2>WARNING:</h2></td>
                  </tr>
                  <tr>
                    <td colspan="2" class="bg_I_-3"><?php echo $msg_confirmation; ?></td>
                  </tr>
                  <tr>
                    <td align="center" class="border_greybg_norm_thick"><strong><a href="xray.php">ABORT</a></strong></td>
                    <td align="center" class="border_greybg_norm_thick"><strong><a href="<?php echo $_SERVER['REQUEST_URI'] . "&confirm=1"; ?>">PROCEED</a></strong></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
          </tr>
        </table>
          <?php } else { ?>
          <table width="100%" border="0" class="border_greybg_dark_thick">
          <tr>
            <td><h2>
              Processing...              
              
</h2></td>
          </tr>
          <tr>
            <td><?php 
					if($command == "xupdate")
					{
						if($_SESSION["auth_admin"] || $_SESSION["auth_mod"]) { AddNewBreaks(); AutoFlagWatching(); TakeSnapshots(); }
						else { $command_error .= "You do not have permission to do that.<BR>"; }
					}
					if($command == "xanalyze")
					{						
						if($_SESSION["auth_admin"] || $_SESSION["auth_mod"])
						{
							$Worlds_Array = GetWorlds();
							foreach($Worlds_Array as $world_index => $world_item)
								{ AddPlayerMines($player_id, $world_item["worldid"], $mine_settings); }
							UpdatePlayerMinesStats($player_id);
						}
						else { $command_error .= "You do not have permission to do that.<BR>"; }
					}
					if($command == "xclear")
					{
						if($_SESSION["auth_admin"]) { Clear_XStats(); }
						else { $command_error .= "You do not have permission to do that.<BR>"; }
					}
			 ?></td>
          </tr>
          <tr>
            <td><?php if($command_error!=""){ ?>
              <table width="100%" border="0" class="bg_I_-3 border_black_thick">
              <tr>
                <td align="center" valign="middle"><h1 class="error"><?php echo $command_error; ?></h1>
                  </h1></td>
              </tr>
              <tr>
                <td align="center" valign="middle">[ <a href="xray.php">Home</a> ]</td>
              </tr>
            </table>
              <?php } else { $command_success .= "Execution complete.<BR>"; } ?></td>
          </tr>
          <tr>
            <td><?php if($command_success!=""){ ?>
              <table width="100%" border="0" class="bg_I_4 border_black_thick">
              <tr>
                <td align="center" valign="middle"><h1 class="success"><?php echo $command_success; ?></h1>
                  </h1></td>
              </tr>
              <tr>
                <td align="center" valign="middle"><?php if($player_name!=""){ ?>[ <a href="xray.php?command=xsingle&player=<?php echo $player_name; ?>">Player's Stats</a> ] <?php } ?>[ <a href="xray.php">Home</a> ]</td>
              </tr>
            </table>
              <?php } ?></td>
          </tr>
        </table>
          <?php } ?></td>
      </tr>
      <tr>
        <td><table width="100%" border="0" class="border_greybg_dark_thick">
          <tr>
            <td>&nbsp;</td>
          </tr>
        </table></td>
      </tr>
    </table></td>
  </tr>
</table>
<br />
<?php } elseif($_SESSION["auth_is_valid"]) { ?>
<table width="800" border="0" class="border_greybg_light_thick">
  <tr>
    <td><table width="100%" border="0">
      <tr>
        <td><table width="100%" border="0" height="90" class="xray_header">
          <tr>
            <td><h1>&nbsp;</h1></td>
            <td align="right"><strong>Logged in as: <?php echo $_SESSION["auth_level"]; if($_SESSION["account"]["playername"]!=""){ echo "<BR>(".$_SESSION["account"]["playername"].")";} ?></strong>              <form id="logoutform" name="logoutform" method="post" action="">
                <strong>
              <input type="submit" name="Submit" id="Submit" value="Logout" />
              <input name="form" type="hidden" id="form" value="logoutform" />
                </strong>
              </form></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width="100%" border="0" class="border_greybg_norm_thick">
          <tr>
            <td><table width="100%" border="0" class="border_greybg_dark_thick">
              <tr>
                <td><h2>Tasks</h2></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><table width="100%" border="0" class="bg_black">
              <tr class="border_greybg_light_thin">
                <td><h3><strong>Users</strong></h3></td>
                <td><h3><strong>Moderators</strong></h3></td>
                <td><h3><strong>Administrators</strong></h3></td>
              </tr>
              <tr class="bg_white">
                <td><strong><a href="xray.php?command=xtoplist" style="color:#000000">Top User Statistics</a><a href="xray.php?command=xclear" style="color:#000000"></a></strong></td>
                <td><a href="xray.php?command=xupdate" style="color:#000000"><strong>Update  X-Ray Stats</strong></a></td>
                <td><a href="xray.php?command=xsettings" style="color:#000000"><strong>Change Settings</strong></a></td>
              </tr>
              <tr class="bg_white">
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr class="bg_white">
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr class="bg_white">
                <td><strong><a href="xray.php?command=xglobal&amp;player=GlobalRates" style="color:#000000">Check Global Averages</a></strong></td>
                <td>&nbsp;</td>
                <td><a href="xray.php?command=xclear" style="color:#000000"><strong>Clear X-Ray Stats</strong></a></td>
              </tr>
             </table></td>
          </tr>
          <tr>
            <td><form action="xray.php" method="post" name="XR_form" target="_self" id="XR_form">
              <table width="100%" border="0" class="border_greybg_light_thin">
                <tr>
                  <td width="14%" nowrap="nowrap"><strong>Check Player By Name
                    <input name="command" type="hidden" id="command" value="xsingle" />
                    <input name="form" type="hidden" id="form" value="XR_form" />
                  </strong></td>
                  <td width="86%" nowrap="nowrap"><input name="player" type="text" id="player" maxlength="20" />
                    <input type="submit" name="xr_submit" id="xr_submit" value="Check" />
                    <input type="submit" name="xr_submit" id="xr_submit" value="Analyze" /></td>
                </tr>
              </table>
            </form></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php if($command=="xtoplist"){ ?>
          <form id="toplist_form" name="toplist_form" method="post" action="xray.php">
            <table width="100%" border="0" class="border_greybg_norm_thick">
              <tr>
                <td><table width="100%" border="0" class="border_greybg_dark_thick">
                  <tr>
                    <td><h2>Top Ratios</h2></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td><table width="100%" border="0">
                  <tr>
                      <td><strong>Block Type</strong></td>
                      <td><select name="block_type" id="block_type">
                        <option value="56"<?php if($block_type=="56"){echo " selected";}?>>Diamonds</option>
                        <option value="25"<?php if($block_type=="25"){echo " selected";}?>>Lapis</option>
                        <option value="14"<?php if($block_type=="14"){echo " selected";}?>>Gold</option>
                        <option value="48"<?php if($block_type=="48"){echo " selected";}?>>Mossy</option>
                        <option value="15"<?php if($block_type=="15"){echo " selected";}?>>Iron</option>
                      </select>
                        <input type="submit" name="top_go" id="top_go" value="Go" />
                        <input name="form" type="hidden" id="form" value="form_toplist" />
                        <input name="command" type="hidden" id="command" value="xtoplist" /></td>
                    </tr>
                    <tr>
                      <td><strong>World</strong></td>
                      <td><select name="worldid" id="worldid">
<?php foreach($world_array as $world_key => $world_item ){ ?>
                        <option value="<?php echo $world_item["worldid"]; ?>"<?php if($world_id==$world_item["worldid"]){ echo " selected";}?>><?php echo $world_item["worldalias"]; ?></option>
<?php } ?>
                      </select>
                        <input type="submit" name="top_go" id="top_go" value="Go" /></td>
                    </tr>
                    <tr>
                      <td><strong>Stone Threshold</strong></td>
                      <td><strong><em>
                        <select name="stone_threshold" id="stone_threshold">
                            <option value="1000"<?php if($stone_threshold=="1000"){ echo " selected";}?>>1000+ Stone Broken (Most Accurate)</option>
                            <option value="750"<?php if($stone_threshold=="750"){ echo " selected";}?>>750+ Stone Broken</option>
                            <option value="500"<?php if($stone_threshold=="500"||$stone_threshold==""){ echo " selected";}?>>500+ Stone Broken (Recommended)</option>
                            <option value="200"<?php if($stone_threshold=="200"){ echo " selected";}?>>200+ Stone Broken</option>
                            <option value="100"<?php if($stone_threshold=="100"){ echo " selected";}?>>100+ Stone Broken (Least Accurate)</option>
                            <option value="0"<?php if($stone_threshold=="0"&&$stone_threshold!=""){ echo " selected";}?>>Show All</option>
                        </select>
                        <input type="submit" name="top_go" id="top_go" value="Go" />
                      </em></strong></td>
                    </tr>
                    <tr>
                      <td><strong>Number Of Results</strong></td>
                      <td><select name="limit_results" id="limit_results">
                        <option value="10"<?php if($limit_results=="10"){ echo " selected";}?>>10 Users</option>
                        <option value="25"<?php if($limit_results=="25"||$limit_results==""){ echo " selected";}?>>25 Users</option>
                        <option value="50"<?php if($limit_results=="50"){ echo " selected";}?>>50 Users</option>
                        <option value="75"<?php if($limit_results=="75"){ echo " selected";}?>>75 Users</option>
                        <option value="100"<?php if($limit_results=="100"){ echo " selected";}?>>100 Users</option>
                        <option value="250"<?php if($limit_results=="250"){ echo " selected";}?>>250 Users</option>
                        <option value="500"<?php if($limit_results=="500"){ echo " selected";}?>>500 Users</option>
                        <option value="-1"<?php if($limit_results=="-1"){ echo " selected";}?>>All Users</option>
                      </select>
                        <input type="submit" name="top_go" id="top_go" value="Go" /></td>
                    </tr>
                    <tr>
                      <td><s><strong>Hide Banned Users</strong></s></td>
                      <td><input name="hide_banned" type="checkbox" id="hide_banned" value="1" />
                        <input type="submit" name="top_go" id="top_go" value="Go" /></td>
                    </tr>
                </table></td>
              </tr>
              <tr>
                <td>
                <?php 
				//echo "TOP_ARRAY: "; print_r($TopArray); echo "<br>";
				?>
                  <table width="100%" border="0" class="bg_black">
                  <tr class="bg_white">
                    <td class="bg_AAA_x"><strong>Username</strong></td>
                    <td class="bg_AAA_x"><strong>Stones</strong></td>
                    <td class="bg_AAA_x"><strong>Info</strong></td>
                    <td colspan="2" align="center" class="bg_<?php if($limit_block=="diamond"){echo"I";}else{echo"AAA";}?>_x"><strong>Diamonds</strong></td>
                    <td colspan="2" align="center" class="bg_<?php if($limit_block=="lapis"){echo"I";}else{echo"AAA";}?>_x"><strong>Lapis</strong></td>
                    <td colspan="2" align="center" class="bg_<?php if($limit_block=="gold"){echo"I";}else{echo"AAA";}?>_x"><strong>Gold</strong></td>
                    <td colspan="2" align="center" class="bg_<?php if($limit_block=="mossy"){echo"I";}else{echo"AAA";}?>_x"><strong>Mossy</strong></td>
                    <td colspan="2" align="center" class="bg_<?php if($limit_block=="iron"){echo"I";}else{echo"AAA";}?>_x"><strong>Iron</strong></td>
                    </tr>
                  <?php foreach($TopArray as $key => $top)
				  		{
							foreach($limits as $limit_type => $limit_array)
							{
								$tempcolor = 10;
								$color[$limit_type] = -3;
								while($top[$limit_type . "_ratio"] < $limits[$limit_type][$tempcolor] && $tempcolor > 0)
								{
									//echo "<br>$limit_block >> " . $limits[$limit_block][$tempcolor] . " [" . ($tempcolor) . "]";
									$tempcolor--;	
								}
								//echo "<< <BR>";
								$color[$limit_type] = $tempcolor;
							}
							$top["firstlogin"] = date_create_from_format("Y-m-d H:i:s", $top["firstlogin"]);
?>
                  <tr class="bg_I_<?php echo $color[$limit_block];?>">
                    <td nowrap="nowrap" class="bg_I_<?php echo $color[$limit_block];?>"><a href="xray.php?command=xsingle&amp;player=<?php echo $top["playername"]; ?>"><strong><?php echo $top["playername"]; ?></strong></a></td>
                    <td nowrap="nowrap" class="bg_I_<?php echo $color[$limit_block];?>"><strong><?php echo $top["stone_count"]; ?></strong></td>
                    <td nowrap="nowrap"><span class="bg_I_<?php echo $color[$limit_block];?>&gt;&lt;strong&gt;&lt;a href=">
                      <?php if($top["firstlogin"] > $datetime_week_ago){ ?>
                      <img src="img/green.png" width="15" height="15" alt="New User" />
                      <?php } else { /*echo $top["firstlogin"];*/ } ?>
                    </span></td>
                    <td nowrap="nowrap" class="bg_<?php if($limit_block=="diamond"){echo"E";}else{echo"I";}?>_<?php echo $color["diamond"];?>"><?php if($limit_block=="diamond"){echo"<strong>";}?><?php echo $top["diamond_count"]; ?><?php if($limit_block=="diamond"){echo"</strong>";}?></td>
                    <td nowrap="nowrap" class="bg_<?php if($limit_block=="diamond"){echo"E";}else{echo"I";}?>_<?php echo $color["diamond"];?>"><?php if($limit_block=="diamond"){echo"<strong>";}?><?php echo number_format($top["diamond_ratio"], 2); ?> %<?php if($limit_block=="diamond"){echo"</strong>";}?></td>
                    <td nowrap="nowrap" class="bg_<?php if($limit_block=="lapis"){echo"E";}else{echo"I";}?>_<?php echo $color["lapis"];?>"><?php if($limit_block=="lapis"){echo"<strong>";}?><?php echo $top["lapis_count"]; ?><?php if($limit_block=="lapis"){echo"</strong>";}?></td>
                    <td nowrap="nowrap" class="bg_<?php if($limit_block=="lapis"){echo"E";}else{echo"I";}?>_<?php echo $color["lapis"];?>"><?php if($limit_block=="lapis"){echo"<strong>";}?><?php echo number_format($top["lapis_ratio"], 2); ?> %<?php if($limit_block=="lapis"){echo"</strong>";}?></td>
                    <td nowrap="nowrap" class="bg_<?php if($limit_block=="gold"){echo"E";}else{echo"I";}?>_<?php echo $color["gold"];?>"><?php if($limit_block=="gold"){echo"<strong>";}?><?php echo $top["gold_count"]; ?><?php if($limit_block=="gold"){echo"</strong>";}?></td>
                    <td nowrap="nowrap" class="bg_<?php if($limit_block=="gold"){echo"E";}else{echo"I";}?>_<?php echo $color["gold"];?>"><?php if($limit_block=="gold"){echo"<strong>";}?><?php echo number_format($top["gold_ratio"], 2); ?> %<?php if($limit_block=="gold"){echo"</strong>";}?></td>
                    <td nowrap="nowrap" class="bg_<?php if($limit_block=="mossy"){echo"E";}else{echo"I";}?>_<?php echo $color["mossy"];?>"><?php if($limit_block=="mossy"){echo"<strong>";}?><?php echo $top["mossy_count"]; ?><?php if($limit_block=="mossy"){echo"</strong>";}?></td>
                    <td nowrap="nowrap" class="bg_<?php if($limit_block=="mossy"){echo"E";}else{echo"I";}?>_<?php echo $color["mossy"];?>"><?php if($limit_block=="mossy"){echo"<strong>";}?><?php echo number_format($top["mossy_ratio"], 2); ?> %<?php if($limit_block=="mossy"){echo"</strong>";}?></td>
                    <td nowrap="nowrap" class="bg_<?php if($limit_block=="iron"){echo"E";}else{echo"I";}?>_<?php echo $color["iron"];?>"><?php if($limit_block=="iron"){echo"<strong>";}?><?php echo $top["iron_count"]; ?><?php if($limit_block=="iron"){echo"</strong>";}?></td>
                    <td nowrap="nowrap" class="bg_<?php if($limit_block=="iron"){echo"E";}else{echo"I";}?>_<?php echo $color["iron"];?>"><?php if($limit_block=="iron"){echo"<strong>";}?><?php echo number_format($top["iron_ratio"], 2); ?> %<?php if($limit_block=="iron"){echo"</strong>";}?></td>
                    </tr>
                  <?php if(!(($key+1) % 25) ){ ?>
                  <tr class="bg_white">
                    <td class="bg_AAA_x"><strong>Username</strong></td>
                    <td class="bg_AAA_x"><strong>Stones</strong></td>
                    <td class="bg_AAA_x"><strong>Info</strong></td>
                    <td colspan="2" align="center" class="bg_<?php if($limit_block=="diamond"){echo"I";}else{echo"AAA";}?>_x"><strong>Diamonds</strong></td>
                    <td colspan="2" align="center" class="bg_<?php if($limit_block=="lapis"){echo"I";}else{echo"AAA";}?>_x"><strong>Lapis</strong></td>
                    <td colspan="2" align="center" class="bg_<?php if($limit_block=="gold"){echo"I";}else{echo"AAA";}?>_x"><strong>Gold</strong></td>
                    <td colspan="2" align="center" class="bg_<?php if($limit_block=="mossy"){echo"I";}else{echo"AAA";}?>_x"><strong>Mossy</strong></td>
                    <td colspan="2" align="center" class="bg_<?php if($limit_block=="iron"){echo"I";}else{echo"AAA";}?>_x"><strong>Iron</strong></td>
                    </tr>
                  <?php } }
				  if( (($key+1) % 25) ){ ?>
                  <tr class="bg_white">
                    <td class="bg_AAA_x"><strong>Username</strong></td>
                    <td class="bg_AAA_x"><strong>Stones</strong></td>
                    <td class="bg_AAA_x"><strong>Info</strong></td>
                    <td colspan="2" align="center" class="bg_<?php if($limit_block=="diamond"){echo"I";}else{echo"AAA";}?>_x"><strong>Diamonds</strong></td>
                    <td colspan="2" align="center" class="bg_<?php if($limit_block=="lapis"){echo"I";}else{echo"AAA";}?>_x"><strong>Lapis</strong></td>
                    <td colspan="2" align="center" class="bg_<?php if($limit_block=="gold"){echo"I";}else{echo"AAA";}?>_x"><strong>Gold</strong></td>
                    <td colspan="2" align="center" class="bg_<?php if($limit_block=="mossy"){echo"I";}else{echo"AAA";}?>_x"><strong>Mossy</strong></td>
                    <td colspan="2" align="center" class="bg_<?php if($limit_block=="iron"){echo"I";}else{echo"AAA";}?>_x"><strong>Iron</strong></td>
                    </tr>
                  <?php } ?>
                </table></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
            </table>
          </form>
          <?php } ?></td>
      </tr>
      <tr>
        <td><?php if($command=="xsingle" || $command=="xglobal"){ ?><table width="100%" border="0" class="border_greybg_norm_thick">
          <tr>
            <td><table width="100%" border="0" class="border_greybg_dark_thick">
              <tr>
                <td><h2>Basic Player Stats: <font color="#FF0000"><?php echo $player; ?></font></h2></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><form action="xray.php" method="post" name="useraction_form" target="_self" id="useraction_form">
              <table width="100%" border="0">
                <tr>
                  <td valign="top"><table width="100%" border="0" class="border_greybg_light_thick">
                    <tr>
                      <td><table width="100%" border="0" class="border_greybg_dark_thick">
                        <tr>
                          <td><strong>User Status </strong></td>
                          </tr>
                        </table></td>
                      </tr>
                    <tr>
                      <td><table width="100%" border="0" class="border_greybg_norm_thick">
                        <tr>
                          <td><strong>Punishment Status</strong></td>
                          <td><select name="playerstatus" id="playerstatus">
                            <option value="0" selected="selected">Normal</option>
                            <option value="1">Warned</option>
                            <option value="2">Jailed</option>
                            <option value="3">Suspended</option>
                            <option value="4">Banned</option>
                            </select></td>
                          </tr>
                        <tr>
                          <td><strong>Watching</strong></td>
                          <td><label for="watchingplayer"></label>
                            <select name="watchingplayer" id="watchingplayer">
                              <option value="0">Hide User</option>
                              <option value="1" selected="selected">Normal</option>
                              <option value="2">Watching</option>
                            </select></td>
                          </tr>
                        <tr>
                          <td>&nbsp;</td>
                          <td><input type="submit" name="button" id="button" value="Modify" />
                            <input name="form" type="hidden" id="form" value="form_useraction" />
                            <input name="command" type="hidden" id="command" value="xmodifyuser" /></td>
                          </tr>
                        </table></td>
                      </tr>
                  </table></td>
                </tr>
              </table>
            </form></td>
          </tr>
          <tr>
            <td>
<?php foreach($player_world_stats as $pw_index => $pw_item) {?>
             <table width="100%" border="0">
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td><table width="100%" border="0" class="border_greybg_light_thick">
                  <tr>
                    <td><table width="100%" border="0" class="border_greybg_dark_thick">
                        <tr>
                          <td><strong><?php echo $player_name; ?>'s Stats for World <?php echo $pw_item["worldalias"]; ?> </strong></td>
                        </tr>
                      </table></td>
                  </tr>
                  <tr>
                    <td><table width="100%" border="0" class="bg_black">
                      <tr class="bg_white">
                        <td class="bg_AAA_x"><strong>Date</strong></td>
                        <td class="bg_AAA_x"><strong>Stones</strong></td>
                        <td colspan="2" align="center" class="bg_<?php if($limit_block=="diamond"){echo"I";}else{echo"AAA";}?>_x"><strong>Diamonds</strong></td>
                        <td colspan="2" align="center" class="bg_<?php if($limit_block=="lapis"){echo"I";}else{echo"AAA";}?>_x"><strong>Lapis</strong></td>
                        <td colspan="2" align="center" class="bg_<?php if($limit_block=="gold"){echo"I";}else{echo"AAA";}?>_x"><strong>Gold</strong></td>
                        <td colspan="2" align="center" class="bg_<?php if($limit_block=="mossy"){echo"I";}else{echo"AAA";}?>_x"><strong>Mossy</strong></td>
                        <td colspan="2" align="center" class="bg_<?php if($limit_block=="iron"){echo"I";}else{echo"AAA";}?>_x"><strong>Iron</strong></td>
                      </tr>
                      <tr class="bg_I_<?php echo $color[$limit_block];?>">
                        <td nowrap="nowrap" class="bg_E_<?php echo $pw_item["color_max"];?>">NOW</td>
                        <td nowrap="nowrap" class="bg_E_<?php echo $pw_item["color_max"];?>"><?php echo $pw_item["stone_count"];?></td>
                        <td nowrap="nowrap" class="bg_E_<?php echo $pw_item["color_diamond"];?>"><?php echo $pw_item["diamond_count"];?></td>
                        <td nowrap="nowrap" class="bg_E_<?php echo $pw_item["color_diamond"];?>"><?php echo $pw_item["diamond_ratio"];?></td>
                        <td nowrap="nowrap" class="bg_E_<?php echo $pw_item["color_lapis"];?>"><?php echo $pw_item["lapis_count"];?></td>
                        <td nowrap="nowrap" class="bg_E_<?php echo $pw_item["color_lapis"];?>"><?php echo $pw_item["lapis_ratio"];?></td>
                        <td nowrap="nowrap" class="bg_E_<?php echo $pw_item["color_gold"];?>"><?php echo $pw_item["gold_count"];?></td>
                        <td nowrap="nowrap" class="bg_E_<?php echo $pw_item["color_gold"];?>"><?php echo $pw_item["gold_ratio"];?></td>
                        <td nowrap="nowrap" class="bg_E_<?php echo $pw_item["color_mossy"];?>"><?php echo $pw_item["mossy_count"];?></td>
                        <td nowrap="nowrap" class="bg_E_<?php echo $pw_item["color_mossy"];?>"><?php echo $pw_item["mossy_ratio"];?></td>
                        <td nowrap="nowrap" class="bg_E_<?php echo $pw_item["color_iron"];?>"><?php echo $pw_item["iron_count"];?></td>
                        <td nowrap="nowrap" class="bg_E_<?php echo $pw_item["color_iron"];?>"><?php echo $pw_item["iron_ratio"];?></td>
                      </tr>
                      <?php if(count($SnapshotArray) > 0 ) { foreach($SnapshotArray as $key => $snap)
				  		{
							foreach($limits as $limit_type => $limit_array)
							{
								$tempcolor = 10;
								$color[$limit_type] = -3;
								while($snap[$limit_type . "_ratio"] < $limits[$limit_type][$tempcolor] && $tempcolor > 0)
								{
									//echo "<br>$limit_block >> " . $limits[$limit_block][$tempcolor] . " [" . ($tempcolor) . "]";
									$tempcolor--;	
								}
								//echo "<< <BR>";
								$color[$limit_type] = $tempcolor;
							}
?>
                      <tr class="bg_I_<?php echo $color[$limit_block];?>">
                        <td nowrap="nowrap" class="bg_I_<?php echo $color[$limit_block];?>&gt;&lt;strong&gt;&lt;a href="xray.php?command="xsingle&amp;player=<?php echo $top["playername"]; ?>&amp;authKey=yourpassword&quot;"><strong><?php echo $snap["datetime"]; ?></a></strong></strong></td>
                        <td nowrap="nowrap" class="bg_I_<?php echo $color[$limit_block];?>"><strong><?php echo $snap["stone_count"]; ?></strong></td>
                        <td nowrap="nowrap" class="bg_I_<?php echo $color["diamond"];?>"><strong><?php echo $snap["diamond_count"]; ?></strong></td>
                        <td nowrap="nowrap" class="bg_I_<?php echo $color["diamond"];?>"><strong><?php echo number_format($snap["diamond_ratio"], 2); ?> %</strong></td>
                        <td nowrap="nowrap" class="bg_I_<?php echo $color["lapis"];?>"><strong><?php echo $snap["lapis_count"]; ?></strong></td>
                        <td nowrap="nowrap" class="bg_I_<?php echo $color["lapis"];?>"><strong><?php echo number_format($snap["lapis_ratio"], 2); ?> %</strong></td>
                        <td nowrap="nowrap" class="bg_I_<?php echo $color["gold"];?>"><strong><?php echo $snap["gold_count"]; ?></strong></td>
                        <td nowrap="nowrap" class="bg_I_<?php echo $color["gold"];?>"><strong><?php echo number_format($snap["gold_ratio"], 2); ?> %</strong></td>
                        <td nowrap="nowrap" class="bg_I_<?php echo $color["mossy"];?>"><strong><?php echo $snap["mossy_count"]; ?></strong></td>
                        <td nowrap="nowrap" class="bg_I_<?php echo $color["mossy"];?>"><strong><?php echo number_format($snap["mossy_ratio"], 2); ?> %</strong></td>
                        <td nowrap="nowrap" class="bg_I_<?php echo $color["iron"];?>"><strong><?php echo $snap["iron_count"]; ?></strong></td>
                        <td nowrap="nowrap" class="bg_I_<?php echo $color["iron"];?>"><strong><?php echo number_format($snap["iron_ratio"], 2); ?> %</strong></td>
                      </tr>
                      <?php if(!(($key+1) % 25) ){ ?>
                      <tr class="bg_white">
                        <td class="bg_AAA_x"><strong>Date</strong></td>
                        <td class="bg_AAA_x"><strong>Stones</strong></td>
                        <td colspan="2" align="center" class="bg_<?php if($limit_block=="diamond"){echo"I";}else{echo"AAA";}?>_x"><strong>Diamonds</strong></td>
                        <td colspan="2" align="center" class="bg_<?php if($limit_block=="lapis"){echo"I";}else{echo"AAA";}?>_x"><strong>Lapis</strong></td>
                        <td colspan="2" align="center" class="bg_<?php if($limit_block=="gold"){echo"I";}else{echo"AAA";}?>_x"><strong>Gold</strong></td>
                        <td colspan="2" align="center" class="bg_<?php if($limit_block=="mossy"){echo"I";}else{echo"AAA";}?>_x"><strong>Mossy</strong></td>
                        <td colspan="2" align="center" class="bg_<?php if($limit_block=="iron"){echo"I";}else{echo"AAA";}?>_x"><strong>Iron</strong></td>
                      </tr>
                      <?php } } }
				  if( (($key+1) % 25) ){ ?>
                      <tr class="bg_white">
                        <td class="bg_AAA_x"><strong>Date</strong></td>
                        <td class="bg_AAA_x"><strong>Stones</strong></td>
                        <td colspan="2" align="center" class="bg_<?php if($limit_block=="diamond"){echo"I";}else{echo"AAA";}?>_x"><strong>Diamonds</strong></td>
                        <td colspan="2" align="center" class="bg_<?php if($limit_block=="lapis"){echo"I";}else{echo"AAA";}?>_x"><strong>Lapis</strong></td>
                        <td colspan="2" align="center" class="bg_<?php if($limit_block=="gold"){echo"I";}else{echo"AAA";}?>_x"><strong>Gold</strong></td>
                        <td colspan="2" align="center" class="bg_<?php if($limit_block=="mossy"){echo"I";}else{echo"AAA";}?>_x"><strong>Mossy</strong></td>
                        <td colspan="2" align="center" class="bg_<?php if($limit_block=="iron"){echo"I";}else{echo"AAA";}?>_x"><strong>Iron</strong></td>
                      </tr>
                      <?php } ?>
                    </table></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
            </table>
<?php } ?>
            </td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width="100%" border="0" class="border_greybg_norm_thick">
          <tr>
            <td><table width="100%" border="0" class="border_greybg_dark_thick">
              <tr>
                <td><h2>
                  Advanced Player Statistics: <font color="#FF0000"><?php echo $player; ?></font>
                  </h2></td>
                </tr>
              </table></td>
            </tr>
          <tr>
            <td><form action="" method="post" name="form_startanalysis" target="_self" id="form_startanalysis">
              <table width="100%" border="0">
                <tr>
                  <td align="center">&nbsp;</td>
                  </tr>
                <tr>
                  <td align="center" class="bg_H_-3"><p>You have not yet analyzed this players mining behavior. Would you like to do that now?</p>
                    <p>
                      <input name="form" type="hidden" id="form" value="form_analyze_mines_now" />
                      <input type="submit" name="submit" id="submit" value="Analyze Mining Behavior" />
                      <input name="command" type="hidden" id="command" value="xanalyze" />
                      <input name="player" type="hidden" id="player" value="<?php echo $player_name;?>" />
                    </p></td>
                  </tr>
                <tr>
                  <td align="center">&nbsp;</td>
                  </tr>
                </table>
              </form></td>
            </tr>
          <tr>
            <td><table width="100%" border="0">
              <tr>
                <td><table width="100%" border="0">
                  <tr>
                    <td width="11%"><img src="img/delete.png" width="15" height="15" alt="Bad Attribute" /></td>
                    <td width="89%">User's Diamond ratio is extremely high.</td>
                  </tr>
                  <tr>
                    <td><img src="img/delete.png" width="15" height="15" alt="Bad Attribute" /></td>
                    <td>User's Lapis ratio is extremely high.</td>
                  </tr>
                  <tr>
                    <td><img src="img/delete.png" width="15" height="15" alt="Bad Attribute" /></td>
                    <td>User's Gold ratio is extremely high.</td>
                  </tr>
                  <tr>
                    <td><img src="img/delete.png" width="15" height="15" alt="Bad Attribute" /></td>
                    <td>User's Mossy ratio is extremely high.</td>
                  </tr>
                  <tr>
                    <td><img src="img/delete.png" width="15" height="15" alt="Bad Attribute" /></td>
                    <td>User's Iron ratio is extremely high.</td>
                  </tr>
                  <tr>
                    <td><img src="img/add.png" width="15" height="15" alt="Good Attribute" /></td>
                    <td>User's Diamond ratio is unusually high, but this alone does not necessarily prove use of X-Ray.</td>
                  </tr>
                  <tr>
                    <td><img src="img/add.png" width="15" height="15" alt="Good Attribute" /></td>
                    <td>User's Lapis ratio is unusually high, but this alone does not necessarily prove use of X-Ray.</td>
                  </tr>
                  <tr>
                    <td><img src="img/add.png" width="15" height="15" alt="Good Attribute" /></td>
                    <td>User's Gold ratio is unusually high, but this alone does not necessarily prove use of X-Ray.</td>
                  </tr>
                  <tr>
                    <td><img src="img/add.png" width="15" height="15" alt="Good Attribute" /></td>
                    <td>User's Mossy ratio is unusually high, but this alone does not necessarily prove use of X-Ray.</td>
                  </tr>
                  <tr>
                    <td><img src="img/add.png" width="15" height="15" alt="Good Attribute" /></td>
                    <td>User's Iron ratio is unusually high, but this alone does not necessarily prove use of X-Ray.</td>
                  </tr>
                  <tr>
                    <td><img src="img/add.png" width="15" height="15" alt="Good Attribute" /></td>
                    <td>User's Diamond ratio is normal.</td>
                  </tr>
                  <tr>
                    <td><img src="img/add.png" width="15" height="15" alt="Good Attribute" /></td>
                    <td>User's Lapis ratio is normal.</td>
                  </tr>
                  <tr>
                    <td><img src="img/add.png" width="15" height="15" alt="Good Attribute" /></td>
                    <td>User's Gold ratio is normal.</td>
                  </tr>
                  <tr>
                    <td><img src="img/add.png" width="15" height="15" alt="Good Attribute" /></td>
                    <td>User's Mossy ratio is normal.</td>
                  </tr>
                  <tr>
                    <td><img src="img/add.png" width="15" height="15" alt="Good Attribute" /></td>
                    <td>User's Iron ratio is normal.</td>
                  </tr>
                  <tr>
                    <td><img src="img/delete.png" width="15" height="15" alt="Bad Attribute" /></td>
                    <td>User often  stops mining nearby after finding ores.</td>
                  </tr>
                  <tr>
                    <td><img src="img/add.png" width="15" height="15" alt="Good Attribute" /></td>
                    <td>User continues mining nearby after finding ores.</td>
                  </tr>
                  <tr>
                    <td><img src="img/delete.png" width="15" height="15" alt="Bad Attribute" /></td>
                    <td>User frequently mines only ores that are visible. This could suggest an x-ray texture pack, but could also simply indicate a preference to mine in exposed caverns.</td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><table width="100%" border="0" class="bg_black">
              <tr class="bg_white">
                <td nowrap="nowrap" class="bg_AAA_x"><strong>Volume</strong></td>
                <td nowrap="nowrap" class="bg_AAA_x"><strong>Time / Block</strong></td>
                <td nowrap="nowrap" class="bg_AAA_x"><strong>PostBreaks</strong></td>
                <td nowrap="nowrap" class="bg_AAA_x"><strong>Spread</strong></td>
                <td nowrap="nowrap" class="bg_AAA_x"><strong>Slope</strong></td>
                <td nowrap="nowrap" class="bg_AAA_x">&nbsp;</td>
                <td nowrap="nowrap" class="bg_AAA_x">&nbsp;</td>
                <td nowrap="nowrap" class="bg_AAA_x">&nbsp;</td>
                <td colspan="2" align="center" nowrap="nowrap" class="bg_<?php if($limit_block=="diamond"){echo"I";}else{echo"AAA";}?>_x"><strong>Diamonds</strong></td>
                <td colspan="2" align="center" nowrap="nowrap" class="bg_<?php if($limit_block=="lapis"){echo"I";}else{echo"AAA";}?>_x"><strong>Lapis</strong></td>
                <td colspan="2" align="center" nowrap="nowrap" class="bg_<?php if($limit_block=="gold"){echo"I";}else{echo"AAA";}?>_x"><strong>Gold</strong></td>
                <td colspan="2" align="center" nowrap="nowrap" class="bg_<?php if($limit_block=="mossy"){echo"I";}else{echo"AAA";}?>_x"><strong>Mossy</strong></td>
                <td colspan="2" align="center" nowrap="nowrap" class="bg_<?php if($limit_block=="iron"){echo"I";}else{echo"AAA";}?>_x"><strong>Iron</strong></td>
                </tr>
              <?php foreach($TopArray as $key => $top)
				  		{
							foreach($limits as $limit_type => $limit_array)
							{
								$tempcolor = 10;
								$color[$limit_type] = -3;
								while($top[$limit_type . "_ratio"] < $limits[$limit_type][$tempcolor] && $tempcolor > 0)
								{
									//echo "<br>$limit_block >> " . $limits[$limit_block][$tempcolor] . " [" . ($tempcolor) . "]";
									$tempcolor--;	
								}
								//echo "<< <BR>";
								$color[$limit_type] = $tempcolor;
							}
?>
              <tr class="bg_I_<?php echo $color[$limit_block];?>">
                <td nowrap="nowrap" class="bg_I_<?php echo $color[$limit_block];?>&gt;&lt;strong&gt;&lt;a href="xray.php?command="xsingle&amp;player=<?php echo $top["playername"]; ?>&amp;authKey=yourpassword&quot;"><strong></a></strong></strong></td>
                <td nowrap="nowrap" class="bg_I_<?php echo $color[$limit_block];?>">&nbsp;</td>
                <td nowrap="nowrap">&nbsp;</td>
                <td nowrap="nowrap">&nbsp;</td>
                <td nowrap="nowrap">&nbsp;</td>
                <td nowrap="nowrap">&nbsp;</td>
                <td nowrap="nowrap">&nbsp;</td>
                <td nowrap="nowrap">&nbsp;</td>
                <td nowrap="nowrap" class="bg_<?php if($limit_block=="diamond"){echo"E";}else{echo"I";}?>_<?php echo $color["diamond"];?>"><strong><?php echo $top["diamond_count"]; ?></strong></td>
                <td nowrap="nowrap" class="bg_<?php if($limit_block=="diamond"){echo"E";}else{echo"I";}?>_<?php echo $color["diamond"];?>"><strong><?php echo number_format($top["diamond_ratio"], 2); ?> %</strong></td>
                <td nowrap="nowrap" class="bg_<?php if($limit_block=="lapis"){echo"E";}else{echo"I";}?>_<?php echo $color["lapis"];?>"><strong><?php echo $top["lapis_count"]; ?></strong></td>
                <td nowrap="nowrap" class="bg_<?php if($limit_block=="lapis"){echo"E";}else{echo"I";}?>_<?php echo $color["lapis"];?>"><strong><?php echo number_format($top["lapis_ratio"], 2); ?> %</strong></td>
                <td nowrap="nowrap" class="bg_<?php if($limit_block=="gold"){echo"E";}else{echo"I";}?>_<?php echo $color["gold"];?>"><strong><?php echo $top["gold_count"]; ?></strong></td>
                <td nowrap="nowrap" class="bg_<?php if($limit_block=="gold"){echo"E";}else{echo"I";}?>_<?php echo $color["gold"];?>"><strong><?php echo number_format($top["gold_ratio"], 2); ?> %</strong></td>
                <td nowrap="nowrap" class="bg_<?php if($limit_block=="mossy"){echo"E";}else{echo"I";}?>_<?php echo $color["mossy"];?>"><strong><?php echo $top["mossy_count"]; ?></strong></td>
                <td nowrap="nowrap" class="bg_<?php if($limit_block=="mossy"){echo"E";}else{echo"I";}?>_<?php echo $color["mossy"];?>"><strong><?php echo number_format($top["mossy_ratio"], 2); ?> %</strong></td>
                <td nowrap="nowrap" class="bg_<?php if($limit_block=="iron"){echo"E";}else{echo"I";}?>_<?php echo $color["iron"];?>"><strong><?php echo $top["iron_count"]; ?></strong></td>
                <td nowrap="nowrap" class="bg_<?php if($limit_block=="iron"){echo"E";}else{echo"I";}?>_<?php echo $color["iron"];?>"><strong><?php echo number_format($top["iron_ratio"], 2); ?> %</strong></td>
                </tr>
              <?php if(!(($key+1) % 25) ){ ?>
              <tr class="bg_white">
                <td class="bg_AAA_x">&nbsp;</td>
                <td class="bg_AAA_x">&nbsp;</td>
                <td class="bg_AAA_x">&nbsp;</td>
                <td class="bg_AAA_x">&nbsp;</td>
                <td class="bg_AAA_x">&nbsp;</td>
                <td class="bg_AAA_x">&nbsp;</td>
                <td class="bg_AAA_x">&nbsp;</td>
                <td class="bg_AAA_x">&nbsp;</td>
                <td colspan="2" align="center" class="bg_<?php if($limit_block=="diamond"){echo"I";}else{echo"AAA";}?>_x"><strong>Diamonds</strong></td>
                <td colspan="2" align="center" class="bg_<?php if($limit_block=="lapis"){echo"I";}else{echo"AAA";}?>_x"><strong>Lapis</strong></td>
                <td colspan="2" align="center" class="bg_<?php if($limit_block=="gold"){echo"I";}else{echo"AAA";}?>_x"><strong>Gold</strong></td>
                <td colspan="2" align="center" class="bg_<?php if($limit_block=="mossy"){echo"I";}else{echo"AAA";}?>_x"><strong>Mossy</strong></td>
                <td colspan="2" align="center" class="bg_<?php if($limit_block=="iron"){echo"I";}else{echo"AAA";}?>_x"><strong>Iron</strong></td>
                </tr>
              <?php } }
				  if( (($key+1) % 25) ){ ?>
              <tr class="bg_white">
                <td class="bg_AAA_x">&nbsp;</td>
                <td class="bg_AAA_x">&nbsp;</td>
                <td class="bg_AAA_x">&nbsp;</td>
                <td class="bg_AAA_x">&nbsp;</td>
                <td class="bg_AAA_x">&nbsp;</td>
                <td class="bg_AAA_x">&nbsp;</td>
                <td class="bg_AAA_x">&nbsp;</td>
                <td class="bg_AAA_x">&nbsp;</td>
                <td colspan="2" align="center" class="bg_<?php if($limit_block=="diamond"){echo"I";}else{echo"AAA";}?>_x"><strong>Diamonds</strong></td>
                <td colspan="2" align="center" class="bg_<?php if($limit_block=="lapis"){echo"I";}else{echo"AAA";}?>_x"><strong>Lapis</strong></td>
                <td colspan="2" align="center" class="bg_<?php if($limit_block=="gold"){echo"I";}else{echo"AAA";}?>_x"><strong>Gold</strong></td>
                <td colspan="2" align="center" class="bg_<?php if($limit_block=="mossy"){echo"I";}else{echo"AAA";}?>_x"><strong>Mossy</strong></td>
                <td colspan="2" align="center" class="bg_<?php if($limit_block=="iron"){echo"I";}else{echo"AAA";}?>_x"><strong>Iron</strong></td>
                </tr>
              <?php } ?>
              </table></td>
            </tr>
          </table>
          <?php } ?></td>
      </tr>
<tr>
  <td><?php if($command=="xsingle" || $command=="xglobal"){ ?>
    <table width="100%" border="0" class="border_greybg_norm_thick">
      <tr>
        <td><table width="100%" border="0" class="border_greybg_dark_thick">
          <tr>
            <td><h2>Ratio Guide</h2></td>
            </tr>
          </table></td>
        </tr>
      <tr>
        <td><table width="100%" border="0">
          <tr>
            <td class="bg_black">&nbsp;</td>
            <td width="80" align="center" class="bg_I_0"><strong>0</strong></td>
            <td width="80" align="center" class="bg_I_1"><strong>1</strong></td>
            <td width="80" align="center" class="bg_I_2"><strong>2</strong></td>
            <td width="80" align="center" class="bg_I_3"><strong>3</strong></td>
            <td width="80" align="center" class="bg_I_4"><strong>4</strong></td>
            <td width="80" align="center" class="bg_I_5"><strong>5</strong></td>
            <td width="80" align="center" class="bg_I_6"><strong>6</strong></td>
            <td width="80" align="center" class="bg_I_7"><strong>7</strong></td>
            <td width="80" align="center" class="bg_I_8"><strong>8</strong></td>
            <td width="80" align="center" class="bg_I_9"><strong>9</strong></td>
            <td width="80" align="center" class="bg_I_10"><strong>10</strong></td>
            </tr>
          <tr>
            <td class="bg_black"><strong>Diamonds</strong></td>
            <?php
$limit_block = "diamond";
for ($col = 0; $col <= 10 ; $col++)
{ ?>
            <td class="bg_G_<?php echo $col;?><?php if($col == $color[$limit_block]){ echo " border_black_thick"; }?>"><?php echo  number_format($limits[$limit_block][$col], 2); ?></td>
            <?php
} ?>
            </tr>
          <tr>
            <td class="bg_black"><strong>Lapis</strong></td>
            <?php
$limit_block = "lapis";
for ($col = 0; $col <= 10 ; $col++)
{ ?>
            <td class="bg_G_<?php echo $col;?><?php if($col == $color[$limit_block]){ echo " border_black_thick"; }?>"><?php echo  number_format($limits[$limit_block][$col], 2); ?></td>
            <?php
} ?>
            </tr>
          <tr>
            <td class="bg_black"><strong>Gold</strong></td>
            <?php
$limit_block = "gold";
for ($col = 0; $col <= 10 ; $col++)
{ ?>
            <td class="bg_G_<?php echo $col;?><?php if($col == $color[$limit_block]){ echo " border_black_thick"; }?>"><?php echo  number_format($limits[$limit_block][$col], 2); ?></td>
            <?php
} ?>
            </tr>
          <tr>
            <td class="bg_black"><strong>Mossy</strong></td>
            <?php
$limit_block = "mossy";
for ($col = 0; $col <= 10 ; $col++)
{ ?>
            <td class="bg_G_<?php echo $col;?><?php if($col == $color[$limit_block]){ echo " border_black_thick"; }?>"><?php echo  number_format($limits[$limit_block][$col], 2); ?></td>
            <?php
} ?>
            </tr>
          <tr>
            <td class="bg_black"><strong>Iron</strong></td>
            <?php
$limit_block = "iron";
for ($col = 0; $col <= 10 ; $col++)
{ ?>
            <td class="bg_G_<?php echo $col;?><?php if($col == $color[$limit_block]){ echo " border_black_thick"; }?>"><?php echo  number_format($limits[$limit_block][$col], 2); ?></td>
            <?php
} ?>
            </tr>
          </table></td>
        </tr>
      </table>
    <?php } ?></td>
</tr>
    </table></td>
  </tr>
</table>
<br />
<p>
  <?php } ?>
</body>