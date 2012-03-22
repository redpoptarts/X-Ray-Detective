<?php 

// if($_SERVER['REMOTE_ADDR']=="173.74.253.9"){ $_SERVER['REMOTE_ADDR'] = "24.52.90.166"; } // Drewman
if($_SERVER['REMOTE_ADDR']=="173.74.253.9"){ $_SERVER['REMOTE_ADDR'] = "192.168.1.7"; }
//if($_SERVER['REMOTE_ADDR']=="173.74.253.9"){ $_SERVER['REMOTE_ADDR'] = "127.0.0.1"; }

if($_SESSION['auth_is_valid']==true)
{
	echo "You are logged in!<br>";
	echo "User ID: " . $_SESSION['viewer_id'] . "<br>";
	echo "User Name: " . $_SESSION['viewer_name'] . "<br>";
	echo "Password: " . $_SESSION['viewer_password'] . "<br>";
	
}
else
{
	session_start();
	mysql_select_db($db_name, $db_resource);
	$query_IP_Users = sprintf("SELECT * FROM `lb-players` WHERE ip LIKE %s ORDER BY playername ASC", GetSQLValueString("%" . $_SERVER['REMOTE_ADDR'] . "%", "text"));
	$res_IP_Users = mysql_query($query_IP_Users, $db_resource) or die(mysql_error());
	$totalRows_IP_Users = mysql_num_rows($res_IP_Users);
	
	// VALIDATE IP
	$ip_valid = false;
	
	if( $totalRows_IP_Users > 0 )
	{
		while(($IP_Users_list[] = mysql_fetch_assoc($res_IP_Users)) || array_pop($IP_Users_list));
	}
	
	if($_POST['form']=="loginform")
	{
		if($auth_mode == "username")
		{
			// VALIDATE IP
			$ip_valid = false;
			
			if( $totalRows_IP_Users > 0 )
			{
				//$playerid = $IP_Users_list[0]["playerid"];
				
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
							$_SESSION["auth_type"] = $auth_mode;
							$_SESSION["account"]=$auth_test_item;
							$_SESSION['auth_is_valid'] = true;
						}
					}
				}
			}
			else
			{
				$login_error .= "ERROR: You do not have access to this page!<br>";
				$auth_valid = false;
				$_SESSION['auth_is_valid'] = false;
			}	
	
		}
		elseif($auth_mode == "password")
		{
			if($_POST['login_password']==""){$login_error .= "ERROR: Password cannot be blank!<br>";}
		
			if(!strnatcasecmp($auth_admin_password,$_POST['login_password']))
			{
				//echo "AUTH: VALID ADMINISTRATOR!<BR>";
				$_SESSION["auth_admin"] = true; $_SESSION["auth_level"] = "Administrator";
			}
			elseif(!strnatcasecmp($auth_mod_password,$_POST['login_password']))
			{
				//echo "AUTH: VALID MODERATOR!<BR>";
				$_SESSION["auth_mod"] = true; $_SESSION["auth_level"] = "Moderator";
			}
			elseif(!strnatcasecmp($auth_user_password,$_POST['login_password']))
			{
				//echo "AUTH: VALID USER!<BR>";
				$_SESSION["auth_user"] = true;  $_SESSION["auth_level"] = "User";
			}

			if($_SESSION["auth_admin"] || $_SESSION["auth_mod"] || $_SESSION["auth_user"])
			{
				$_SESSION["auth_type"] = $auth_mode;
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
}

if($_POST['form']=="logoutform" && $_POST['Submit']=="Logout")
{
	session_unset();
	$logout_success .= "You have been logged off successfully.<br>";
}
?>