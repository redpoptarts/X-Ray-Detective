;<?php die();
;/* DO NOT EDIT ABOVE THIS LINE
[xray_config]
version = v0.01a
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

[auth]

;=============================
; Failsafe IPs
;=============================
; List any IPs that will be given administrator powers in the event that they can't authenticate
; via USERNAME or PASSWORD mode above.
;
; This prevents you from being locked out of the script if your config gets messed up or
; you somehow get removed from the admin list.
;
; The IP 127.0.0.1 will always be given administrator access,
; even if it is not included in this list.
;
; Example:
; failsafe_ips = 192.168.1.10, 123.4.5.67

failsafe_ips = 

;=============================
; AUTHORIZATION MODE
;=============================
; Select a method of granting access to the X-Ray Detection script.
;
; "username" - Users who are listed below are granted access automatically.
;              Do not use this method if your server is a cracked server (online-mode=false in server config)
; "password" - Anyone may login to the script if they enter the correct password.
; "none"     - Everyone is granted USER status. No one may make changes at all. You must modify the config files manually to change settings.

mode = username

; If AUTH_MODE is set to USERNAME
; List the usernames of all users who will be granted access rights. They will not require a password.
;
; IMPORTANT: They MUST login to your server from that computer before they can be granted access.
; If a valid user does NOT login to your server, they will still receive a message saying they do not have authorization to view the page.
;
; IMPORTANT: Because this method relies on Minecraft.net to validate their user account, do not use this option in offline mode.
;
; Example:
; admin_usernames = Username1, Username2, Username3
; mod_usernames   = Username1, Username2, Username3
; user_usernames  = Username1, Username2, Username3
admin_usernames = 
mod_usernames = 
user_usernames = 

; If AUTH_MODE is set to PASSWORD
; Anyone may login to the script if they enter the correct password.
;
; If they enter the Admin password, they are granted admin powers.
; If they enter the Mod password, they are granted mod powers.
; If they enter the User password, they are granted mod powers.
;
; Example:
; admin_password = adminpassword
; mod_password   = modpassword
; user_password  = userpassword
admin_password = adminpassword
mod_password = modpassword
user_password = userpassword

[settings]
;=============================
; Mine detection settings
;=============================
ignorefirstore_before = 2 ; If the first block broken in a mine in an ore, the stats will not count any ores broken among the first __ blocks.
mine_max_distance = 5 ; A block must be within __ blocks of all previous blocks to be considered part of the same mine
; Recommended 1 to 10, 1 will cause more fragmented statistics, 10 will lump more stats together
postbreak_check = 3 ; How many breaks to check for after last ore in a cluster

; BELOW SETTINGS ARE NOT YET IMPLEMENTED
;=============================
; Auto-watch settings
;=============================
; Users stats can be automatically tracked over time
;
;
;
; Minimum number of stone that must be broken before a user can be automatically flagged as Watching (for snapshots)
autowatch_threshold = 100

; Which resource types are capable of flagging a user
autowatch_triggers = all

; Players that have any stat hit this level are autowatched
; Must be 0 thru 10 (0 is no ratio, 1 is low ratio, 10 is very high)
autowatch_start = 7

; Players whose max stat returns to this level are cleared from Watched status
; Must be 0 thru 10 (0 is no ratio, 1 is low ratio, 10 is very high)
autowatch_stop = 6
; ABOVE SETTINGS ARE NOT YET IMPLEMENTED


first_setup = yes
; DO NOT EDIT BELOW THIS LINE */
;?>