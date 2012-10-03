<?php 

// if($_SERVER['REMOTE_ADDR']=="173.74.253.9"){ $_SERVER['REMOTE_ADDR'] = "24.52.90.166"; } // Drewman
//if($_SERVER['REMOTE_ADDR']=="173.74.253.9"){ $_SERVER['REMOTE_ADDR'] = "192.168.1.7"; }
if($_SERVER['REMOTE_ADDR']=="173.74.253.9"){ $_SERVER['REMOTE_ADDR'] = "127.0.0.1"; }

function Do_Auth($ip_only=false)
{
	//echo "FIRST SETUP (Config): " . FixOutput_Bool($GLOBALS['config_settings']['settings']['first_setup'], "YES", "NO", "UNDEFINED") . "<BR>";
	
	// Force IP to match Failsafe IPs list if running setup for first time
	if( FixOutput_Bool($GLOBALS['config_settings']['settings']['first_setup'], true, false, true) )
	{
		session_unset(); session_destroy(); session_start();
		$_SESSION['first_setup'] = true;
		$ip_only = true;
	}
	else
	{
		session_start();
		$_SESSION['first_setup'] = false;
	}
	
	// Initialize variables
	if(count($_GET) > 0){ $_POST = $_GET; }
	if(!isset($_POST['form'])){$_POST['form']="";}
	if(!isset($_POST['submit'])){$_POST['submit']="";}
	$IP_Users_list = array(); $login_error = ""; $logout_success = "";
	$_SESSION['auth_is_valid'] = false;
	$_SESSION['first_setup'] = FixOutput_Bool($GLOBALS['config_settings']['settings']['first_setup'], true, false, true);
	
	if(!$ip_only)
	{
		//echo "IP-Only Authentication is OFF.<BR>";
		if($_SESSION['auth_is_valid']==true)
		{
			/*
			echo "You are logged in!<br>";
			echo "User ID: " . $_SESSION['viewer_id'] . "<br>";
			echo "User Name: " . $_SESSION['viewer_name'] . "<br>";
			echo "Password: " . $_SESSION['viewer_password'] . "<br>";
			*/
		}
		else
		{
			Use_DB("source");
			//mysql_select_db($GLOBALS['db']['db_source']['base'], $GLOBALS['db']['s_resource']);
			$query_IP_Users = sprintf("SELECT * FROM `".DB_Type_PlayersTable($GLOBALS['db']['type'])."` WHERE ip LIKE %s ORDER BY playername ASC", GetSQLValueString("%" . $_SERVER['REMOTE_ADDR'] . "%", "text"));
			//echo "SQL[query_IP_Users]: <BR>". $query_IP_Users. "<BR>";
			$res_IP_Users = mysql_query($query_IP_Users, $GLOBALS['db']['s_resource']) or die(mysql_error());
			$totalRows_IP_Users = mysql_num_rows($res_IP_Users);
			
			// VALIDATE IP
			$ip_valid = false;
			
			if( $totalRows_IP_Users > 0 )
			{
				while(($IP_Users_list[] = mysql_fetch_assoc($res_IP_Users)) || array_pop($IP_Users_list));
			} else
			{
				//echo "WARNING: There are no known users with your IP.<BR>";
			}
			
			if($_POST['form']=="loginform")
			{
				//echo "Login form detected...<BR>";
				if($GLOBALS['config_settings']['auth']['mode'] == "username")
				{
					// VALIDATE IP
					$ip_valid = false;
					$_SESSION["auth_admin"] = false; $_SESSION["auth_mod"] = false;	$_SESSION["auth_user"] = false;
					
					if( $totalRows_IP_Users > 0 )
					{
						//$playerid = $IP_Users_list[0]["playerid"];
						$auth_allow_guest_users = FixInput_Bool($auth_allow_guest_users);
		
						$auth_admin_usernames_exploded = explode(",",$GLOBALS['config']['auth']['admin_usernames']); foreach($auth_admin_usernames_exploded as &$input_fix_item){ $input_fix_item = trim($input_fix_item); }
						$auth_mod_usernames_exploded   = explode(",",$GLOBALS['config']['auth']['mod_usernames']); foreach($auth_mod_usernames_exploded as &$input_fix_item){ $input_fix_item = trim($input_fix_item); }
						$auth_user_usernames_exploded  = explode(",",$GLOBALS['config']['auth']['user_usernames']); foreach($auth_user_usernames_exploded as &$input_fix_item){ $input_fix_item = trim($input_fix_item); }
						
						//echo "AUTH_ADMIN_USERNAMES: "; print_r($auth_admin_usernames_exploded); echo "<BR>";
						//echo "AUTH_MOD_USERNAMES: "; print_r($auth_mod_usernames_exploded); echo "<BR>";
						//echo "AUTH_USER_USERNAMES: "; print_r($auth_user_usernames_exploded); echo "<BR>";
						
						foreach($IP_Users_list as $auth_test_item)
						{
							$ip_valid = true;
							if($auth_test_item["playername"] == $_GET['my_username'])
							{
								
								foreach($auth_admin_usernames_exploded as $admin_name_item)
								{
									if(!strnatcasecmp($admin_name_item,$auth_test_item["playername"]))
									{
										//echo "AUTH: VALID ADMINISTRATOR!<BR>";
										$_SESSION["auth_admin"] = true; $_SESSION["auth_level"] = "Administrator"; break;
										$_SESSION["auth_username"] = $auth_test_item["playername"];
									}
								}
								foreach($auth_mod_usernames_exploded as $mod_name_item)
								{
									if(!strnatcasecmp($mod_name_item,$auth_test_item["playername"]))
									{
										//echo "AUTH: VALID MODERATOR!<BR>";
										$_SESSION["auth_mod"] = true; $_SESSION["auth_level"] = "Moderator"; break;
									}
								}
								foreach($auth_user_usernames_exploded as $user_name_item)
								{
									if(!strnatcasecmp($user_name_item,$auth_test_item["playername"]))
									{
										//echo "AUTH: VALID USER!<BR>";
										$_SESSION["auth_user"] = true; $_SESSION["auth_level"] = "User"; break;
									}
								}
								
								if($_SESSION["auth_admin"] || $_SESSION["auth_mod"] || $_SESSION["auth_user"])
								{
									$_SESSION["auth_type"] = $GLOBALS['config_settings']['auth']['mode'];
									$_SESSION["account"]=$auth_test_item;
									$_SESSION['auth_is_valid'] = true;
								}
							}
						}
					}
					else
					{
						$login_error .= "ERROR: You do not have access to this page!<br>";
						$_SESSION['auth_is_valid'] = false;
					}
			
				}
				elseif($GLOBALS['config_settings']['auth']['mode'] == "password")
				{
					if($_POST['login_password']==""){$login_error .= "ERROR: Password cannot be blank!<br>";}
				
					if(!strnatcasecmp($GLOBALS['config']['auth']['admin_password'],$_POST['login_password']))
					{
						//echo "AUTH: VALID ADMINISTRATOR!<BR>";
						$_SESSION["auth_admin"] = true; $_SESSION["auth_level"] = "Administrator";
					}
					elseif(!strnatcasecmp($GLOBALS['config']['auth']['mod_password'],$_POST['login_password']))
					{
						//echo "AUTH: VALID MODERATOR!<BR>";
						$_SESSION["auth_mod"] = true; $_SESSION["auth_level"] = "Moderator";
					}
					elseif(!strnatcasecmp($GLOBALS['config']['auth']['user_password'],$_POST['login_password']))
					{
						//echo "AUTH: VALID USER!<BR>";
						$_SESSION["auth_user"] = true;  $_SESSION["auth_level"] = "User";
					}
		
					if($_SESSION["auth_admin"] || $_SESSION["auth_mod"] || $_SESSION["auth_user"])
					{
						$_SESSION["auth_type"] = $GLOBALS['config_settings']['auth']['mode'];
						$_SESSION["account"]=false;
						$_SESSION['auth_is_valid'] = true;
					}
					else
					{
						$login_error .= "ERROR: Incorrect password!<br>";
						$_SESSION['auth_is_valid'] = false;
					}
				}
			}
			
			if($GLOBALS['config_settings']['auth']['mode'] == "none")
			{
				$_SESSION["auth_user"] = true; $_SESSION["auth_level"] = "Administrator";
				$_SESSION["auth_username"] = NULL;
			}
		}
	}
	
	if(!isset($_SESSION['auth_is_valid']) || !$_SESSION['auth_is_valid'] || $ip_only)
	{
		$_SESSION['auth_is_valid'] = false;
		$auth_failsafe_ips_exploded = explode(",", $GLOBALS['config']['auth']['failsafe_ips']);
		foreach($auth_failsafe_ips_exploded as &$input_fix_item){ $input_fix_item = trim($input_fix_item); }
		array_push($auth_failsafe_ips_exploded, "127.0.0.1","::1");
		
		//echo "FAILSAFE_IPS: "; print_r($auth_failsafe_ips_exploded); echo "<BR>";
	
		foreach($auth_failsafe_ips_exploded as $auth_test_item)
		{
			if($_SERVER['REMOTE_ADDR'] == $auth_test_item)
			{
				$_SESSION["auth_admin"] = true; $_SESSION["auth_level"] = "Administrator";
				$_SESSION["auth_type"] = "ip";
				$_SESSION["account"]=false;
				$_SESSION['auth_is_valid'] = true; break;
			}
		}
	}
	
	if($_POST['form']=="logoutform" && $_POST['Submit']=="Logout")
	{
		session_unset();
		$logout_success .= "You have been logged off successfully.<br>";
		$_SESSION['auth_is_valid'] = false;
		$_SESSION['first_setup'] = FixOutput_Bool($GLOBALS['config_settings']['settings']['first_setup'], true, false, true);
		$_SESSION['IP_Users_List'] = $IP_Users_list;
	}
	
	$GLOBALS['auth']['IP_Users_list'] = $IP_Users_list;
	
	//echo "FIRST SETUP (Session - Final): " . FixOutput_Bool($_SESSION['first_setup'], "YES", "NO", "UNDEFINED") . "<BR>";
	//echo "AUTH VALID (Session - Final): " . FixOutput_Bool($_SESSION['auth_is_valid'], "YES", "NO", "UNDEFINED") . "<BR>";
		
	return array("valid_ips" => $IP_Users_list, "login_error"=> $login_error, "logout_success"=>$logout_success);
}

?>
