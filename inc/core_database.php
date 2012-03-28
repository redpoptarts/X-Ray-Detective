<?php

function Use_DB($choose)
{
	switch($choose)
	{
		case "source":
			mysql_select_db($GLOBALS['db']['s_base'],$GLOBALS['db']['s_resource']);
			mysqli_select_db($GLOBALS['db']['s_link'], $GLOBALS['db']['s_base']);
			break;
		case "xray":
			mysql_select_db($GLOBALS['db']['x_base'],$GLOBALS['db']['x_resource']);
			mysqli_select_db($GLOBALS['db']['x_link'],$GLOBALS['db']['x_base']);
			break;
		default:
			echo "UseDB: Invalid database selection provided. [$choose]<BR>";
	}

}

function GetMySQL_ResultStats($linkid = null){
    $linkid? $strInfo = mysql_info($linkid) : $strInfo = mysql_info();
   
    $return = array();
    ereg("Records: ([0-9]*)", $strInfo, $records);
    ereg("Duplicates: ([0-9]*)", $strInfo, $dupes);
    ereg("Warnings: ([0-9]*)", $strInfo, $warnings);
    ereg("Deleted: ([0-9]*)", $strInfo, $deleted);
    ereg("Skipped: ([0-9]*)", $strInfo, $skipped);
    ereg("Rows matched: ([0-9]*)", $strInfo, $rows_matched);
    ereg("Changed: ([0-9]*)", $strInfo, $changed);
   
    $return['records'] = $records[1];
    $return['duplicates'] = $dupes[1];
    $return['warnings'] = $warnings[1];
    $return['deleted'] = $deleted[1];
    $return['skipped'] = $skipped[1];
    $return['rows_matched'] = $rows_matched[1];
    $return['changed'] = $changed[1];
   
    return $return;
}

function FixInput_Bool($input_item)
{
	if($input_item === true){ return true; } else
	if($input_item === false){ return false; }
	switch( trim( strtolower($input_item) ) )
	{
		case "yes":
		case "on":
		case "true":
		case "1":
			return true;
			break;
		case "no":
		case "off":
		case "false":
		case "0":
		default:
			return false;
			break;
	}

}

function FixOutput_Bool($input_item, $yes_output, $no_output)
{
	if($input_item === true){ return $yes_output; } else
	if($input_item === false){ return $no_output; }
	switch( trim( strtolower($input_item) ) )
	{
		case "enabled":
		case "enable":
		case "yes":
		case "on":
		case "true":
		case "1":
			return $yes_output;
			break;
		case "disabled":
		case "disable":
		case "no":
		case "off":
		case "false":
		case "0":
		default:
			return $no_output;
			break;
	}

}

function DB_Type_Name($db_type)
{
	switch(trim(strtoupper($db_type)))
	{
		case "LB":
			return "LogBlock"; break;
		case "GD":
			return "Guardian"; break;
		case "HE":
			return "HawkEye"; break;
		case "BB":
			return "BigBrother"; break;
		case "":
			return "NO LOGGING METHOD SELECTED"; break;
		default:
			return "Custom Logger"; break;
	}	
}

function DB_Type_PlayersTable($db_type)
{
	switch(trim(strtoupper($db_type)))
	{
		case "LB":
			return "lb-players"; break;
		case "GD":
			return "gd_players"; break;
		case "HE":
			return "hawk_players"; break;
		case "BB":
			return "bbusers"; break;
		case "":
			return "NO LOGGING METHOD SELECTED"; break;
		default:
			return "Custom Logger"; break;
	}	
}

function SQL_DB_OK($choose)
{
	//echo "GLOBALS['db_config']['db_$choose']['host']: " . $GLOBALS['config_db']['db_' . $choose]['host'] . "<BR>";
	return Check_DB_Exists( ($choose == "source") ,
		$GLOBALS['db']['type'],
		$GLOBALS['config_db']['db_' . $choose]['host'],
		$GLOBALS['config_db']['db_' . $choose]['base'],
		$GLOBALS['config_db']['db_' . $choose]['user'],
		$GLOBALS['config_db']['db_' . $choose]['pass'],
		$GLOBALS['config_db']['db_' . $choose]['prefix'] );
}

