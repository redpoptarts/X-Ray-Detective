<?php require_once('inc/core_xdetector.php'); ?>
<?php include_once('inc/auth_xray.php'); ?>
<?php

Global_Init();
Do_Auth(true);

$tab_count = 3;

if($_GET['setup_stage']!=""){$_POST = $_GET; }

$setup_stage_tab = $_POST['setup_stage']; if($setup_stage_tab==""){ $setup_stage_tab = 0; }

if( FixOutput_Bool($GLOBALS['config_settings']['settings']['first_setup'], true, false, true) )
{
	$_SESSION['first_setup'] = true;
}

// Only users who have been authenticated by IP can use the setup script
if( $_SESSION['auth_type']!="ip" )
{
	$_SESSION['auth_is_valid'] = false;
	$config_error .= "ERROR: Your IP is not on the Failsafe IPs list.<BR>You cannot use the Setup script until you add your IP to that list.<BR><BR>(You must manually edit: <em>config_settings.php</em>)<BR><BR>Your current IP is: " . $_SERVER['REMOTE_ADDR'] . "<BR>";
}

if($_POST['form']!="")
{
	$setup_submit_ok = false;
	if($_POST['config_db_submit']!="")
	{
		$setup_stage_tab = 0;
		
		$db1_ok = Check_DB_Exists(true, $_POST['db_type'],
			$_POST['db_source_host'], $_POST['db_source_base'], $_POST['db_source_user'], $_POST['db_source_pass'], $_POST['db_source_prefix']);

		if(!$_POST['copy_stx'])
		{
			$db2_ok = Check_DB_Exists(false, "",
				$_POST['db_xray_host'], $_POST['db_xray_base'], $_POST['db_xray_user'], $_POST['db_xray_pass'], $_POST['db_xray_prefix']);
		}
		else
		{
			$db2_ok = $db1_ok;
			$_POST['db_xray_host']=$_POST['db_source_host'];
			$_POST['db_xray_base']=$_POST['db_source_base'];
			$_POST['db_xray_user']=$_POST['db_source_user'];
			$_POST['db_xray_pass']=$_POST['db_source_pass'];
			$_POST['db_xray_prefix']=$_POST['db_source_prefix'];
		}
		
		if($db1_ok && $db2_ok)
		{
			$GLOBALS['config_db']['db_config']['db_module_type']=$_POST['db_type'];

			$GLOBALS['config_db']['db_config']['db_use_same']=FixOutput_Bool($_POST['copy_stx'],"yes","no");

			$GLOBALS['config_db']['db_source']['host']=$_POST['db_source_host'];
			$GLOBALS['config_db']['db_source']['base']=$_POST['db_source_base'];
			$GLOBALS['config_db']['db_source']['user']=$_POST['db_source_user'];
			$GLOBALS['config_db']['db_source']['pass']=$_POST['db_source_pass'];
			$GLOBALS['config_db']['db_source']['prefix']=$_POST['db_source_prefix'];
			
			$GLOBALS['config_db']['db_xray']['host']=$_POST['db_xray_host'];
			$GLOBALS['config_db']['db_xray']['base']=$_POST['db_xray_base'];

			$GLOBALS['config_db']['db_xray']['user']=$_POST['db_xray_user'];
			$GLOBALS['config_db']['db_xray']['pass']=$_POST['db_xray_pass'];
			$GLOBALS['config_db']['db_xray']['prefix']=$_POST['db_xray_prefix'];
			

			$outfile_ok = Save_Config_DB();

			unset($GLOBALS['config_db']);
			
			if($outfile_ok)
			{
				$infile_ok = Load_Configs($config_database_file_path, $GLOBALS['config_db']);
				//echo FixOutput_Bool($infile_ok, "INFILE OK<BR>", "INFILE BAD<BR>");
			}
			// Create Tables
			if( $infile_ok )
			{
				$multi_link = mysqli_connect($db['x_host'], $db['x_user'], $db['x_pass'], $db['x_base']);
				
				/* check connection */
				if (mysqli_connect_errno()) {
					printf($_SERVER["SCRIPT_FILENAME"] . "Connect failed: %s\n", mysqli_connect_error());
					exit();
				}
				
				$sql_NewTables = file_get_contents(dirname($_SERVER['SCRIPT_FILENAME']). "/inc/sql/initialize_database.sql");
				
				if($db['x_base']!="minecraft")
				{
					$sql_NewTables = str_replace("`minecraft`","`".$db['x_base']."`",$sql_NewTables);
				}
				
				/* execute multi query */
				if (mysqli_multi_query($multi_link, $sql_NewTables)) {
					do {
						/* store first result set */
						if ($result = mysqli_store_result($multi_link)) {
							while ($row = mysqli_fetch_row($result)) {
								//printf("%s\n", $row[0]);
							}
							mysqli_free_result($result);
						}
						/* print divider */
						if (mysqli_more_results($multi_link)) {
							//printf("-----------------\n");
						}
					} while (mysqli_next_result($multi_link));
				}
				
				/* close connection */
				mysqli_close($multi_link);
			} else { $config_error .= "ERROR: There was a problem reading the database configuration after saving it.<BR>";}
			
			$xtables_valid_response =Check_XTables_Valid();
			//echo FixOutput_Bool($xtables_valid_response["error"], $xtables_valid_response["message"], "XTABLES OK<BR>");
			
			if($xtables_valid_response["error"])
			{
				$config_error .= "ERROR: There was a problem creating the tables in the database you specified.<BR>";	
				$config_error .= "After attempting to create the X-Ray Detective tables, they could not be found.<BR>";
				$config_error .= "[".$xtables_valid_response["message"]."]<BR>";
			}
			else
			{
				$config_success .= "SUCCESS: The X-Ray Detective tables were created successfully!<BR>";
				$setup_submit_ok = true;
			}
			
		}
	}
	elseif($_POST['config_auth_submit']!="")
	{
		$setup_stage_tab = 1;
		$auth_input_ok = true;
		
		switch($_POST['auth_mode'])
		{
			case "":
				$auth_input_ok = false;
				$config_error .= "ERROR: Invalid form data. Auth_Mode cannot be blank.<BR>"; break;
			case "username":
				if( !ctype_alnum(str_replace(array(","," ","_"), "", $_POST['auth_admin_usernames'])) )
					{ $config_error .= "ERROR: Admin Username list contains invalid characters. <BR>"; $auth_input_ok = false; }
				if( $_POST['auth_mod_usernames']!="" && !ctype_alnum(str_replace(array(","," ","_"), "", $_POST['auth_mod_usernames'])) )
					{ $config_error .= "ERROR: Moderator Username list contains invalid characters. <BR>"; $auth_input_ok = false; }
				if( $_POST['auth_user_usernames']!="" && !ctype_alnum(str_replace(array(","," ","_"), "", $_POST['auth_user_usernames'])) )
					{ $config_error .= "ERROR: User Username list contains invalid characters. <BR>"; $auth_input_ok = false; }
				break;
			case "password":
				if( !ctype_graph($_POST['auth_admin_password']) )
					{ $config_error .= "ERROR: Admin password must contain no spaces. <BR>"; $auth_input_ok = false; }
				if( !ctype_graph($_POST['auth_mod_password']) )
					{ $config_error .= "ERROR: Moderator password must contain no spaces. <BR>"; $auth_input_ok = false; }
				if( !ctype_graph($_POST['auth_user_password']) )
					{ $config_error .= "ERROR: User password must contain no spaces. <BR>"; $auth_input_ok = false; }
				break;
			case "none":
				$auth_input_ok = true;
				break;	
		}
		
		if($auth_input_ok)
		{
			$GLOBALS['config_settings']['auth']['mode'] = $_POST['auth_mode'];
			$GLOBALS['config']['auth']['mode'] = $_POST['auth_mode'];
			switch($_POST['auth_mode'])
			{
				case "":
					$auth_input_ok = false;
					$config_error .= "ERROR: Invalid form data. Auth_Mode cannot be blank.<BR>"; break;
				case "username":
					// Write settings into config_file array
					$GLOBALS['config_settings']['auth']['admin_usernames']	= implode(", ", preg_split("/[\s,]+/", $_POST['auth_admin_usernames']) );
					$GLOBALS['config_settings']['auth']['mod_usernames']	= implode(", ", preg_split("/[\s,]+/", $_POST['auth_mod_usernames']) );
					$GLOBALS['config_settings']['auth']['user_usernames'] 	= implode(", ", preg_split("/[\s,]+/", $_POST['auth_user_usernames']) );
					
					// Refresh settings so that changes appear on the form
					$GLOBALS['config']['auth']['admin_usernames']	 = $GLOBALS['config_settings']['auth']['admin_usernames'];
					$GLOBALS['config']['auth']['mod_usernames']		 = $GLOBALS['config_settings']['auth']['mod_usernames'];
					$GLOBALS['config']['auth']['user_usernames']	 = $GLOBALS['config_settings']['auth']['user_usernames'];
					
					break;
				case "password":
					$GLOBALS['config_settings']['auth']['admin_password']	= $_POST['auth_admin_password'];
					$GLOBALS['config_settings']['auth']['mod_password']		= $_POST['auth_mod_password'];
					$GLOBALS['config_settings']['auth']['user_password']	= $_POST['auth_user_password'];
					
					$GLOBALS['config']['auth']['admin_password']	 = $GLOBALS['config_settings']['auth']['admin_password'];
					$GLOBALS['config']['auth']['mod_password']		 = $GLOBALS['config_settings']['auth']['mod_password'];
					$GLOBALS['config']['auth']['user_password']		 = $GLOBALS['config_settings']['auth']['user_password'];
					break;
				case "none":
					break;	
			}
			
			$GLOBALS['config_settings']['auth']['failsafe_ips'] 	= implode(", ", preg_split("/[\s,]+/", $_POST['auth_failsafe_ips']) );
			$GLOBALS['config']['auth']['failsafe_ips']				= $GLOBALS['config_settings']['auth']['failsafe_ips'];
			
			$outfile_ok = Save_Config_Settings();
			if($outfile_ok)
			{
				$setup_submit_ok = true;
				$config_success .= "Authentication Settings Saved Successfully.<BR>";
			}
			else
			{
				$config_error .= "ERROR: There was a problem writing the Settings configuration file.<BR>";
			}
			//echo "GLOBAL AUTH: <BR>"; print_r( $GLOBALS['config']['auth'] ); echo "<BR>";
			
		}
	}
	elseif($_POST['config_worlds_submit']!="")
	{
		$setup_stage_tab = 2;
		
		$Worlds_all_array = GetWorlds_All();
		$Worlds_update_array = array();

		foreach($Worlds_all_array as $world_index => $world_item)
		{
			if($_POST["worldalias_".$world_item["worldid"]]!="")
			{
				//echo "[". $_POST["worldtoggle_".$world_item["worldid"]] . "]";
				array_push($Worlds_update_array, array(
					"worldid"	=>	$world_item["worldid"],
					"enabled"	=>	FixOutput_Bool($_POST["worldtoggle_".$world_item["worldid"]],"1","0"),
					"worldalias"		=>	$_POST["worldalias_".$world_item["worldid"]]));
			}
		}
		
		if(count($Worlds_update_array)==0)
		{
			$config_error .= "ERROR: World Aliases cannot be blank.<BR>";
			
		}
		else
		{
			$sql_ModifyWorlds = "";
			foreach($Worlds_update_array as $world_index => $world_item)
			{
				$sql_ModifyWorlds .= " UPDATE `".$GLOBALS['db']['x_base']."`.`x-worlds` ";
				$sql_ModifyWorlds .= " SET ";
				$sql_ModifyWorlds .= " `worldalias` =	'". ucfirst($world_item["worldalias"]) ."', ";
				$sql_ModifyWorlds .= " `enabled`	=	'". $world_item["enabled"] ."' ";
				$sql_ModifyWorlds .= " WHERE `worldid` = '". $world_item["worldid"] ."';";
				//if($world_index < count($Worlds_update_array)-1){ $sql_ModifyWorlds .= ";"; }
			}
			//echo "SQL QUERY: <BR>" . $sql_ModifyWorlds . "<BR>";
			$res_ModifyWorlds = mysqli_multi_query($GLOBALS['db']['x_link'],$sql_ModifyWorlds);
			if(mysqli_errno($GLOBALS['db']['x_link']))
			{
				die("SQL_QUERY[ModifyWorlds]: " . $sql_ModifyWorlds . "<BR> " . mysqli_error($GLOBALS['db']['x_link']) . "<BR>");
			}
			else
			{
				$setup_submit_ok = true;
				$config_success .= "World Settings Saved Successfully.<BR>";
			}
		}
	}
	else
	{
		$config_error .= "ERROR: Invalid form data.<BR>";
	}
	
	if($_SESSION['first_setup'])
	{
		if($setup_submit_ok)
		{
			$setup_stage_tab++;	
			echo "SETUP SUBMIT OK [$setup_stage_tab]<BR>";
		}
		else
		{
			echo "SETUP ERROR: [$config_error]<BR>";
		}
		if($setup_stage_tab == $tab_count)
		{
			$GLOBALS['config_settings']['settings']['first_setup']="no"; 
			$GLOBALS['config']['settings']['first_setup']=false;
			$outfile_ok = Save_Config_Settings();
			if($outfile_ok)
			{
				$_SESSION['first_setup'] = false; session_unset(); Do_Auth(true);
				$config_success .= "<BR><BR>SETUP COMPLETE: You have successfully configured X-Ray Detective.<BR>";
			}
			else
			{
				$config_error .= "ERROR: There was a problem writing to the Settings config file.<BR>";
			}
		}
	}
	if(!$setup_submit_ok && $config_error == ""){ $config_error .= "ERROR: An unknown error occurred. Cannot continue to next step of installation.<BR>"; }
}


