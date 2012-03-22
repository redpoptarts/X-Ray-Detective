SELECT p.playername, playerid, volume, first_block_ore, slope_before_neg, slope_before_pos, slope_after_neg, slope_after_pos, spread_before, spread_after, ore_begin, ore_length FROM `lb-players` AS p


LEFT JOIN
(
    SELECT playerid, AVG(slope_before) AS slope_before_neg FROM `x-clusters`
    WHERE slope_before < 0

) AS c1 USING (playerid)

LEFT JOIN
(
    SELECT playerid, AVG(slope_before) AS slope_before_pos FROM `x-clusters`
    WHERE slope_before >= 0

) AS c2 USING (playerid)

LEFT JOIN
(
    SELECT playerid, AVG(slope_after) AS slope_after_neg FROM `x-clusters`
    WHERE slope_after < 0

) AS c3 USING (playerid)

LEFT JOIN
(
    SELECT playerid, AVG(slope_after) AS slope_after_pos FROM `x-clusters`
    WHERE slope_after >= 0

) AS c4 USING (playerid)

LEFT JOIN
(
    SELECT playerid, AVG(spread_before) AS spread_before, AVG(spread_after) AS spread_after, AVG(ore_begin) AS ore_begin, AVG(ore_length) AS ore_length FROM `x-clusters`

) AS c5 USING (playerid)

LEFT JOIN
(
    SELECT playerid, AVG(volume) AS volume, AVG(first_block_ore) * 100 AS first_block_ore FROM `x-mines`

) AS c6 USING (playerid)


/*

WHERE `playerid` = 4671
*/


LIMIT 9999