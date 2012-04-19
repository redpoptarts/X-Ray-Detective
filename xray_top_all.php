<?php require_once('inc/core_xdetector.php'); ?>
<?php include_once('inc/auth_xray.php'); ?>
<?php

//echo "Global Init...<BR>";
Global_Init();
//echo "Global Init Complete...<BR>";
$auth = Do_Auth();

//if(array_key_exists('command', $_POST)){ $_GET = $_POST; }
if(array_key_exists('Submit', $_POST)){ $_GET = $_POST; }
if(array_key_exists('command', $_POST)){ $_GET = $_POST; }
$command = array_key_exists('command', $_GET) ? $_GET['command'] : "";
$command_error = ""; $command_success = "";

//echo "Begin script...<br>";
if($_SESSION["auth_is_valid"] && !$_SESSION['first_setup'])
{
	//echo "Continuing...<br>";
	@mysql_connect($db['x_host'], $db['x_user'], $db['x_pass']) or die($_SERVER["REQUEST_URI"] . "Could not connect to XRAY DB host [".$db['x_host']."].");
	@mysql_selectdb($db['x_base']) or die($_SERVER["REQUEST_URI"] . "Could not select XRAY DB [".$db['x_base']."]");


	$sortby_column_name = array_key_exists('sortby', $_GET) ? $_GET['sortby'] : "diamond_ratio";
	
	$block_type = array_key_exists('block_type', $_GET) ? $_GET['block_type'] : 56;
	$stone_threshold = array_key_exists('stone_threshold', $_GET) ? $_GET['stone_threshold'] : 500;
	$limit_results = array_key_exists('limit_results', $_GET) ? $_GET['limit_results'] : 100;
	$player_name = array_key_exists('player', $_GET) ? $_GET['player'] : NULL;
	$player_id = Get_Player_IDByName($player_name);
	$show_process = false;
	$require_confirmation = false;
	$_GET['confirm'] = array_key_exists('confirm', $_GET) ? $_GET['confirm'] : NULL;
	
	/*
	switch($block_type){
		case 56: $sortby_column_name = "diamond_ratio"; break;
		case 25: $sortby_column_name = "lapis_ratio"; break;
		case 14: $sortby_column_name = "gold_ratio"; break;
		case 48: $sortby_column_name = "moss_ratioy"; break;
		case 15: $sortby_column_name = "iron_ratio"; break;
		default: $sortby_column_name = "invalid"; break;
	} */
	//echo "LIMIT BLOCK: $sortby_column_name<BR>";
	//echo "WORLD ID: $world_id<BR>";
	//echo "WORLD NAME: $world_name<BR>";
	//echo "WORLD ALIAS: $world_alias<BR>";
	
	/*
	echo "ARGUMENTS [GET] : ----<br>";
	print_r($_GET); echo "<br>";
	echo "---------------<br>";
	echo "ARGUMENTS [POST] : ----<br>";
	print_r($_POST); echo "<br>";
	echo "---------------<br>";
	*/


	$colorbins["diamond_ratio"] = array_fill(0, 10, 0); $colorbins["lapis_ratio"] = array_fill(0, 10, 0); $colorbins["gold_ratio"] = array_fill(0, 10, 0); $colorbins["mossy_ratio"] = array_fill(0, 10, 0); $colorbins["iron_ratio"] = array_fill(0, 10, 0);
	
	// Here are the sensitivity colorbins for each block type.
	// 3 is the LOW value (GREEN)
	// 6 is the MID value (YELLOW)
	// 9 is the HIGH value (RED)
	//
	// All other color values will be created for you automatically.
	//
	if($command == "xsingle" || $command == "xtoplist")
	{
		/////////////////////////////////////////[   ]///////////[    ]//////////[   ]/////
		$colorbins["diamond_ratio"] = array(0 => 0,	3 => "0.5", 	6 => "1.25",	9 => "2");
		$colorbins["lapis_ratio"] =   array(0 => 0,	3 => "1",		6 => "2",   	9 => "3");
		$colorbins["gold_ratio"] =    array(0 => 0, 3 => "2.5",		6 => "4", 		9 => "6");
		$colorbins["mossy_ratio"] =   array(0 => 0,	3 => "5",   	6 => "10",		9 => "15");
		$colorbins["iron_ratio"] =    array(0 => 0,	3 => "15",  	6 => "20",		9 => "30");
		/////////////////////////////////////////[   ]///////////[    ]//////////[   ]/////	
		$colorbins["first_block_ore"] =array(0 =>  0,	3 =>  "0.20", 	6 =>  "0.40",	9 =>  "0.60");
		$colorbins["slope_before_neg"] =array(0 => "-0.17",	3 => "-0.20", 	6 => "-0.25",	9 => "-0.30");
		$colorbins["slope_before_pos"] =array(0 =>  "0.17",	3 =>  "0.20", 	6 =>  "0.25",	9 =>  "0.30");
		$colorbins["slope_after_neg"] =array(0 => "-0.17",	3 => "-0.20", 	6 => "-0.25",	9 => "-0.30");
		$colorbins["slope_after_pos"] =array(0 =>  "0.17",	3 =>  "0.20", 	6 =>  "0.25",	9 =>  "0.30");
		$colorbins["spread_before"] =	array(0 => 0, 		3 => "1",		6 => "2.1", 		9 => "4");
	}
	/////////////////////////////////////////[   ]///////////[    ]//////////[   ]/////
	
	//echo "LIMITS::<br>"; print_r($colorbins); echo "<br><br>";
	
	foreach($colorbins as $column_name => $bins)
	{
		//echo "BLOCK TYPE: $sortby_column_name <br>";
		$colorbins[$column_name][1] = $colorbins[$column_name][3] * 0.33;
		$colorbins[$column_name][2] = $colorbins[$column_name][3] * 0.66;
		$colorbins[$column_name][4] = $colorbins[$column_name][3] + ($colorbins[$column_name][6] - $colorbins[$column_name][3]) * 0.33;
		$colorbins[$column_name][5] = $colorbins[$column_name][3] + ($colorbins[$column_name][6] - $colorbins[$column_name][3]) * 0.66;
		$colorbins[$column_name][7] = $colorbins[$column_name][6] + ($colorbins[$column_name][9] - $colorbins[$column_name][6]) * 0.33;
		$colorbins[$column_name][8] = $colorbins[$column_name][6] + ($colorbins[$column_name][9] - $colorbins[$column_name][6]) * 0.66;
		$colorbins[$column_name][10] = $colorbins[$column_name][9] + ($colorbins[$column_name][9] - $colorbins[$column_name][6]) * 1.33;
		asort($colorbins[$column_name]);
		//echo "[" . $column_name . "]<br>"; print_r($colorbins[$column_name]); echo "<br>";
	}

	if ($command == 'xsingle')
	{
	
		
		$_GET['xr_submit'] = array_key_exists('xr_submit', $_GET) ? $_GET['xr_submit'] : NULL;
//		echo "XCHECK";
		if($_GET['xr_submit']=="Check" || $_GET['xr_submit']=="")
		{

		}
		if($_GET['xr_submit']=="Analyze")
		{
			$command = "xanalyze"; $show_process = true;
		}
	}
	elseif ($command == 'xglobal')
	{

	}
	elseif ($command == 'xtoplist')
	{
		$world_id = array_key_exists('worldid', $_GET) ? $_GET["worldid"] : $GLOBALS['worlds'][0]["worldid"];
		
		foreach($GLOBALS['worlds'] as $world_key => $world_item )
		{
			if($world_id==$world_item["worldid"]){ $world_name = $world_item["worldname"]; $world_alias = $world_item["worldalias"];}
		}
		
		if($world_id==""){$world_id=1;}
		if($block_type==""){$block_type=56;}
		if($limit_results==""){$limit_results=50;}
		if($stone_threshold==""){$stone_threshold=500;}
		

		
		Use_DB("xray");
		$sql_PlayerStats  = "
				SELECT
					c.playerid,
					p.playername,
					x.stone_count,
					x.diamond_count,
					x.gold_count,
					format(x.diamond_ratio,2) AS diamond_ratio,
					format(x.gold_ratio,2) AS gold_ratio,
					format(
						SUM(
								CASE c.ore_begin
								WHEN 1 THEN 1
								ELSE 0
								END
							) / COUNT(c.playerid), 2)
						AS first_block_ore,
					SUM(ore_length) AS total_ores,
					COUNT(c.playerid) AS total_clusters,
					#format(AVG(ABS(slope_before)),2) AS slope_before,
					#format(AVG(ABS(slope_after)),2) AS slope_after,
					#format(AVG(spread_before),2) AS spread_before,
					#format( AVG(spread_after),2) AS spread_after,
					avg_slope_before_pos AS slope_before_pos,
					avg_slope_before_neg AS slope_before_neg,
					format( (count_slope_before_neg / (count_slope_before_pos + count_slope_before_neg)), 2) AS slope_before_preference,
					avg_slope_after_pos AS slope_after_pos,
					avg_slope_after_neg AS slope_after_neg,
					format( (count_slope_after_neg / (count_slope_after_pos + count_slope_after_neg)), 2) AS slope_after_preference,
					count_slope_before_pos,
					count_slope_before_neg    
				FROM `x-clusters` AS c
				
				LEFT JOIN
				(
					SELECT * FROM `lb-players`
				) AS p ON p.playerid = c.playerid
				
				LEFT JOIN
				(
					SELECT
						playerid,
						format(AVG(slope_before),2) AS avg_slope_before_pos,
						count(playerid) AS count_slope_before_pos
					FROM `x-clusters`
					WHERE slope_before >= 0
					GROUP BY playerid
				) AS s_b_pos ON p.playerid = s_b_pos.playerid
				
				LEFT JOIN
				(
					SELECT
						playerid,
						format(AVG(slope_before),2) AS avg_slope_before_neg,
						count(playerid) AS count_slope_before_neg
					FROM `x-clusters`
					WHERE slope_before < 0
					GROUP BY playerid
				) AS s_b_neg ON p.playerid = s_b_neg.playerid
				
				LEFT JOIN
				(
					SELECT
						playerid,
						format(AVG(slope_after),2) AS avg_slope_after_pos,
						count(playerid) AS count_slope_after_pos
					FROM `x-clusters`
					WHERE slope_after >= 0
					GROUP BY playerid
				) AS s_a_pos ON p.playerid = s_a_pos.playerid
				
				LEFT JOIN
				(
					SELECT
						playerid,
						format(AVG(slope_after),2) AS avg_slope_after_neg,
						count(playerid) AS count_slope_after_neg
					FROM `x-clusters`
					WHERE slope_after < 0
					GROUP BY playerid
				) AS s_a_neg ON p.playerid = s_a_neg.playerid
				
				LEFT JOIN
				(
					SELECT playerid, SUM(stone_count) AS stone_count, SUM(diamond_count) AS diamond_count, AVG(diamond_ratio) AS diamond_ratio, SUM(gold_count) AS gold_count, AVG(gold_ratio) AS gold_ratio
					FROM `x-stats`
					#WHERE diamond_count > 20
					GROUP BY playerid
				) AS x ON x.playerid = c.playerid
				
				#WHERE x.stone_count > 500
				WHERE x.diamond_count > 1 OR x.gold_count > 1
				
				GROUP BY playerid
				
				HAVING total_clusters > 1
				
				ORDER BY ".$sortby_column_name ." DESC";
		//echo "SQL QUERY: <BR>" . $sql_PlayerIDexists . "<BR>";
		$res_PlayerStats = mysql_query($sql_PlayerStats) or die("Get_Player_Stats_ByWorld: " . mysql_error());
		while(($PlayerStatsArray[] = mysql_fetch_assoc($res_PlayerStats)) || array_pop($PlayerStatsArray)); 
	
		$TopArray = $PlayerStatsArray;
//		$TopArray = Get_Ratios_ByWorldID($world_id, $limit_results, $block_type, $stone_threshold);
		$color_important_columns = array("diamond_ratio", "gold_ratio", "slope_before_neg", "slope_after_neg");

		foreach($TopArray as $dataset_rownum => &$dataset_row)
		{
			//echo "INDEX: $dataset_rownum <br>";
			foreach($colorbins as $color_column_name => $bins)
			{
				//echo "COLOR_SEARCH: $color_column_name <br>";					
				foreach($dataset_row as $row_column_name => &$row_column_value)
				{
					if(array_key_exists($color_column_name, $dataset_row) && $color_column_name == $row_column_name)
					{
						$tempcolor = -3;
						$dataset_row["color_" . $row_column_name] = -3;
						//echo "MATCHING_COLUMN: $row_column_name == $color_column_name <br>";
						$compare_value = ($colorbins[$color_column_name][9] < 0) ? abs($row_column_value) : $row_column_value;
						
						if(isset($row_column_value) && $row_column_value != "")
						{
							if($colorbins[$color_column_name][9] > 0)
							{
								$tempcolor = 10;									
								while($row_column_value < $colorbins[$color_column_name][$tempcolor] && $tempcolor > 0)
								{
									//echo "$color_column_name >> " . $colorbins[$color_column_name][$tempcolor] . " [" . ($tempcolor) . "]<br>";
									$tempcolor--;	
								}
							}
							else
							{
								$tempcolor = 0;
								while($row_column_value < $colorbins[$color_column_name][$tempcolor] && $tempcolor < 10)
								{
									//echo "$color_column_name >> " . $colorbins[$color_column_name][$tempcolor] . " [" . ($tempcolor) . "]<br>";
									$tempcolor++;	
								}	
							}
							$dataset_row["color_" . $row_column_name] = $tempcolor;
						}
						else
						{
							$dataset_row["color_" . $row_column_name] = -3;
						}
					}
				}
				//echo "<BR>";
			}
			$row_color_stats_full = array();
			$row_color_stats_top2 = array();
			foreach($color_important_columns as $column_name)
			{
				if(isset($dataset_row["color_" . $column_name]) && $dataset_row["color_" . $column_name] >= 0)
				{
					array_push($row_color_stats_full, $dataset_row["color_" . $column_name]);
				}
			}
			
			arsort($row_color_stats_full);
			$row_color_stats_top2 = array_slice($row_color_stats_full,0,2);
			
			if(count($row_color_stats_full) > 0)
			{
				$dataset_row["color_max"] = max($row_color_stats_full);
				$dataset_row["color_avg"] = number_format(array_sum($row_color_stats_full) / count($row_color_stats_full),0);
			}
			if(count($row_color_stats_top2) > 0)
			{
				$dataset_row["color_avg_top2"] = number_format(array_sum($row_color_stats_top2) / count($row_color_stats_top2),0);
			}
			else
			{
				$dataset_row["color_max"] = -3;
				$dataset_row["color_avg"] = -3;
				$dataset_row["color_avg_top2"] = -3;
			}
		}

	}
	elseif ($command == 'xscan')
	{
		$show_process = true;
	}
	elseif ($command == 'xupdate')
	{
		$show_process = true;
	}
	elseif ($command == 'xanalyze')
	{
		$show_process = true;
	}
	elseif ($command == 'xclear')
	{
		$show_process = true;
		$require_confirmation = true;
		$msg_confirmation = "You are about to delete all collected x-ray statistics (block counts) for all users!";
	}
	elseif ($command == 'xworlds')
	{
		
	}
	else
	{
		echo "ERROR: Unrecognized command: [$command]";
		
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
<link type="text/css" href="styles/css/xray-default/jquery-ui-1.8.18.custom.css" rel="stylesheet">
<link type="text/css" href="styles/css/xray-dark/jquery-ui-1.8.18.custom.css" rel="stylesheet">	
<link type="text/css" href="styles/css/xray-light/jquery-ui-1.8.18.custom.css" rel="stylesheet">	
<link type="text/css" href="styles/css/xray-whiteborder/jquery-ui-1.8.18.custom.css" rel="stylesheet">
<script type="text/javascript" src="styles/jquery-1.7.1.js"></script>
<script type="text/javascript" src="styles/external/jquery.bgiframe-2.1.2.js"></script>
<script type="text/javascript" src="styles/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="styles/ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="styles/ui/jquery.ui.accordion.js"></script>
<script type="text/javascript" src="styles/ui/jquery.ui.tabs.js"></script>
<script type="text/javascript" src="styles/ui/jquery.ui.mouse.js"></script>
<script type="text/javascript" src="styles/ui/jquery.ui.button.js"></script>
<script type="text/javascript" src="styles/ui/jquery.ui.draggable.js"></script>
<script type="text/javascript" src="styles/ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="styles/ui/jquery.ui.resizable.js"></script>
<script type="text/javascript" src="styles/ui/jquery.ui.dialog.js"></script>
<script type="text/javascript" src="styles/ui/jquery.ui.autocomplete.js"></script>
<script type="text/javascript" src="styles/ui/jquery.effects.core.js"></script>
<script type="text/javascript" src="styles/ui/jquery.effects.blind.js"></script>
<script type="text/javascript" src="inc/jquery.form.js"></script>
	<style type="text/css">

	</style>

	<script type="text/javascript">
		$(function(){
			$('.ui-state-default').hover(
				function(){ $(this).addClass('ui-state-hover'); }, 
				function(){ $(this).removeClass('ui-state-hover'); }
			);
			$('.ui-state-default').click(function(){ $(this).toggleClass('ui-state-active'); });
			$('.icons').append(' <a href="#">Toggle text</a>').find('a').click(function(){ $('.icon-collection li span.text').toggle(); return false; }).trigger('click');
			$( "#tabs" ).tabs();
		});
	</script>
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
<?php //echo "FIRST SETUP: [".$GLOBALS['config_settings']['settings']['first_setup']."][".FixOutput_Bool($_SESSION['first_setup'],"YES","NO","EMPTY")."]";?>
<?php //echo "AUTH_IS_VALID: [".FixOutput_Bool($_SESSION['auth_is_valid'],"YES","NO","UNDEFINED")."]"; ?>
<?php if(!$_SESSION["auth_is_valid"] || $_SESSION["first_setup"]){ ?>
<table width="800" border="0" class="borderblack_greybg_light_thick ui-corner-all">
  <tr>
    <td><form id="loginform" name="loginform" method="post" action="">
      <table width="100%" border="0">
        <tr>
          <td><table width="100%" height="90" border="0" class="xray_header">
            <tr>
              <td><a href="xray.php" target="_self"><img src="img/null15.gif" width="500" height="80" hspace="0" vspace="0" border="0" /></a></td>
            </tr>
          </table></td>
        </tr>
        <tr>
          <td><table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all">
            <tr>
              <td align="right">&nbsp;</td>
            </tr>
          </table></td>
        </tr>
        <tr>
          <td><table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all">
            <tr>
              <td>&nbsp;</td>
              </tr>
            <tr>
              <td align="right"><?php if($auth['logout_success']!=""){ ?>
                <table width="100%" border="0" cellpadding="20" class="ui-widget ui-state-highlight ui-corner-all border_black_thick">
                  <tr>
                    <td align="center" valign="middle">&nbsp;</td>
                  </tr>
                  <tr>
                    <td align="center" valign="middle"><strong><?php echo $auth['logout_success']; ?>
                      </h1>
                    </strong></td>
                  </tr>
                  <tr>
                    <td align="center" valign="middle">[ <a href="xray.php" target="_self">Login</a> ]</td>
                  </tr>
              </table>
<br />
                <?php } if($auth['login_error']!=""){ ?>
                <table width="100%" border="0" cellpadding="20" class="ui-widget ui-state-error ui-corner-all border_black_thick">
                  <tr>
                    <td align="center" valign="middle">&nbsp;</td>
                  </tr>
                  <tr>
                    <td align="center" valign="middle"><strong><?php echo $auth['login_error']; ?>
                    </strong></td>
                  </tr>
                  <tr>
                    <td align="center" valign="middle">&nbsp;</td>
                  </tr>
                  </table>
<br />
                <?php } ?>
				<?php if($_SESSION['first_setup']){ ?>
                <table width="100%" border="0" cellpadding="20" class="ui-widget ui-state-error ui-corner-all border_black_thick">
                  <tr>
                    <td align="center" valign="middle">&nbsp;</td>
                  </tr>
                  <tr>
                    <td align="center" valign="middle"><strong>
						Thank you for choosing X-Ray Detective!<br /><br />
						It looks like you are running this for the first time.<BR /><BR />
						You cannot use X-Ray Detective until you have fully completed the <a href="setup.php">Setup</a>.
                    </strong></td>
                  </tr>
                  <tr>
                    <td align="center" valign="middle">&nbsp;</td>
                  </tr>
                </table>
                <?php } elseif($GLOBALS['config_settings']['auth']['mode'] == "username"){ ?>
                <?php if(isset($GLOBALS['auth']['IP_Users_list']) && count($GLOBALS['auth']['IP_Users_list']) > 0) { // Show if recordset not empty ?>
                <table width="100%" border="0">
                  <tr>
                    <td align="center" valign="middle"><h1>Please Login...</h1></td>
                    <td><table width="100%" border="0" class="borderblack_greybg_light_thick ui-corner-all">
                      <tr>
                        <td class="borderblack_greybg_norm_thin"><strong>Select Your Username</strong></td>
                        </tr>
                      <tr>
                        <td><table width="100%" border="0">
                          <tr>
                            <td width="200" valign="top" nowrap="nowrap"><strong>Your Username:</strong></td>
                            <td valign="top"><select name="my_username" id="my_username">
                              <?php foreach($GLOBALS['auth']['IP_Users_list'] as $ip_index => $ip_item) { ?>
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
                <table width="100%" border="0" cellpadding="20" class="ui-widget ui-state-error ui-corner-all border_black_thick">
                  <tr>
                    <td align="center" valign="middle">&nbsp;</td>
                  </tr>
                  <tr>
                    <td align="center" valign="middle"><strong>You are not authorized to view this page:<BR /><BR />Could not find any users matching your IP.
                    </strong></td>
                  </tr>
                  <tr>
                    <td align="center" valign="middle">&nbsp;</td>
                  </tr>
                </table>
                <?php } } if ($GLOBALS['config_settings']['auth']['mode'] == "password"){ ?>
                <table width="100%" border="0">
                  <tr>
                    <td align="center" valign="middle"><h1>Please Login...</h1></td>
                    <td align="center" valign="middle"><table width="100%" border="0" class="borderblack_greybg_light_thick ui-corner-all">
                      <tr>
                        <td class="borderblack_greybg_norm_thin"><strong>Enter Your Password</strong></td>
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
          <td><table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all">
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
<?php } if($_SESSION["auth_is_valid"] && !$_SESSION["first_setup"] && $show_process==true){ ?>
<table width="800" border="0" class="borderblack_greybg_light_thick ui-corner-all">
  <tr>
    <td><table width="100%" border="0">
      <tr>
        <td><table width="100%" height="90" border="0" cellpadding="0" cellspacing="0" class="xray_header">
          <tr>
            <td><a href="xray.php" target="_self"><img src="img/null15.gif" alt="" width="500" height="80" hspace="0" vspace="0" border="0" /></a></td>
            <td align="right"><table width="100%" border="0">
              <tr>
                <td align="right"><strong>Logged in as: <?php echo $_SESSION["auth_level"]; if($_SESSION["account"]["playername"]!=""){ echo "<BR>(".$_SESSION["account"]["playername"].")";}elseif($_SESSION["auth_type"]=="ip"){echo "<BR>ADMIN IP OVERRIDE";} ?><br />
                  </strong>
                  <form id="logoutform" name="logoutform" method="post" action="xray.php">
                    <strong>
                      <input type="submit" name="Submit" id="Submit" value="Logout" />
                      <input name="form" type="hidden" id="form" value="logoutform" />
                      </strong>
                  </form></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all">
          <tr>
            <td>&nbsp;</td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php if($require_confirmation && $_GET['confirm']!="1"){ ?>
          <table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all">
          <tr>
            <td><table width="100%" border="0" cellpadding="25">
              <tr>
                <td><table width="100%" border="0" cellpadding="15" class="borderblack_greybg_dark_thick ui-corner-all">
                  <tr>
                    <td colspan="2" class="bg_I_10"><h2>WARNING:</h2></td>
                  </tr>
                  <tr>
                    <td colspan="2" class="bg_I_-3"><?php echo $msg_confirmation; ?></td>
                  </tr>
                  <tr>
                    <td align="center" class="borderblack_greybg_norm_thick ui-corner-all"><strong><a href="xray.php">ABORT</a></strong></td>
                    <td align="center" class="borderblack_greybg_norm_thick ui-corner-all"><strong><a href="<?php echo $_SERVER['REQUEST_URI'] . "&confirm=1"; ?>">PROCEED</a></strong></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
          </tr>
        </table>
          <?php } else { ?>
          <table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all">
          <tr>
            <td><h2>
              Processing...              
              
</h2></td>
          </tr>
          <tr>
            <td><?php 
					if($command == "xupdate")
					{
						if($_SESSION["auth_admin"] || $_SESSION["auth_mod"]) { Add_NewBreaks(); /* AutoFlagWatching(); TakeSnapshots();*/ }
						else { $command_error .= "You do not have permission to do that.<BR>"; }
					}
					if($command == "xanalyze")
					{						
						if($_SESSION["auth_admin"] || $_SESSION["auth_mod"])
						{
							Add_Player_Mines($player_id);
							Update_Player_MinesStats($player_id);
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
              <table width="100%" border="0" class="ui-widget ui-state-error ui-corner-all border_black_thick">
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
              <table width="100%" border="0" class="ui-widget ui-state-highlight ui-corner-all border_black_thick">
              <tr>
                <td align="center" valign="middle"><h1 class="success"><?php echo $command_success; ?></h1>
                  </h1></td>
              </tr>
              <tr>
                <td align="center" valign="middle">
					<?php if($player_name!=""){ ?>[ <a href="xray.php?command=xsingle&player=<?php echo $player_name; ?>">Player's Stats</a> ] <?php } ?>
                    <?php if($command=="xupdate"){ ?>[ <a href="xray.php?command=xtoplist">Top List</a> ] <?php } ?>
                    [ <a href="xray.php">Home</a> ]</td>
              </tr>
            </table>
              <?php } ?></td>
          </tr>
        </table>
          <?php } ?></td>
      </tr>
      <tr>
        <td><table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all">
          <tr>
            <td>&nbsp;</td>
          </tr>
        </table></td>
      </tr>
    </table></td>
  </tr>
</table>
<br />
<?php } elseif($_SESSION["auth_is_valid"] && !$_SESSION["first_setup"] && array_search($command, array("", "xtoplist", "xsingle", "xglobal", "xworlds"))!==false ) { ?>
<table width="800" border="0" class="borderblack_greybg_light_thick ui-corner-all">
  <tr>
    <td><table width="100%" border="0">
      <tr>
        <td><table width="100%" height="90" border="0" cellpadding="0" cellspacing="0" class="xray_header">
          <tr>
            <td><a href="xray.php" target="_self"><img src="img/null15.gif" alt="" width="500" height="80" hspace="0" vspace="0" border="0" /></a></td>
            <td align="right"><table width="100%" border="0">
              <tr>
                <td align="right"><strong>Logged in as: <?php echo $_SESSION["auth_level"]; if($_SESSION["account"]["playername"]!=""){ echo "<BR>(".$_SESSION["account"]["playername"].")";}elseif($_SESSION["auth_type"]=="ip"){echo "<BR>ADMIN IP OVERRIDE";} ?><br />
                </strong>
                  <form id="logoutform" name="logoutform" method="post" action="xray.php">
                    <strong>
                      <input type="submit" name="Submit" id="Submit" value="Logout" />
                      <input name="form" type="hidden" id="form" value="logoutform" />
                      </strong>
                  </form>
                  </td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width="100%" border="0" class="borderblack_greybg_norm_thick ui-corner-all">
          <tr>
            <td><table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all">
              <tr>
                <td><h1>Tasks</h1></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><table width="100%" border="0" class="bg_black">
              <tr class="borderblack_greybg_light_thin">
                <td><h3><strong>Users</strong></h3></td>
                <td><h3><strong>Moderators</strong></h3></td>
                <td><h3><strong>Administrators</strong></h3></td>
              </tr>
              <tr class="bg_white">
                <td><strong><a href="xray.php?command=xtoplist" style="color:#000000">Top User Statistics</a><a href="xray.php?command=xclear" style="color:#000000"></a></strong></td>
                <td><a href="xray.php?command=xupdate" style="color:#000000"><strong>Update  X-Ray Stats</strong></a></td>
                <td><a href="setup.php" style="color:#000000"><strong>Change X-Ray Settings</strong></a></td>
              </tr>
              <tr class="bg_white">
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr class="bg_white">
                <td><strong><a href="xray.php?command=xglobal&amp;player=GlobalRates" style="color:#000000"><s>Check Global Averages</s></a></strong></td>
                <td>&nbsp;</td>
                <td><a href="xray.php?command=xclear" style="color:#000000"><strong>Clear X-Ray Stats</strong></a></td>
              </tr>
             </table></td>
          </tr>
          <tr>
            <td><form action="xray.php" method="post" name="XR_form" target="_self" id="XR_form">
              <table width="100%" border="0" class="borderblack_greybg_light_thin">
                <tr>
                  <td width="14%" nowrap="nowrap"><strong><s>Check Player By Name</s>
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
          <form id="Get_Ratios_ByWorldID_form" name="Get_Ratios_ByWorldID_form" method="post" action="xray.php">
            <table width="100%" border="0" class="borderblack_greybg_norm_thick ui-corner-all">
              <tr>
                <td><table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all">
                  <tr>
                    <td><h1>Top Ratios</h1></td>
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
                        <input name="form" type="hidden" id="form" value="form_Get_Ratios_ByWorldID" />
                        <input name="command" type="hidden" id="command" value="xtoplist" /></td>
                    </tr>
                    <tr>
                      <td><strong>World</strong></td>
                      <td><select name="worldid" id="worldid">
<?php foreach($GLOBALS['worlds'] as $world_key => $world_item ){ ?>
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
                    <?php 
					// Feature currently hidden until future version
					/*
                    <tr>
                      <td><s><strong>Hide Banned Users</strong></s></td>
                      <td><input name="hide_banned" type="checkbox" id="hide_banned" value="1" />
                        <input type="submit" name="top_go" id="top_go" value="Go" /></td>
                    </tr>*/
					?>
                </table></td>
              </tr>
              <tr>
                <td>
                <?php 
				//echo "TOP_ARRAY: "; print_r($TopArray); echo "<br>";
				if(count($TopArray)>0){ 
				?>
                  <table width="100%" border="0" class="bg_black">
                  <tr class="bg_white">
                    <td class="bg_AAA_x"><strong>Username</strong></td>
                    <td class="bg_AAA_x"><strong>Stones</strong></td>
                    <td align="center" class="bg_AAA_x"><strong>Max</strong></td>
                    <td align="center" class="bg_AAA_x"><strong>A</strong></td>
                    <td align="center" class="bg_AAA_x"><strong>B</strong></td>
                    <td align="center" class="bg_<?php if($sortby_column_name=="diamond_ratio"){echo"I";}else{echo"AAA";}?>_x"><strong>Diamond</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="gold_ratio"){echo"I";}else{echo"AAA";}?>_x"><strong>Gold</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="slope_before_pos"){echo"I";}else{echo"AAA";}?>_x"><strong>SB +</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="slope_before_neg"){echo"I";}else{echo"AAA";}?>_x"><strong>SB -</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="slope_after_pos"){echo"I";}else{echo"AAA";}?>_x"><strong>SA +</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="slope_after_neg"){echo"I";}else{echo"AAA";}?>_x"><strong>SA -</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="first_block_ore"){echo"I";}else{echo"AAA";}?>_x"><strong>1st Ore</strong></td>
                    <td nowrap="nowrap" class="bg_AAA_x"><strong>Ores</strong></td>
                    <td nowrap="nowrap" class="bg_AAA_x"><strong>Clusters</strong></td>
                    </tr>
                  <?php 
				  
				  
				  		$sortby_column_name = "diamond_ratio";
				  		foreach($TopArray as $key => $top)
				  		{
							//$top["firstlogin"] = date_create_from_format("Y-m-d H:i:s", $top["firstlogin"]);
?>
                  <tr class="bg_I_-3">
<!--                    <td nowrap="nowrap" class="bg_I_<?php echo $top["color_" . $sortby_column_name];?>"><strong><?php echo $top["playername"]; ?></strong></td> -->
                <td nowrap="nowrap" class="bg_I_<?php echo $top["color_max"];?>"><a href="xray.php?command=xsingle&amp;player=<?php echo $top["playername"]; ?>"><strong><?php echo $top["playername"]; ?></strong></a></td>
                    <td nowrap="nowrap" class="bg_I_<?php echo $top["color_max"];?>"><strong><?php echo $top["stone_count"]; ?></strong></td>
                    <td nowrap="nowrap" class="bg_I_<?php echo $top["color_max"];?>"><?php echo $top["color_max"];?></td>
                    <td nowrap="nowrap" class="bg_I_<?php echo $top["color_avg"];?>"><?php echo $top["color_avg"];?></td>
                    <td nowrap="nowrap" class="bg_I_<?php echo $top["color_avg_top2"];?>"><?php echo $top["color_avg_top2"];?></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="diamond_ratio"){echo"E";}else{echo"I";}?>_<?php echo $top["color_diamond_ratio"];?>"><?php echo $top["diamond_ratio"]; ?></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="gold_ratio"){echo"E";}else{echo"I";}?>_<?php echo $top["color_gold_ratio"];?>"><?php echo $top["gold_ratio"]; ?></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="slope_before_pos"){echo"E";}else{echo"I";}?>_<?php echo $top["color_slope_before_pos"];?>"><?php echo $top["slope_before_pos"]; ?></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="slope_before_neg"){echo"E";}else{echo"I";}?>_<?php echo $top["color_slope_before_neg"];?>"><?php echo $top["slope_before_neg"]; ?></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="slope_after_pos"){echo"E";}else{echo"I";}?>_<?php echo $top["color_slope_after_pos"];?>"><?php echo $top["slope_after_pos"]; ?></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="slope_after_neg"){echo"E";}else{echo"I";}?>_<?php echo $top["color_slope_after_neg"];?>"><?php echo $top["slope_after_neg"]; ?></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="first_block_ore"){echo"E";}else{echo"I";}?>_<?php echo $top["color_first_block_ore"];?>"><?php echo $top["first_block_ore"]; ?></td>
                    <td nowrap="nowrap" class="bg_I_0"><?php echo $top["total_ores"]; ?></td>
                    <td nowrap="nowrap" class="bg_I_0"><?php echo $top["total_clusters"]; ?></td>
                    </tr>
                  <?php if(!(($key+1) % 25) ){ ?>
                  <tr class="bg_white">
                    <td class="bg_AAA_x"><strong>Username</strong></td>
                    <td class="bg_AAA_x"><strong>Stones</strong></td>
                    <td align="center" class="bg_AAA_x"><strong>Max</strong></td>
                    <td align="center" class="bg_AAA_x"><strong>A</strong></td>
                    <td align="center" class="bg_AAA_x"><strong>B</strong></td>
                    <td align="center" class="bg_<?php if($sortby_column_name=="diamond_ratio"){echo"I";}else{echo"AAA";}?>_x"><strong>Diamond</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="gold_ratio"){echo"I";}else{echo"AAA";}?>_x"><strong>Gold</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="slope_before_pos"){echo"I";}else{echo"AAA";}?>_x"><strong>SB +</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="slope_before_neg"){echo"I";}else{echo"AAA";}?>_x"><strong>SB -</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="slope_after_pos"){echo"I";}else{echo"AAA";}?>_x"><strong>SA +</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="slope_after_neg"){echo"I";}else{echo"AAA";}?>_x"><strong>SA -</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="first_block_ore"){echo"I";}else{echo"AAA";}?>_x"><strong>1st Ore</strong></td>
                    <td nowrap="nowrap" class="bg_AAA_x"><strong>Ores</strong></td>
                    <td nowrap="nowrap" class="bg_AAA_x"><strong>Clusters</strong></td>
                    </tr>
                  <?php } } // End For Loop
				  if( (($key+1) % 25) ){ ?>
                  <tr class="bg_white">
                    <td class="bg_AAA_x"><strong>Username</strong></td>
                    <td class="bg_AAA_x"><strong>Stones</strong></td>
                    <td align="center" class="bg_AAA_x"><strong>Max</strong></td>
                    <td align="center" class="bg_AAA_x"><strong>A</strong></td>
                    <td align="center" class="bg_AAA_x"><strong>B</strong></td>
                    <td align="center" class="bg_<?php if($sortby_column_name=="diamond_ratio"){echo"I";}else{echo"AAA";}?>_x"><strong>Diamond</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="gold_ratio"){echo"I";}else{echo"AAA";}?>_x"><strong>Gold</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="slope_before_pos"){echo"I";}else{echo"AAA";}?>_x"><strong>SB +</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="slope_before_neg"){echo"I";}else{echo"AAA";}?>_x"><strong>SB -</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="slope_after_pos"){echo"I";}else{echo"AAA";}?>_x"><strong>SA +</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="slope_after_neg"){echo"I";}else{echo"AAA";}?>_x"><strong>SA -</strong></td>
                    <td nowrap="nowrap" class="bg_<?php if($sortby_column_name=="first_block_ore"){echo"I";}else{echo"AAA";}?>_x"><strong>1st Ore</strong></td>
                    <td nowrap="nowrap" class="bg_AAA_x"><strong>Ores</strong></td>
                    <td nowrap="nowrap" class="bg_AAA_x"><strong>Clusters</strong></td>
                    </tr>
                  <?php } ?>
                </table>
				<?php } // TopArray is not empty ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
            </table>
          </form>
          <?php } ?></td>
      </tr>
      <tr>
        <td><?php if($command=="xsingle" || $command=="xglobal"){ ?>
          <table width="100%" border="0" class="borderblack_greybg_norm_thick ui-corner-all">
            <tr>
            <td><table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all">
              <tr>
                <td><h1>Basic Player Stats: <font color="#FF0000"><?php echo $player_name; ?></font></h1></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><form action="xray.php" method="post" name="useraction_form" target="_self" id="useraction_form">
              <table width="100%" border="0">
                <tr>
                  <td valign="top"><table width="100%" border="0" class="borderblack_greybg_light_thick ui-corner-all">
                    <tr>
                      <td><table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all">
                        <tr>
                          <td><strong>User Status </strong></td>
                          </tr>
                        </table></td>
                      </tr>
                    <tr>
                      <td><table width="100%" border="0" class="borderblack_greybg_norm_thick ui-corner-all">
                        <tr>
                          <td><s><strong>Punishment Status</strong></s></td>
                          <td><select name="playerstatus" id="playerstatus">
                            <option value="0" selected="selected">Normal</option>
                            <option value="1">Warned</option>
                            <option value="2">Jailed</option>
                            <option value="3">Suspended</option>
                            <option value="4">Banned</option>
                            </select></td>
                          </tr>
                        <tr>
                          <td><s><strong>Watching</strong></s></td>
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
            <td><table width="100%" border="0">
              <tr>
                <td><table width="100%" border="0" class="borderblack_greybg_light_thick ui-corner-all">
                  <tr>
                    <td><table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all">
                      <tr>
                        <td><strong>Summary</strong></td>
                        </tr>
                      </table></td>
                    </tr>
                  <tr>
                    <td><table width="100%" border="0" class="borderblack_greybg_norm_thick ui-corner-all">
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
                        </table></td>
                      </tr>
                    </table></td>
                    </tr>
                  </table></td>
              </tr>
              </table></td>
          </tr>
          <tr>
            <td><table width="100%" border="0">
              <tr>
                <td><table width="100%" border="0" class="borderblack_greybg_light_thick ui-corner-all">
                  <tr>
                    <td><table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all">
                      <tr>
                        <td><strong>General Info</strong></td>
                      </tr>
                    </table></td>
                  </tr>
                  <tr>
                    <td>
                      <table width="100%" border="0" class="borderblack_greybg_norm_thick ui-corner-all">
                        <tr>
                          <th width="22%" align="right" scope="row"><s>Location</s></th>
                          <td width="78%">Future Feature</td>
                        </tr>
                        <tr>
                          <th align="right" scope="row">IP Address</th>
                          <td><?php ?></td>
                        </tr>
                        <tr>
                          <th align="right" scope="row">Join Date</th>
                          <td>&nbsp;</td>
                        </tr>
                        <tr>
                          <th align="right" scope="row">Online Time</th>
                          <td>&nbsp;</td>
                        </tr>
                        <tr>
                          <th align="right" scope="row">&nbsp;</th>
                          <td>&nbsp;</td>
                        </tr>
                      </table></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><table width="100%" border="0">
              <tr>
                <td><table width="100%" border="0" class="borderblack_greybg_light_thick ui-corner-all">
                  <tr>
                    <td><table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all">
                      <tr>
                        <td><strong><?php echo $player_name; ?>'s Basic Stats</strong></td>
                        </tr>
                      </table></td>
                    </tr>
                  <tr>
                    <td><table width="100%" border="0" class="bg_black">
                      <tr class="bg_white">
                        <td class="bg_I_x"><strong>World</strong></td>
                        <td class="bg_I_x"><strong>Stones</strong></td>
                        <td align="center" class="bg_AAA_x"><strong>D%</strong></td>
                        <td nowrap="nowrap" class="bg_AAA_x"><strong>SB+</strong></td>
                        <td nowrap="nowrap" class="bg_AAA_x"><strong>SB-</strong></td>
                        <td nowrap="nowrap" class="bg_AAA_x"><strong>Spread Before</strong></td>
                        <td nowrap="nowrap" class="bg_AAA_x">&nbsp;</td>
                        <td nowrap="nowrap" class="bg_AAA_x">&nbsp;</td>
                        <td nowrap="nowrap" class="bg_AAA_x">&nbsp;</td>
                        <td nowrap="nowrap" class="bg_AAA_x">&nbsp;</td>
                        <td nowrap="nowrap" class="bg_AAA_x"><strong>Slope After</strong></td>
                        <td nowrap="nowrap" class="bg_AAA_x"><strong>Spread After</strong></td>
                        </tr>
                      <?php foreach($player_world_stats as $pw_index => $pw_item) {?>
                      <tr class="bg_I_<?php echo $color[$sortby_column_name];?>">
                        <td nowrap="nowrap" class="bg_H_<?php echo $pw_item["color_max"];?>"><?php echo $pw_item["worldalias"]; ?></td>
                        <td nowrap="nowrap" class="bg_H_<?php echo $pw_item["color_max"];?>"><?php echo $pw_item["stone_count"];?></td>
                        <td nowrap="nowrap" class="bg_H_<?php echo $pw_item["color_diamond_ratio"];?>"><?php echo $pw_item["diamond_ratio"];?></td>
                        <td nowrap="nowrap" class="bg_H_<?php echo $pw_item["color_slope_before_pos"];?>"><?php echo $pw_item["slope_before_pos"];?></td>
                        <td nowrap="nowrap" class="bg_H_<?php echo $pw_item["color_slope_before_neg"];?>"><?php echo $pw_item["slope_before_neg"];?></td>
                        <td nowrap="nowrap" class="bg_H_<?php echo $pw_item["color_spread_before"];?>"><?php echo $pw_item["spread_before"];?></td>
                        <td nowrap="nowrap" class="bg_H_<?php echo $pw_item["color_spread_before"];?>">&nbsp;</td>
                        <td nowrap="nowrap" class="bg_H_<?php echo $pw_item["color_spread_before"];?>">&nbsp;</td>
                        <td nowrap="nowrap" class="bg_H_<?php echo $pw_item["color_spread_before"];?>">&nbsp;</td>
                        <td nowrap="nowrap" class="bg_H_<?php echo $pw_item["color_spread_before"];?>">&nbsp;</td>
                        <td nowrap="nowrap" class="bg_H_<?php echo $pw_item["color_slope_after"];?>"><?php echo $pw_item["slope_after"];?></td>
                        <td nowrap="nowrap" class="bg_H_<?php echo $pw_item["color_spread_after"];?>"><?php echo $pw_item["spread_after"];?></td>
                        </tr>
                      <?php } ?>
                      <tr class="bg_white">
                        <td class="bg_I_x"><strong>World</strong></td>
                        <td class="bg_I_x"><strong>Stones</strong></td>
                        <td align="center" class="bg_AAA_x"><strong>D%</strong></td>
                        <td nowrap="nowrap" class="bg_AAA_x"><strong>SB+</strong></td>
                        <td nowrap="nowrap" class="bg_AAA_x"><strong>SB-</strong></td>
                        <td nowrap="nowrap" class="bg_AAA_x"><strong>Spread Before</strong></td>
                        <td nowrap="nowrap" class="bg_AAA_x">&nbsp;</td>
                        <td nowrap="nowrap" class="bg_AAA_x">&nbsp;</td>
                        <td nowrap="nowrap" class="bg_AAA_x">&nbsp;</td>
                        <td nowrap="nowrap" class="bg_AAA_x">&nbsp;</td>
                        <td nowrap="nowrap" class="bg_AAA_x"><strong>Slope After</strong></td>
                        <td nowrap="nowrap" class="bg_AAA_x"><strong>Spread After</strong></td>
                        </tr>
                      </table></td>
                    </tr>
                  </table></td>
              </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td><table width="100%" border="0" class="borderblack_greybg_light_thick ui-corner-all">
              <tr>
                <td><table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all">
                  <tr>
                    <td><strong><?php echo $player_name; ?>'s Advanced Stats</strong></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td><table width="100%" border="0" class="borderblack_greybg_norm_thick ui-corner-all">
                  <tr>
                    <td><table width="100%" border="0">
                      <tr>
                        <td align="center">&nbsp;</td>
                      </tr>
                      <tr>
                        <td align="center" class="bg_H_-3"><p>You have not yet analyzed this players mining behavior. Would you like to do that now?</p>
                          <p>&nbsp;</p>
                          <form action="xray.php" method="post" name="form_startanalysis" target="_self" id="form_startanalysis">
                            <input name="form" type="hidden" id="form" value="form_analyze_mines_now" />
                            <input type="submit" name="Submit" id="Submit" value="Analyze Mining Behavior" />
                            <input name="command" type="hidden" id="command" value="xanalyze" />
                            <input name="player" type="hidden" id="player" value="<?php echo $player_name;?>" />
                          </form>
                          </p></td>
                      </tr>
                      <tr>
                        <td align="center">&nbsp;</td>
                      </tr>
                    </table>
                      <?php foreach($GLOBALS['worlds'] as $world_index => $world_item)
					  { 
					  	if(count( $player_clusters_world[$world_index]) > 0)
						{ ?>
                      <table width="100%" border="0">
                      <tr>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td><table width="100%" border="0" class="bg_black">
                          <tr class="bg_white">
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Slope Before</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Spread Before</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Ores</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Slope After</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Spread After</strong></td>
                          </tr>
                          <?php 
				  		foreach($player_clusters_world[$world_index] as $cluster_index => $cluster_item)
				  		{
						?>
                          <tr class="bg_I_0">
                            <td nowrap="nowrap" class="bg_H_<?php echo (!isset($cluster_item["slope_before"]) ) ? "-3" : $cluster_item["color_slope_before"];?>"><strong><?php echo $cluster_item["slope_before"]; ?></strong></td>
                            <td nowrap="nowrap" class="bg_H_<?php echo (!isset($cluster_item["spread_before"]) ) ? "-3" : $cluster_item["color_spread_before"];?>"><strong><?php echo $cluster_item["spread_before"]; ?></strong></td>
                            <td nowrap="nowrap"><strong><?php echo $cluster_item["ore_length"]; ?></strong></td>
                            <td nowrap="nowrap" class="bg_H_<?php echo (!isset($cluster_item["slope_after"]) ) ? "-3" : "0"; ?>"><strong><?php echo $cluster_item["slope_after"]; ?></strong></td>
                            <td nowrap="nowrap" class="bg_H_<?php echo (!isset($cluster_item["spread_after"]) ) ? "-3" : "0"; ?>"><strong><?php echo $cluster_item["spread_after"]; ?></strong></td>
                          </tr>
                          <?php if(!(($cluster_index+1) % 25) ){ ?>
                          <tr class="bg_white">
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Slope Before</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Spread Before</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Ores</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Slope After</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Spread After</strong></td>
                          </tr>
                          <?php } }
				  if( (($cluster_index+1) % 25) ){ ?>
                          <tr class="bg_white">
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Slope Before</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Spread Before</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Ores</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Slope After</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Spread After</strong></td>
                          </tr>
                          <?php } ?>
                        </table></td>
                      </tr>
                      <?php /*<!--<tr>
                        <td><table width="100%" border="0" class="bg_black">
                          <tr class="bg_white">
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Date</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Volume</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>First Block Ore?</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>PostBreaks</strong></td>
                            </tr>
	                  <?php 
				  		foreach($player_mines_all as $mine_index => $mine_item)
				  		{
							foreach($colorbins as $column_name => $bins)
							{
								$tempcolor = 10;
								$color[$column_name] = -3;
								while($mine_item[$column_name . "_ratio"] < $colorbins[$column_name][$tempcolor] && $tempcolor > 0)
								{
									//echo "<br>$sortby_column_name >> " . $colorbins[$sortby_column_name][$tempcolor] . " [" . ($tempcolor) . "]";
									$tempcolor--;	
								}
								//echo "<< <BR>";
								$color[$column_name] = $tempcolor;
							}
						?>
                          <tr class="bg_I_<?php echo $color[$sortby_column_name];?>">
                            <td nowrap="nowrap"><strong><?php echo $mine_item["volume"]; ?></strong></td>
                            <td nowrap="nowrap"><strong><?php echo $mine_item["volume"]; ?></strong></td>
                            <td nowrap="nowrap"><strong><?php echo FixOutput_Bool($mine_item["first_block_ore"],"Yes","No","?"); ?></strong></td>
                            <td nowrap="nowrap"><strong><?php echo $mine_item["volume"]; ?></strong></td>
                            </tr>
                          <?php if(!(($mine_index+1) % 25) ){ ?>
                          <tr class="bg_white">
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Date</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Volume</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>First Block Ore?</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>PostBreaks</strong></td>
                          </tr>
                          <?php } }
				  if( (($mine_index+1) % 25) ){ ?>
                          <tr class="bg_white">
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Date</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>Volume</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>First Block Ore?</strong></td>
                            <td nowrap="nowrap" class="bg_AAA_x"><strong>PostBreaks</strong></td>
                          </tr>
                          <?php } ?>
                        </table></td>
                      </tr>-->*/ ?>
                    </table>
                    <?php } } ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
          </tr>
          </table>
          <?php } ?></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