function Check_DB_Exists($validate_table, $db_type, $db_host, $db_base, $db_user, $db_pass, $db_prefix)
{
    //print_r($_POST);
	
	if($db_host=="" || $db_base=="" || $db_user=="" || $db_pass=="")
	{
		return (array('error' => true, 'message' => "Check_DB_Exists: Missing a required variable." ));
	}
	
	$connect = @mysql_connect($db_host,$db_user,$db_pass);
	
	if($connect)
	{
		  // Get ID of players whose names partially match the search parameter
		  $sql_DB_IsValid  = "SHOW DATABASES LIKE '".$db_base."'";
		  //echo "SQL QUERY: <BR>" . $sql_DB_IsValid . "<BR>";
		  $res_DB_IsValid = @mysql_query($sql_DB_IsValid);
		  if(!$res_DB_IsValid)
		  {
			  return (array('error' => true, 'message' => "SQL connection OK, but an error occurred while looking for database [".$db_base."] ." ));
		  }

		if( mysql_num_rows($res_DB_IsValid) > 0 )
		{
			if($validate_table)
			{
				switch( trim(strtoupper($db_type)) )
				{
					case "LB":
						$db_table_find = "lb-players"; break;
					case "GD":
						$db_table_find = "gd_players"; break;
					case "HE":
						return (array('error' => false, 'message' => "Logging Type Invalid: HawkEye (Custom Logger Required) - NOT YET IMPLEMENTED" )); // For future use: 'hawk_players' is the default table
					case "BB":
						return (array('error' => false, 'message' => "Logging Type Invalid: BigBrother (Custom Logger Required) - NOT YET IMPLEMENTED" )); // For future use: 'bbusers' is the correct table
					case "":
						return (array('error' => false, 'message' => "Logging Type Invalid: LOG TYPE CANNOT BE BLANK." ));
					default:
						return (array('error' => false, 'message' => "Logging Type Invalid: Custom Logger - NOT YET IMPLEMENTED" ));
				}
				
				if( @mysql_select_db($db_base) )
				{
					$sql_Logging_IsValid  = "SHOW TABLES LIKE '$db_table_find'";
					//echo "SQL QUERY: <BR>" . $sql_Logging_IsValid . "<BR>";
					$res_Logging_IsValid = @mysql_query($sql_Logging_IsValid);
					if($res_Logging_IsValid)
					{
						if( mysql_num_rows($res_Logging_IsValid) > 0 )
							{ return (array('error' => false, 'message' => "HOST OK" )); }
						else
							{ return (array('error' => true, 'message' => "Database connection OK, but could not find ".DB_Type_Name($db_type)." installation." )); }	
					} else
					{
						return (array('error' => true, 'message' => "Database connection OK, but an error occurred while checking ".DB_Type_Name($db_type)." installation: " . mysql_error() ));
					}

					
				} else
				{
					 return (array('error' => true, 'message' => "SQL connection OK, Database [".$db_base."] exists, but an error occurred when trying to connect to the database: " . mysql_error() ));
					
				}

			}
			else
			{
				return (array('error' => false, 'message' => "SUCCESS: SQL Connection OK, Database [".$db_base."] exists. DB is selectable. " ));
			}
		}
		else
		{ return (array('error' => true, 'message' => "SQL connection OK, but could not find database [".$db_base."] ." )); }


		
	}
	else
	{
		return (array('error' => true, 'message' => mysql_error() ));
	}
}

function Check_XTables_Valid()
{
	$correct_tablecount = 6;
	$db_ok_response = SQL_DB_OK("xray");
	
	if($db_ok_response["error"])
	{
		return $db_ok_response;
	}

	$sql_Find_XTables  = "SHOW TABLES LIKE 'x-%'";
	//echo "SQL QUERY: <BR>" . $sql_Find_XTables . "<BR>";
	$res_Find_XTables = @mysql_query($sql_Find_XTables);
	if($res_Find_XTables)
	{
		if( mysql_num_rows($res_Find_XTables) == $correct_tablecount )
			{ return (array('error' => false, 'message' => "X-Ray Tables Found (".mysql_num_rows($res_Find_XTables)."/$correct_tablecount)" )); }
		else
			{ return (array('error' => true, 'message' => "Database connection OK, but could not find ".DB_Type_Name($GLOBALS['db']['type'])." installation." )); }	
	} else
	{
		return (array('error' => true, 'message' => "Attempted to search for X-Ray Tables, but and error occurred while executing SQL Query: <p>[$sql_Find_XTables]<p> <p>ERROR: [".mysql_error()."]</p> "));
	}
}

function Find_WorldTables_Valid()
{
switch($GLOBALS['db']['type'])
	{
		default: case "LB":
			// search for tables that contain column_name = 'replaced'. This should find all LB world tables
			$sql_Find_WorldTables  = "SELECT `table_name` ";
			$sql_Find_WorldTables .= "	FROM `information_schema`.`columns` ";
			$sql_Find_WorldTables .= " 		WHERE `table_schema` = '".$GLOBALS['db']['s_base']."' AND ";
			$sql_Find_WorldTables .= " 			(`column_name` = 'replaced') ";
			$sql_Find_WorldTables .= " GROUP BY `table_name`";
			$sql_Find_WorldTables .= " ";
			$sql_Find_WorldTables .= " ";
			//echo "SQL QUERY: <BR>" . $sql_Find_WorldTables . "<BR>";
			$res_Find_WorldTables = @mysql_query($sql_Find_WorldTables);
			$res_Find_WorldTables = mysql_query($sql_Find_WorldTables) or die("GetWorlds: " . mysql_error());
			$WorldsArray = array();
			while(($WorldsArray[] = mysql_fetch_assoc($res_Find_WorldTables)) || array_pop($WorldsArray));
			return $WorldsArray;		
			break;
			
		case "GD": return false; break; // Guardian lists the worlds in a separate table (gd_worlds by default)
		case "HE": return false; break; // Hawkeye lists the worlds in a separate table (hawk_worlds by default)
		case "BB": return false; break; // Hawkeye lists the worlds in a separate table (bbworlds by default)
	}
}


?>