<?php 
//=====================================================
// X-Ray Detective Config - General Settings
//=====================================================
//
// For More Information Visit:
// http://dev.bukkit.org/server-mods/x-ray-detective/
//
// IRC Channel: (irc.esper.net) #xray
//
// Version: 0.01a
//
//=====================================================

//=============================
// Database settings
//=============================

// -----------------------------------------------
// WHICH SYSTEM DO YOU USE?
// GD - Guardian (NOT YET SUPPORTED)
// LB - LogBlock
// CUSTOM - Use a custom database module. (See usage below)
// -----------------------------------------------
$db_type = "LB";

$db_host     = "localhost"; // IP or Hostname for the database? Normally localhost
$db_name     = "minecraft"; // DB that contains LogBlock / Guardian tables
$db_user     = "root"; // Username to access the DB
$db_pass     = "password"; // Password to access the DB
$db_prefix   = ""; // Prefix of the LB/GD Tables, keep blank by default unless you know you use a prefix

// X-Ray Detective supports Guardian and LogBlock data sources.
// If you wish to use data from another source (HawkEye, BigBrother), you must use a custom module developed by another developer (if they ever create one).
//
// If another developer has created a custom module, it must be installed in the following folder "xray/src".
//
// Example path and filename: "xray/src/dbmodule_CUSTOM.php";
//
?>