<tr>
  <td><?php if($command=="xsingle" || $command=="xglobal"){ ?>
    <table width="100%" border="0" class="borderblack_greybg_norm_thick ui-corner-all">
      <tr>
        <td><table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all">
          <tr>
            <td><h1>Global Averages</h1></td>
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
$sortby_column_name = "diamond_ratio";
for ($col = 0; $col <= 10 ; $col++)
{ ?>
            <td class="bg_G_<?php echo $col;?><?php if($col == $color[$sortby_column_name]){ echo " border_black_thick"; }?>"><?php echo  number_format($colorbins[$sortby_column_name][$col], 2); ?></td>
            <?php
} ?>
            </tr>
          <tr>
            <td class="bg_black"><strong>Lapis</strong></td>
            <?php
$sortby_column_name = "lapis_ratio";
for ($col = 0; $col <= 10 ; $col++)
{ ?>
            <td class="bg_G_<?php echo $col;?><?php if($col == $color[$sortby_column_name]){ echo " border_black_thick"; }?>"><?php echo  number_format($colorbins[$sortby_column_name][$col], 2); ?></td>
            <?php
} ?>
            </tr>
          <tr>
            <td class="bg_black"><strong>Gold</strong></td>
            <?php
$sortby_column_name = "gold_ratio";
for ($col = 0; $col <= 10 ; $col++)
{ ?>
            <td class="bg_G_<?php echo $col;?><?php if($col == $color[$sortby_column_name]){ echo " border_black_thick"; }?>"><?php echo  number_format($colorbins[$sortby_column_name][$col], 2); ?></td>
            <?php
} ?>
            </tr>
          <tr>
            <td class="bg_black"><strong>Mossy</strong></td>
            <?php
$sortby_column_name = "mossy_ratio";
for ($col = 0; $col <= 10 ; $col++)
{ ?>
            <td class="bg_G_<?php echo $col;?><?php if($col == $color[$sortby_column_name]){ echo " border_black_thick"; }?>"><?php echo  number_format($colorbins[$sortby_column_name][$col], 2); ?></td>
            <?php
} ?>
            </tr>
          <tr>
            <td class="bg_black"><strong>Iron</strong></td>
            <?php
$sortby_column_name = "iron_ratio";
for ($col = 0; $col <= 10 ; $col++)
{ ?>
            <td class="bg_G_<?php echo $col;?><?php if($col == $color[$sortby_column_name]){ echo " border_black_thick"; }?>"><?php echo  number_format($colorbins[$sortby_column_name][$col], 2); ?></td>
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