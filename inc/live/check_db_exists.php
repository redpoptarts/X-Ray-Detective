<?php
require_once('../core_database.php');

/*
if(!isset($_POST['type']))
{
	$_POST['type'] = "LB";
	$_POST['host'] = "mysql.maedoria.com";
	$_POST['base'] = "minecraft";
	$_POST['user'] = "username";
	$_POST['pass'] = "password";
	
}*/

if(isset($_POST['type']) && isset($_POST['host']) && isset($_POST['base']) && isset($_POST['user']) && isset($_POST['pass']) )
{
    //print_r($_POST);
	//json_encode($_POST);
	
	$connect = @mysql_connect($_POST['host'],$_POST['user'],$_POST['pass']) or die(json_encode(array('error' => true, 'message' => mysql_error() )));
	if($connect)
	{
		// Get ID of players whose names partially match the search parameter
		$sql_DB_IsValid  = "SHOW DATABASES LIKE '".$_POST['base']."'";
		//echo "SQL QUERY: <BR>" . $sql_DB_IsValid . "<BR>";
		$res_DB_IsValid = @mysql_query($sql_DB_IsValid) or die(json_encode(array('error' => true, 'message' => "SQL connection OK, but an error occurred while looking for database [".$_POST['base']."] ." )));
		if( mysql_num_rows($res_DB_IsValid) > 0 )
		{
			if($_POST['check_logging_table']=="check_source_db")
			{
				switch( trim(strtoupper($_POST['type'])) )
				{
					case "LB":
						$db_table_find = "lb-players"; break;
					case "GD":
						$db_table_find = "gd_players"; break;
					case "HE":
						die(json_encode(array('error' => false, 'message' => "HawkEye (Custom Logger Required) - NOT YET IMPLEMENTED" )));
					case "BB":
						die(json_encode(array('error' => false, 'message' => "BigBrother (Custom Logger Required) - NOT YET IMPLEMENTED" )));
					case "":
						die(json_encode(array('error' => false, 'message' => "LOG TYPE CANNOT BE BLANK." )));
					default:
						die(json_encode(array('error' => false, 'message' => "Custom Logger - NOT YET IMPLEMENTED" )));
					
				}
				
				@mysql_select_db($_POST['base']) or die(json_encode(array('error' => true, 'message' => "SQL connection OK, Database [".$_POST['base']."] exists, but an error occurred when trying to connect to the database: " . mysql_error() )));
				$sql_Logging_IsValid  = "SHOW TABLES LIKE '$db_table_find'";
				//echo "SQL QUERY: <BR>" . $sql_Logging_IsValid . "<BR>";
				$res_Logging_IsValid = @mysql_query($sql_Logging_IsValid) or die(json_encode(array('error' => true, 'message' => "Database connection OK, but an error occurred while checking ".DB_Type_Name($_POST['type'])." installation: " . mysql_error() )));
				if( mysql_num_rows($res_Logging_IsValid) > 0 )
					{ die(json_encode(array('error' => false, 'message' => "HOST OK" ))); }
				else
					{ die(json_encode(array('error' => true, 'message' => "Database connection OK, but could not find ".DB_Type_Name($_POST['type'])." installation." ))); }	
			}
			else
			{
				die(json_encode(array('error' => false, 'message' => "HOST OK" )));
			}
		}
		else
		{ die(json_encode(array('error' => true, 'message' => "SQL connection OK, but could not find database [".$_POST['base']."] ." ))); }





		
	}
	else
	{
		die(json_encode(array('error' => true, 'message' => "HOST BAD" )));
	}
}
else
{
	die(json_encode(array('error' => true, 'message' => "Please complete all fields." )));
}

?>