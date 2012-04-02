;<?php die();
;/* DO NOT EDIT ABOVE THIS LINE
[xray_config]
version = v0.02.00a
;=====================================================
; X-Ray Detective Config - General Settings
;=====================================================
;
; For More Information Visit:
; http:;dev.bukkit.org/server-mods/x-ray-detective/
;
; IRC Channel: (irc.esper.net) #xray
;
; Version: 0.01a
;
;=====================================================

;=============================
; Database settings
;=============================

[db_config]
; -----------------------------------------------
; WHICH SYSTEM DO YOU USE?
; GD - Guardian (NOT YET SUPPORTED)
; LB - LogBlock
; CUSTOM - Use a custom database module. (See usage below)
; -----------------------------------------------
db_module_type = LB
; To ignore xray database info and use same info as source DB instead, use "yes"
db_use_same = yes

[db_source]
; -----------------------------------------------
; Database that contains your LogBlock / Guardian tables
; -----------------------------------------------
; IP or Hostname for the database? Normally localhost
host = mysqlserver
; DB that contains LogBlock / Guardian tables
base = minecraft
; Username to access the DB
user = username
; Password to access the DB
pass = password
; Prefix of the LB/GD Tables, keep blank by default unless you know you use a prefix
prefix = 

[db_xray]
; -----------------------------------------------
; Database to store X-Ray Detective tables in
; -----------------------------------------------
; IP or Hostname for the database? Normally localhost
host = mysqlserver
; DB that contains LogBlock / Guardian tables
base = minecraft
; Username to access the DB
user = username
; Password to access the DB
pass = password
; Prefix of the LB/GD Tables, keep blank by default unless you know that you use a prefix
prefix = 

; X-Ray Detective supports Guardian and LogBlock data sources.
; If you wish to use data from another source (HawkEye, BigBrother), you must use a custom module developed by another developer (if they ever create one).
;
; If another developer has created a custom module, it must be installed in the following folder "xray/src".
;
; Example path and filename: "xray/inc/dbmodule_CUSTOM.php"
;

; DO NOT EDIT BELOW THIS LINE */
;?>