if(!$_SESSION['first_setup'] || $setup_stage_tab == 2)
{
	// Attempt to populate the X-Ray Worlds table before loading the Worlds configuration page
	$Find_Worlds_array = Find_WorldTables_Valid();
	$Worlds_all_array = GetWorlds_All();
	$Worlds_enabled_array = GetWorlds_Enabled();
	foreach($Find_Worlds_array as $new_world_index => &$new_world_item)
	{
		$new_world_item["table_name"] = preg_replace('/^' . $GLOBALS['db']['s_prefix'] . '/', '', $new_world_item["table_name"]);
		$new_world_item["table_name"] = preg_replace('/^' . "lb-" . '/', '', $new_world_item["table_name"]);
		$new_world_item["table_name"] = preg_replace('/' . "_nether" . '$/', '', $new_world_item["table_name"]);
		$new_world_item["table_name"] = preg_replace('/' . "_the_end" . '$/', '', $new_world_item["table_name"]);
		
		foreach($Worlds_all_array as $old_world_index => $old_world_item)
		{
			if($new_world_item['table_name'] == $old_world_item['worldname'])
			{
				unset($Find_Worlds_array[$new_world_index]);
			}
		}
	}
	
	if(count($Find_Worlds_array)>0)
	{
		$sql_AddValidWorlds  = "INSERT IGNORE INTO ".$GLOBALS['db']['x_base'].".`x-worlds` ";
		$sql_AddValidWorlds .= " (`worldname`, ";
		$sql_AddValidWorlds .= " `worldalias`) ";
		$sql_AddValidWorlds .= " VALUES ";
		foreach($Find_Worlds_array as $world_index => $world_item)
		{
	//		$world_item["table_name"] = trim($GLOBALS['db']['s_prefix'], $world_item["table_name"]);
	//		$world_item["table_name"] = trim("lb-", $world_item["table_name"]);
			
	
			
			$sql_AddValidWorlds .= " ( ";
			$sql_AddValidWorlds .= " 	'". $world_item["table_name"] ."', ";
			$sql_AddValidWorlds .= " 	'". ucfirst($world_item["table_name"]) ."' ";
			$sql_AddValidWorlds .= " ) ";
			if($world_index < count($Find_Worlds_array)-1){ $sql_AddValidWorlds .= ","; }
		}
		//echo "SQL QUERY: <BR>" . $sql_AddValidWorlds . "<BR>";
		$res_AddValidWorlds = mysql_query($sql_AddValidWorlds);
		if(mysql_errno())
		{
			die("SQL_QUERY[AddValidWorlds]: " . $sql_AddValidWorlds . "<BR> " . mysql_error() . "<BR>");
		}
	
	
		//echo "VALID WORLDS: <BR>"; print_r($Find_Worlds_array); echo "<BR>";
		
	
	}
}

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
	.toggler { width: 500px; height: 200px; position: relative; }
	#button { padding: .5em 1em; text-decoration: none; }
	#effect {position: relative;   }
	</style>

	<script type="text/javascript">
open:
$(function()
{
	$( "#tabs" ).tabs( {selected: <?php echo $setup_stage_tab; ?>
		<?php if($_SESSION['first_setup'])
		{
			echo ", disabled: "; $tab_disabled=array();
			for($tab_index = 0; $tab_index <= $tab_count-1; $tab_index++)
			{
				if($tab_index != $setup_stage_tab)
				{
					array_push($tab_disabled, $tab_index);
				}
			}
			echo "[".  implode(", ", $tab_disabled) . "]";
		} ?> } );
	
	$('.ui-state-default').hover(
		function(){ $(this).addClass('ui-state-hover'); }, 
		function(){ $(this).removeClass('ui-state-hover'); }
	);
	//$('.ui-state-default').click(function(){ $(this).toggleClass('ui-state-active'); });

	$( "#stage" ).buttonset();
	
	
	$( "#logging_radio" ).buttonset();
	$( "#copy_source_to_xray_radio" ).buttonset();
	
	$( ".radio-disable" ).button({ disabled: true });
	$( "input:submit" ).button();

	
	$( "#db_xray_host, #db_xray_base, #db_xray_user, #db_xray_pass, #db_xray_prefix" ).change(function()
	{
		if( $('input:radio[name=copy_stx]:checked').val() == 1 )
		{
			$('#db_xray_host').val( $('#db_source_host').val() );
			$('#db_xray_base').val( $('#db_source_base').val() );
			$('#db_xray_user').val( $('#db_source_user').val() );
			$('#db_xray_pass').val( $('#db_source_pass').val() );
			$('#db_xray_prefix').val( $('#db_source_prefix').val() );	
		}
		
	});
	
	$('#copy_stx_radio1').click(function()
	{
		$('input[name*="db_xray_"]').attr('disabled', true);
		
		$('#db_xray_host').val( $('#db_source_host').val() );
		$('#db_xray_base').val( $('#db_source_base').val() );
		$('#db_xray_user').val( $('#db_source_user').val() );
		$('#db_xray_pass').val( $('#db_source_pass').val() );
		$('#db_xray_prefix').val( $('#db_source_prefix').val() );
		
		if( $( "#db_source_ok" ).val() == "1")
		{
			$( "#check_xray_db" ).switchClass( "ui-state-default", "ui-state-highlight", 1000 );
			$( "#check_xray_db" ).switchClass( "ui-state-error", "ui-state-highlight", 1000 );
		}
		else
		{
			$( "#check_xray_db" ).switchClass( "ui-state-highlight", "ui-state-default", 1000 );
			$( "#check_xray_db" ).switchClass( "ui-state-error", "ui-state-default", 1000 );
		}
		
		$( "#db_xray_ok" ).val( $( "#db_source_ok" ).val() );
		document.getElementById("check_xray_db_text").innerHTML = document.getElementById("check_source_db_text").innerHTML;
		
		Check_DB_Form_OK();

	});
		
	$('#copy_stx_radio2').click(function()
	{
		$('input[name*="db_xray_"]').attr('disabled', false);
		
		$( "#db_xray_ok" ).val('0');

		$( "#check_xray_db" ).switchClass( "ui-state-highlight", "ui-state-default", 1000 );
		$( "#check_xray_db" ).switchClass( "ui-state-error", "ui-state-default", 1000 );
		
		document.getElementById("check_xray_db_text").innerHTML = "Check Connection";
		
		Check_DB_Form_OK();
	});

	$( "#db_setup_error_dialog" ).dialog({
		autoOpen: false,
		modal: true,
		buttons: {
			Ok: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	$( "#logging_radio" ).click(function()
	{
		$( "#check_source_db" ).switchClass( "ui-state-default", "ui-state-error", 1000 );
		$( "#check_source_db" ).switchClass( "ui-state-highlight", "ui-state-error", 1000 );
		
		document.getElementById("check_source_db_text").innerHTML = "Check Connection";
		$( "#db_source_ok" ).val('0');
		
		if( $('input:radio[name=copy_stx]:checked').val() == 1 )
		{
			$( "#db_xray_ok" ).val('0');
			$( "#check_xray_db" ).switchClass( "ui-state-default", "ui-state-error", 1000 );
			$( "#check_xray_db" ).switchClass( "ui-state-highlight", "ui-state-error", 1000 );
			document.getElementById("check_xray_db_text").innerHTML = document.getElementById("check_source_db_text").innerHTML;
		}
		
		Check_DB_Form_OK();
	});
	

	$( 'li[id*="check_"]' ).click(function()
	{
		var clicked_obj = $(this);
		
		$.ajax(
		{ url: 'inc/live/check_db_exists.php',
				data: { 
					type: $('input:radio[name=db_type]:checked').val(),
					host: clicked_obj.closest('table').find('input:text[id*=host]').val(),
					base: clicked_obj.closest('table').find('input:text[id*=base]').val(),
					user: clicked_obj.closest('table').find('input:text[id*=user]').val(),
					pass: clicked_obj.closest('table').find('input:text[id*=pass]').val(),
					check_logging_table: clicked_obj.attr("id")
					},
				type: 'post',
				dataType: 'json',
				success: function(response, data)
						 {
								//alert(clicked_obj.attr('id'));
								if(response.message == "HOST OK")
								{
									clicked_obj.switchClass( "ui-state-default", "ui-state-highlight", 1000 );
									clicked_obj.switchClass( "ui-state-error", "ui-state-highlight", 1000 );
									clicked_obj.closest('ul').find('span:last').html("Connection OK");
									clicked_obj.closest('ul').find('input').val('1');
									clicked_obj.button();

									if( ( $('input:radio[name=copy_stx]:checked').val() == 1 ) && ( clicked_obj.attr("id") == "check_source_db"  ) )
									{
										$( "#db_xray_ok" ).val('1');
										$( "#check_xray_db" ).switchClass( "ui-state-default", "ui-state-highlight", 1000 );
										$( "#check_xray_db" ).switchClass( "ui-state-error", "ui-state-highlight", 1000 );
										document.getElementById("check_xray_db_text").innerHTML = document.getElementById("check_source_db_text").innerHTML;
									}
								} else {

							
									document.getElementById("source_db_error_main").innerHTML = "An error occurred while validating MySQL Server.<BR>Please check the information and try again.";
									document.getElementById("source_db_error_specific").innerHTML = response.message;
									$( "#db_setup_error_dialog" ).dialog({
										autoOpen: true,
										width: 500,
										modal: false,
										buttons: {
											Ok: function() {
												$( this ).dialog( "close" );
											}
										}
									});
									
									clicked_obj.switchClass( "ui-state-default", "ui-state-error", 1000 );
									clicked_obj.switchClass( "ui-state-highlight", "ui-state-error", 1000 );
									document.getElementById("check_source_db_text").innerHTML = "Check Connection";
									clicked_obj.closest('ul').find('input').val('0');
									clicked_obj.button();
									
									if( ( $('input:radio[name=copy_stx]:checked').val() == 1 ) && ( clicked_obj.attr("id") == "check_source_db"  ) )
									{
										$( "#db_xray_ok" ).val('0');
										$( "#check_xray_db" ).switchClass( "ui-state-default", "ui-state-error", 1000 );
										$( "#check_xray_db" ).switchClass( "ui-state-highlight", "ui-state-error", 1000 );
										document.getElementById("check_xray_db_text").innerHTML = document.getElementById("check_source_db_text").innerHTML;
									}

								}
								Check_DB_Form_OK();

						 },
				error:   function()
						 {
								alert("Could not connect to MySQL Server. Please check the information and try again. \n\n Invalid response from validation script.");
								document.getElementById("source_db_error_msg").innerHTML = "Could not connect to database. Please check the information and try again. \n\n Invalid response from validation script.";
								
								$( "#db_setup_error_dialog" ).dialog({
									autoOpen: true,
									width: 500,
									modal: true,
									buttons: {
										Ok: function() {
											$( this ).dialog( "close" );
										}
									}
								});
							
								clicked_obj.switchClass( "ui-state-default", "ui-state-error", 1000 );
								clicked_obj.switchClass( "ui-state-highlight", "ui-state-error", 1000 );
								clicked_obj.button();
								
								document.getElementById("check_source_db_text").innerHTML = "Check Connection";
								$( "#db_source_ok" ).val('0');
								
								if( ( $('input:radio[name=copy_stx]:checked').val() == 1 ) && ( clicked_obj.attr("id") == "check_source_db"  ) )
								{
									$( "#db_xray_ok" ).val('0');
									$( "#check_xray_db" ).switchClass( "ui-state-default", "ui-state-error", 1000 );
									$( "#check_xray_db" ).switchClass( "ui-state-highlight", "ui-state-error", 1000 );
									document.getElementById("check_xray_db_text").innerHTML = document.getElementById("check_source_db_text").innerHTML;
								}
								Check_DB_Form_OK();
						 }
		}); // AJAX
		
	});
	
	
	function Check_DB_Form_OK()
	{
		if( $( "#db_source_ok" ).val()=="1" && $( "#db_xray_ok" ).val()=="1" )
		{
			$( "#config_db_submit" ).val('Save & Continue');
			$( "#config_db_submit" ).switchClass( "ui-state-default", "ui-state-highlight", 1000 );
			$( "#config_db_submit" ).switchClass( "ui-state-error", "ui-state-highlight", 1000 );
			$( "#config_db_submit" ).button({ disabled: false });
		}
		else
		{
			$( "#config_db_submit" ).val('Please Check Connection To Continue');
			$( "#config_db_submit" ).switchClass( "ui-state-default", "ui-state-error", 1000 );
			$( "#config_db_submit" ).switchClass( "ui-state-highlight", "ui-state-error", 1000 );
			$( "#config_db_submit" ).button({ disabled: true });
		}
	}
////////////////////////
// Auth Settings
////////////////////////
	$( "#auth_mode_radio" ).buttonset();

	var icons = {
		header: "ui-icon-cancel",
		headerSelected: "ui-icon-check"
	};
	$( "#accordion" ).accordion({
		active: <?php switch($GLOBALS['config']['auth']['mode'])
				{
					default: case "username": echo "0"; break;
					case "password": echo "1"; break;
					case "none": echo "2"; break;
				}?>,
		icons: icons,
		autoHeight: false,
		collapsible: false,
		event: false
	});


	$('#auth_mode_radio1').click(function()
		{
			$('#show_authmode_username').fadeIn("slow");			
			$('#show_authmode_password').fadeOut("slow");
			$('#show_authmode_none').fadeOut("slow");
			$( "#accordion" ).accordion( {active: 0, icons: icons} );
			
		});
	$('#auth_mode_radio2').click(function()
		{
			$('#show_authmode_password').fadeIn("slow");
			$('#show_authmode_username').fadeOut("slow");
			$('#show_authmode_none').fadeOut("slow");
			$( "#accordion" ).accordion( {active: 1, icons: icons} );
		});
	$('#auth_mode_radio3').click(function()
		{
			$('#show_authmode_none').fadeIn("slow");
			$('#show_authmode_username').fadeOut("slow");
			$('#show_authmode_password').fadeOut("slow");
			$( "#accordion" ).accordion( {active: 2, icons: icons} );
		});
	
	function split( val ) {
		return val.split( /,\s*/ );
	}
	function extractLast( term ) {
		return split( term ).pop();
	}

	$("#auth_admin_usernames, #auth_mod_usernames, #auth_user_usernames").bind( "keydown", function( event )
		{
			if ( event.keyCode === $.ui.keyCode.TAB && $( this ).data( "autocomplete" ).menu.active )
			{
				event.preventDefault();
			}
		}).autocomplete(
	{
		source: function( request, response )
		{
			$.getJSON(
				"inc/live/search_usernames.php",
				{ term: extractLast( request.term )	},
				response );
		},
		search: function()
		{
			// custom minLength
			var term = extractLast( this.value );
			if ( term.length < 2 ) {
				return false;
			}
		},
		focus: function() {
			// prevent value inserted on focus
			return false;
		},
		select: function( event, ui )
		{
			var terms = split( this.value );
			// remove the current input
			terms.pop();
			// add the selected item
			terms.push( ui.item.value );
			// add placeholder to get the comma-and-space at the end
			terms.push( "" );
			this.value = terms.join( ", " );
			return false;
		}
	});
	
	$("#auth_mode_radio, #auth_admin_usernames, #auth_mod_usernames, #auth_user_usernames, #auth_admin_password, #auth_mod_password, #auth_user_password").change(function()
	{
		Check_Auth_Form_OK()
	});
	
	/*
    $("#auth_admin_usernames, #auth_mod_usernames, #auth_user_usernames, #auth_admin_password, #auth_mod_password, #auth_user_password").keydown(function(event) {
        // Allow: backspace, delete, tab and escape
        if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || 
             // Allow: Ctrl+A
            (event.keyCode == 65 && event.ctrlKey === true) || 
             // Allow: home, end, left, right
            (event.keyCode >= 35 && event.keyCode <= 39)) {
                 // let it happen, don't do anything
                 return;
        }
        else {
            // Ensure that it is a number and stop the keypress
            if ((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
                event.preventDefault(); 
            }   
        }
    });
	*/

	
	function Check_Auth_Form_OK()
	{
		auth_mode = $('input[name=auth_mode]:checked').val();
		auth_form_ok = false;
		
		//alert( auth_mode );

		if( auth_mode == "username" )
		{
			if( $("#auth_admin_usernames").val()!="")
			{
				auth_form_ok = true;
			}
		}
		if( auth_mode == "password" )
		{
			if( $("#auth_admin_password").val()!="" && $("#auth_admin_password").val()!="" && $("#auth_admin_password").val()!="" )
			{
				auth_form_ok = true;
			}
		}
		if( auth_mode == "none" )
		{
			auth_form_ok = true;
		}
		
		
		if( auth_form_ok )
		{
			$( "#config_auth_submit" ).val('Save & Continue');
			$( "#config_auth_submit" ).switchClass( "ui-state-default", "ui-state-highlight", 1000 );
			$( "#config_auth_submit" ).switchClass( "ui-state-error", "ui-state-highlight", 1000 );
			$( "#config_auth_submit" ).button({ disabled: false });
		}
		else
		{
			$( "#config_auth_submit" ).val('Please Complete All Fields');
			$( "#config_auth_submit" ).switchClass( "ui-state-default", "ui-state-error", 1000 );
			$( "#config_auth_submit" ).switchClass( "ui-state-highlight", "ui-state-error", 1000 );
			$( "#config_auth_submit" ).button({ disabled: true });
		}
	}
	

////////////////////////
// World Settings
////////////////////////
	
	$( "#scanworlds" ).button();

	worldtoggle_array = new Array();
	worldalias_array = new Array();
<?php if(count($Worlds_all_array)>0){ foreach($Worlds_all_array as $world_index => $world_item){ ?>
	$( "#worldtoggle_<?php echo $world_item['worldid']?>" ).button();
	worldtoggle_array.push( $( "#worldtoggle_<?php echo $world_item['worldid']?>" ) );
	worldalias_array.push( $( "#worldalias_<?php echo $world_item['worldid']?>" ) );
	//alert("#worldalias_<?php echo $world_item['worldid']?>");
<?php } } ?>

	$( 'input[id*="worldtoggle_"]' ).click(function()
	{
		var clicked_obj = $(this);
		if(clicked_obj.attr("checked") == "checked")
		{
			$(this).button({ label: "ON" });
			//clicked_obj.parent().find('label').text("ON");
		} else
		{
			$(this).button({ label: "OFF" });
			//clicked_obj.parent().find('label').text("OFF");
		}
		
		// Validate input
		Check_Worlds_Form_OK();

	});
	
	$( 'input[id*="worldalias_"]' ).change(function()
	{
		Check_Worlds_Form_OK();
	});

	function Check_Worlds_Form_OK()
	{
		world_toggle_ok = false;
		world_alias_ok = true;
		jQuery.each(worldtoggle_array, function(worldtoggle_key, worldtoggle_item)
		{
			if($(worldtoggle_item).attr("checked") == "checked")
			{
				world_toggle_ok = true;
			}
		})
		
		jQuery.each(worldalias_array, function(worldalias_key, worldalias_item)
		{
			//alert( worldalias_item.val() );
			if( worldalias_item.val() == "")
			{
				world_alias_ok = false;
			}
		})
		
		if(world_toggle_ok && world_alias_ok)
		{
			$( "#config_worlds_submit" ).switchClass( "ui-state-default", "ui-state-highlight", 1000 );
			$( "#config_worlds_submit" ).switchClass( "ui-state-error", "ui-state-highlight", 1000 );
			$( "#config_worlds_submit" ).button({disabled: false});
		}
		else
		{
			$( "#config_worlds_submit" ).switchClass( "ui-state-default", "ui-state-error", 1000 );
			$( "#config_worlds_submit" ).switchClass( "ui-state-highlight", "ui-state-error", 1000 );
			$( "#config_worlds_submit" ).button({disabled: true});
		}
	}
	
	Check_DB_Form_OK();
	Check_Auth_Form_OK();
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
<table width="800" border="0" class="borderblack_greybg_light_thick">
  <tr>
    <td><form id="form_setup_config" name="form_setup_config" method="post" action="">
      <table width="100%" border="0">
        <tr>
          <td><table width="100%" height="90" border="0" cellpadding="0" cellspacing="0" class="xray_header">
            <tr>
              <td><?php if(!$_SESSION['first_setup']){ ?><a href="xray.php" target="_self"><img src="img/null15.gif" width="500" height="80" hspace="0" vspace="0" border="0" /></a><?php } ?></td>
            </tr>
          </table></td>
        </tr>
        <tr>
          <td><table width="100%" border="0" class="borderblack_greybg_dark_thick ui-corner-all" style="background:url(img/clusters.jpg)">
            <tr>
              <td align="center"><h1>X-Ray Detective First Time Setup</h1></td>
            </tr>
          </table></td>
        </tr>
        <tr>
          <td>
            <?php if($config_error!="" || $config_success!=""){ ?>
            <table width="100%" border="0" cellpadding="20" class="borderblack_greybg_dark_thick ui-corner-all">
              <tr>
                <td><?php if($config_error!=""){ ?>
                  <table width="100%" border="0" cellpadding="20" class="ui-widget ui-state-error ui-corner-all">
                    <tr>
                      <td align="center" valign="middle"><strong><?php echo $config_error; ?>
                        </h1>
                      </strong></td>
                      </tr>
                    <tr>
                      <td align="center" valign="middle"><?php if($_SESSION['auth_is_valid']){ ?>[ <a href="setup.php">Start Over</a> ]<?php } ?><?php if($_SESSION['auth_type']!="ip"){ ?>[ <a href="xray.php">Cancel</a> ]<?php } ?></td>
                      </tr>
                    </table>
                  <?php } ?>
                  <?php if($config_success!=""){ ?>
                  <table width="100%" border="0" cellpadding="20" class="ui-widget ui-state-highlight ui-corner-all">
                    <tr>
                      <td align="center" valign="middle"><strong><?php echo $config_success; ?>
                        </h1>
                      </strong></td>
                      </tr>
                    <tr>
                      <td align="center" valign="middle"><?php if($config_error==""){ ?>
                        <?php if($setup_stage_tab==$tab_count && $setup_submit_ok){?>
                        [ <a href="xray.php">Let's Get Started</a> ]
                        <?php } ?></td>
                      <?php } ?>
                      </tr>
                    </table>
                  <?php } ?></td>
              </tr>
              </table>
            <?php } ?></td>
        </tr>
<?php if($_SESSION['auth_is_valid']){ ?>
        <tr>
          <td><div id="tabs" class="xray-dark">
            <ul>
              <li ><a href="#tabs-1">Database</a></li>
              <li><a href="#tabs-2">Authentication</a></li>
              <li><a href="#tabs-3">Worlds</a></li>
            </ul>
            <div id="tabs-1">
<table width="100%" border="0">
  <tr>
                  <td><?php if(true || $setup_stage == 0){ ?>
                    <table width="100%" border="0" class="borderwhite_greybg_dark_thick ui-corner-all">
                      <tr>
                        <td>
                          <h1>Source Database</h1>
                          <div class="ui-widget-content ui-corner-all" style="margin: 10; padding: 10"><span class="ui-icon ui-icon-info"></span>This database contains the tables of your Logging Software. Your original logs will never be modified by X-Ray Detective.</div>
                          <br />
                          <table width="100%" border="0" class="borderblack_greybg_light_thick ui-corner-all">
                            <tr>
                              <th width="27%" align="left" scope="row">Logging Software</th>
                              <td width="68%"><div id="logging_radio">
                                <input name="db_type" type="radio" id="radio1" value="LB" checked="checked" />
                                <label for="radio1">LogBlock</label>
                                <input name="db_type" type="radio" id="radio2" value="GD" class="radio-disable" />
                                <label for="radio2">Guardian</label>
                                <input name="db_type" type="radio" id="radio3" value="CUSTOM" class="radio-disable" />
                                <label for="radio3">Custom</label>
                              </div></td>
                              <td width="5%">&nbsp;</td>
                            </tr>
                          </table>
                          <br />
                          <table width="100%" border="0" cellpadding="5" class="borderblack_greybg_light_thick ui-corner-all">
                            <tr>
                              <th width="42%" align="right" scope="row">Hostname / IP Address</th>
                              <td width="28%"><label for="textfield"></label>
                                <input name="db_source_host" type="text" id="db_source_host" value="<?php echo $db['x_host']; ?>" /></td>
                              <td width="30%">Ex: localhost</td>
                              </tr>
                            <tr>
                              <th align="right" scope="row">Database Name</th>
                              <td><input name="db_source_base" type="text" id="db_source_base" value="<?php echo $GLOBALS['config_db']['db_source']['base']; ?>" /></td>
                              <td>Ex: minecraft</td>
                              </tr>
                            <tr>
                              <th align="right" scope="row">Access Username</th>
                              <td><input type="text" name="db_source_user" id="db_source_user" value="<?php echo $GLOBALS['config_db']['db_source']['user']; ?>" /></td>
                              <td>Ex: root</td>
                              </tr>
                            <tr>
                              <th align="right" scope="row">Access Password</th>
                              <td><input name="db_source_pass" type="text" id="db_source_pass" value="<?php echo $GLOBALS['config_db']['db_source']['pass']; ?>" /></td>
                              <td>Ex: password</td>
                              </tr>
                            <tr>
                              <th align="left" scope="row">&nbsp;</th>
                              <td colspan="2"><ul class="ui-widget icon-collection xray-dark">
                                <li class="ui-state-default ui-corner-all" title="Add World" id="check_source_db"><span id="check_source_db_icon" class="ui-icon ui-icon-signal-diag"></span><span class="text" id="check_source_db_text">Check Connection </span>
                                  <input type="hidden" name="db_source_ok" id="db_source_ok" value="0" />
                                  </li>
                              </ul></td>
                              </tr>
                          </table></td>
                      </tr>
                    </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><table width="100%" border="0" class="borderwhite_greybg_dark_thick ui-corner-all">
                    <tr>
                      <td><h1>X-Ray Database</h1>
                        <div class="ui-widget-content ui-corner-all" style="margin: 10; padding: 10"><span class="ui-icon ui-icon-info"></span>This database is where the X-Ray Detective tables will be created.</div>
                        <br />
                        <table width="100%" border="0" class="borderblack_greybg_light_thick ui-corner-all">
                          <tr>
                            <th width="31%" align="left" scope="row">Use Same Database?</th>
                            <td width="62%"><div id="copy_source_to_xray_radio">
                              <input name="copy_stx" type="radio" id="copy_stx_radio1" value="1" <?php if(true || $db['same']){ ?>checked="checked"<?php } ?> />
                              <label for="copy_stx_radio1">Yes</label>
                              <input name="copy_stx" type="radio" id="copy_stx_radio2" value="0"<?php if(false && !$db['same']){ ?>checked="checked"<?php } ?> disabled="disabled" />
                              <label for="copy_stx_radio2">No</label>
                            </div></td>
                            <td width="7%">&nbsp;</td>
                          </tr>
                        </table>
                        <br />
                        <table width="100%" border="0" cellpadding="5" class="borderblack_greybg_light_thick ui-corner-all">
                          <tr>
                            <th align="right" scope="row">Hostname / IP Address</th>
                            <td><label for="textfield"></label>
                              <input name="db_xray_host" type="text" disabled="disabled" id="db_xray_host" value="<?php echo $db['x_host']; ?>" /></td>
                            <td width="30%">Ex: localhost</td>
                            </tr>
                          <tr>
                            <th align="right" scope="row">Database Name</th>
                            <td><input name="db_xray_base" type="text" disabled="disabled" id="db_xray_base" value="<?php echo $db['x_base']; ?>" /></td>
                            <td>Ex: minecraft</td>
                            </tr>
                          <tr>
                            <th align="right" scope="row">Access Username</th>
                            <td><input name="db_xray_user" type="text" disabled="disabled" id="db_xray_user" value="<?php echo $db['x_user']; ?>" /></td>
                            <td>Ex: root</td>
                            </tr>
                          <tr>
                            <th align="right" scope="row">Access Password</th>
                            <td><input name="db_xray_pass" type="text" disabled="disabled" id="db_xray_pass" value="<?php echo $db['x_pass']; ?>" /></td>
                            <td>Ex: password</td>
                            </tr>
                          <tr>
                            <th align="left" scope="row">&nbsp;</th>
                            <td><ul class="ui-widget">
                              <li class="ui-state-default ui-corner-all" title="Add World" id="check_xray_db"><span class="ui-icon ui-icon-signal-diag"></span><span class="text" id="check_xray_db_text">Check Connection </span>
                                <input type="hidden" name="db_xray_ok" id="db_xray_ok" value="0" />
                                </li>
                            </ul></td>
                            <td>&nbsp;</td>
                            </tr>
                        </table>
                        <div id="dialog" title="Connection Error" style="display: none">
                          <p>&nbsp;</p>
                          <div class="ui-state-error ui-corner-all" style="padding: 20"><span id="xray_db_error_main"><strong>Main Error</strong></span>
                            </p>
                          </div>
                          <p>&nbsp;</p>
                          <div class="ui-widget-content ui-corner-all" style="padding: 20"><span id="xray_db_error_specific"><strong>ERROR:</strong>Main Erro</span></div>
                        </div></td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><table width="100%" border="0" cellpadding="20" class="borderwhite_greybg_dark_thick ui-corner-all" >
                    <tr>
                      <td align="right"><input type="submit" name="config_db_submit" id="config_db_submit" value="Please Check Connection To Continue" disabled="disabled" class="xray-dark ui-state-error" /></td>
                    </tr>
                  </table>
                    <?php } ?></td>
                </tr>
            </table>
            </div>
            <div id="tabs-2">
<table width="100%" border="0">
  <tr>
                  <td><?php if(true || $setup_stage == 2){ ?>
                    <table width="100%" border="0" class="borderwhite_greybg_dark_thick ui-corner-all">
                      <tr>
                        <td><h1>Authentication</h1>
                          <div class="ui-widget-content ui-corner-all" style="margin: 10; padding: 10"> <span class="ui-icon ui-icon-info"></span>Select a method of granting access to the X-Ray Detection script.</div>
                          <table width="100%" border="0" class="borderblack_greybg_light_thick ui-corner-all">
                            <tr>
                              <th width="31%" align="left" scope="row">Authentication Mode</th>
                              <td width="62%"><div id="auth_mode_radio">
                                <input name="auth_mode" type="radio" id="auth_mode_radio1" value="username" <?php if($GLOBALS['config']['auth']['mode']=="username"){ ?>checked="checked"<?php } ?> />
                                <label for="auth_mode_radio1">Username</label>
                                <input name="auth_mode" type="radio" id="auth_mode_radio2" value="password" <?php if($GLOBALS['config']['auth']['mode']=="password"){ ?>checked="checked"<?php } ?> />
                                <label for="auth_mode_radio2">Password</label>
                                <input name="auth_mode" type="radio" id="auth_mode_radio3" value="none" <?php if($GLOBALS['config']['auth']['mode']=="none"){ ?>checked="checked"<?php } ?> />
                                <label for="auth_mode_radio3">None</label>
                                </div></td>
                              <td width="7%">&nbsp;</td>
                              </tr>
                            </table></td>
                      </tr>
                    </table>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td><br />
                          <table width="100%" border="0" cellspacing="0" class="border_black_thick ui-corner-all bg_black">
                          <tr>
                              <td><div id="accordion" class="xray-whiteborder">
                                <h3><a href="#" id="section_authmode_password">Username Authentication</a></h3>
                                <div>
                                  <table width="100%" border="0" class="borderblack_greybg_light_thick ui-corner-all" id="show_authmode_username">
                                    <tr>
                                      <td class="ui-widget-header">Configure Usernames</td>
                                      </tr>
                                    <tr>
                                      <td><div class="ui-widget-content ui-corner-all" style="margin: 10; padding: 10"> <span class="ui-icon ui-icon-info"></span>Users are granted access automatically after logging into your Minecraft server with a username specified below.<br />
                                        </div>
                                        <div class="ui-state-error ui-corner-all" style="margin: 10; padding: 10"> <span class="ui-icon ui-icon-alert"></span>Do not use this method if your server is a cracked server: <em>online-mode=false</em> in server config.</div>
                                        <table width="100%" border="0" cellpadding="5">
                                          <tr>
                                            <th align="right" scope="row">Admin Usernames</th>
                                            <td><div class="ui-widget">
                                              <input name="auth_admin_usernames" id="auth_admin_usernames" value="<?php echo $GLOBALS['config']['auth']['admin_usernames']; ?>" size="50" />
                                              </div></td>
                                            </tr>
                                          <tr>
                                            <th align="right" scope="row">Moderator Usernames</th>
                                            <td><div class="ui-widget">
                                              <input name="auth_mod_usernames" id="auth_mod_usernames" value="<?php echo $GLOBALS['config']['auth']['mod_usernames']; ?>" size="50" />
                                              </div></td>
                                            </tr>
                                          <tr>
                                            <th align="right" scope="row">User  Usernames</th>
                                            <td><div class="ui-widget">
                                              <input name="auth_user_usernames" id="auth_user_usernames" value="<?php echo $GLOBALS['config']['auth']['user_usernames']; ?>" size="50" />
                                              </div></td>
                                            </tr>
                                          </table></td>
                                      </tr>
                                    </table>
                                </div>
                                <h3><a href="#" id="section_authmode_password">Password Authentication</a></h3>
                                <div>
                                  <table width="100%" border="0" class="borderblack_greybg_light_thick ui-corner-all" id="show_authmode_password">
                                    <tr>
                                      <td class="ui-widget-header">Configure Password</td>
                                      </tr>
                                    <tr>
                                      <td><div class="ui-widget-content ui-corner-all" style="margin: 10; padding: 10"> <span class="ui-icon ui-icon-info"></span>Anyone may login to the script if they enter the correct password for their user type.<br />
                                        <br />
                                        Note: Each user does not receieve their own password. There is only one valid password per level. </div>
                                        <br />
                                        <table width="100%" border="0">
                                          <tr>
                                            <th align="right" scope="row">Admin Password</th>
                                            <td><label for="textfield"></label>
                                              <input type="text" name="auth_admin_password" id="auth_admin_password" value="<?php echo $GLOBALS['config']['auth']['admin_password']; ?>" /></td>
                                            </tr>
                                          <tr>
                                            <th align="right" scope="row">Moderator Password</th>
                                            <td><input type="text" name="auth_mod_password" id="auth_mod_password" value="<?php echo $GLOBALS['config']['auth']['mod_password']; ?>" /></td>
                                            </tr>
                                          <tr>
                                            <th align="right" scope="row">User  Password</th>
                                            <td><input type="text" name="auth_user_password" id="auth_user_password" value="<?php echo $GLOBALS['config']['auth']['user_password']; ?>" /></td>
                                            </tr>
                                          </table></td>
                                      </tr>
                                    </table>
                                </div>
                                <h3><a href="#" id="section_authmode_password">No Authentication</a></h3>
                                <div>
                                  <table width="100%" border="0" class="borderblack_greybg_light_thick ui-corner-all" id="show_authmode_none">
                                    <tr>
                                      <td class="ui-widget-header">Description</td>
                                      </tr>
                                    <tr>
                                      <td><div class="ui-widget-content ui-corner-all" style="margin: 10; padding: 10"> <span class="ui-icon ui-icon-info"></span>Everyone is granted User status. No one may make changes at all.<br />
                                        <br />
                                        Only computers accessing the script from the <strong>Failsafe IPs</strong> specified below will be given Administrator status.<br />
                                        <br />
                                        </div>
                                        <table width="100%" border="0" cellpadding="20">
                                          <tr>
                                            <td>No further configuration required for this option.</td>
                                            </tr>
                                          </table></td>
                                      </tr>
                                    </table>
                                </div>
                                </div></td>
                              </tr>
                        </table>
                          <br />
                          <table width="100%" border="0" class="borderwhite_greybg_norm_thick ui-corner-all">
                            <tr>
                              <td class="ui-widget-header"><strong>Failsafe IPs</strong></td>
                              </tr>
                            <tr>
                              <td><div class="ui-widget-content ui-corner-all" style="margin: 10; padding: 10"> 
                                <p><span class="ui-icon ui-icon-info"></span>In order to prevent  you from being locked out of the script if your config gets messed up or you somehow get removed from the admin list, your IP will be used to give you Administrator status.<br />
                                  <br />
                                  The IP <em>127.0.0.1</em> will always be given administrator access, even if it is not included in this list. This allows you to always login if you are accessing the script from the same computer that the script is hosted on.<br />
                                  <br />
                                  Your current IP is:</p>
                                <ul>
                                  <li><?php echo $_SERVER['REMOTE_ADDR']; ?></li>
                                </ul>
                              </div>
                                <table width="100%" border="0">
                                  <tr>
                                    <th scope="row">IP List</th>
                                    <td><input name="auth_failsafe_ips" type="text" id="auth_failsafe_ips" size="50" value="<?php echo $GLOBALS['config']['auth']['failsafe_ips']; ?>" /></td>
                                    </tr>
                                  </table></td>
                              </tr>
                            </table>
                          <div id="dialog" title="Connection Error" style="display: none">
                            <p>.</p>
                            <div class="ui-state-error ui-corner-all" style="padding: 20"><span id="xray_db_error_main"><strong>Main Error</strong></span>
                              </p>
                              </div>
                            <p>.</p>
                            <div class="ui-widget-content ui-corner-all" style="padding: 20"><span id="xray_db_error_specific"><strong>ERROR:</strong>Main Erro</span></div>
                            </div></td>
                      </tr>
                </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><table width="100%" border="0" cellpadding="20" class="borderwhite_greybg_dark_thick ui-corner-all" >
                    <tr>
                      <td align="right"><input type="submit" name="config_auth_submit" id="config_auth_submit" value="Please Complete All Fields" disabled="disabled" class="ui-state-error" /></td>
                    </tr>
                  </table>
                    <?php } ?></td>
                </tr>
            </table>
            </div>
            <div id="tabs-3">
              <table width="100%" border="0">
                <tr>
                  <td><table width="100%" border="0" class="borderwhite_greybg_dark_thick ui-corner-all">
                    <tr>
                      <td><h1>Worlds</h1>
                        <div class="ui-widget-content ui-corner-all" style="margin: 10; padding: 10"><span class="ui-icon ui-icon-info"></span>Select the worlds that you want X-Ray Detective to compile statistics for.<br />
                          <br />
                          X-Ray Detective only works on Normal type worlds. It will not work if the world type is Nether, The End, Skylands, Flat, etc.<br />
                          <br />
                          If the world you are looking for is not listed, check to make sure your Logging Software is tracking that world.
                        </div>
                        <?php if(count($Worlds_all_array)==0){?>
                        <div class="ui-widget-content ui-state-error ui-corner-all" style="margin: 10; padding: 10">
                          <p><span class="ui-icon ui-icon-alert"></span>Could not find any worlds in the Source Database you specified.<br />
                            <br />
                            Possible causes:</p>
                          <ul>
                            <li>The Source Database is incorrect.</li>
                            <li>Your logging software has not been configured to monitor any worlds yet.</li>
                          </ul>
                        </div>
                        <?php } if(count($Worlds_enabled_array)==0){ ?>
                        <div class="ui-widget-content ui-state-error ui-corner-all" style="margin: 10; padding: 10"><span class="ui-icon ui-icon-alert"></span>There are no worlds currently being monitored by X-Ray detective.<br />
                          Please select at least one.</div>
                        <?php } ?>
                        <table width="100%" border="0">
                          <tr>
                            <td><table width="100%" border="0" cellpadding="0" cellspacing="0" class="borderblack_greybg_light_thick ui-corner-all">
                              <tr class="bg_black">
                                <td><table width="100%" border="0" class="ui-widget-header ui-corner-top">
                                  <tr>
                                      <td><strong>World Selection</strong></td>
                                    </tr>
                                </table></td>
                              </tr>
                              <tr>
                                <td><table width="100%" border="0">
                                  <tr class="bordernone_greybg_norm">
                                    <th align="right" nowrap="nowrap" scope="col">World Name</th>
                                    <th nowrap="nowrap" scope="col">Toggle</th>
                                    <th align="left" nowrap="nowrap" scope="col">World Alias</th>
                                    </tr>
                                  <?php if(count($Worlds_all_array)>0){ foreach($Worlds_all_array as $world_index => $world_item){ ?>
                                  <tr>
                                    <td align="right"><strong><?php echo $world_item['worldname']; ?></strong></td>
                                    <td align="center"><input type="checkbox" name="worldtoggle_<?php echo $world_item['worldid']; ?>" id="worldtoggle_<?php echo $world_item['worldid']; ?>"<?php if($world_item['enabled']){?> checked="checked"<?php } ?> />
                                      <label for="worldtoggle_<?php echo $world_item['worldid']; ?>"><?php echo FixOutput_Bool($world_item['enabled'],"ON","OFF"); ?></label></td>
                                    <td><input type="text" value="<?php echo $world_item['worldalias']; ?>" name="worldalias_<?php echo $world_item['worldid']; ?>" id="worldalias_<?php echo $world_item['worldid']; ?>" /></td>
                                    </tr>
                                  <?php } } ?>
                                  <tr>
                                    <td>&nbsp;</td>
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
                  <td><br />
                    <table width="100%" border="0" cellpadding="20" class="borderwhite_greybg_dark_thick ui-corner-all" >
                    <tr>
                      <td align="right"><input type="submit" name="config_worlds_submit" id="config_worlds_submit" value="Save Changes" <?php if(count($Worlds_enabled_array)==0){ ?> disabled="disabled" class="ui-state-error"<?php } else { ?>class="ui-state-highlight"<?php } ?> /></td>
                    </tr>
                  </table></td>
                </tr>
              </table>
            </div>
          </div><input name="form" type="hidden" value="form_config_setup" /></td>
        </tr>
        <?php } ?>
        <tr>
          <td>&nbsp;</td>
        </tr>
      </table>
    </form></td>
  </tr>
</table>
<div id="db_setup_error_dialog" title="Connection Error" >
  <p>&nbsp;</p>
  <div class="ui-state-error ui-corner-all" style="padding: 20"><span id="source_db_error_main"><strong>Main Error</strong></span>
    </p>
  </div>
  <p>&nbsp;</p>
  <div class="ui-widget-content ui-corner-all" style="padding: 20"><span id="source_db_error_specific"><strong>ERROR:</strong>Main Error</span>
    </p>
  </div>
</div>
<p>
</body>