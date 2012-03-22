
INSERT INTO `x-stats`
(`playerid`, `volume`, `first_block_ore`, `slope_before_neg`, `slope_before_pos`, `slope_after_neg`, `slope_after_pos`, `spread_before`, `spread_after`, `ore_begin`, `ore_length`) 

SELECT p.playerid, c6.volume, first_block_ore, slope_before_neg, slope_before_pos, slope_after_neg, slope_after_pos, spread_before, spread_after, ore_begin, ore_length FROM `lb-players` AS p

INNER JOIN
(
    SELECT AVG(slope_before) AS slope_before_neg FROM `x-clusters`
    WHERE slope_before < 0
        AND `playerid` = 4671
) AS c1

INNER JOIN
(
    SELECT AVG(slope_before) AS slope_before_pos FROM `x-clusters`
    WHERE slope_before >= 0
        AND `playerid` = 4671
) AS c2

INNER JOIN
(
    SELECT AVG(slope_after) AS slope_after_neg FROM `x-clusters`
    WHERE slope_after < 0
        AND `playerid` = 4671
) AS c3

INNER JOIN
(
    SELECT AVG(slope_after) AS slope_after_pos FROM `x-clusters`
    WHERE slope_after >= 0
        AND `playerid` = 4671
) AS c4

INNER JOIN
(
    SELECT AVG(spread_before) AS spread_before, AVG(spread_after) AS spread_after, AVG(ore_begin) AS ore_begin, AVG(ore_length) AS ore_length FROM `x-clusters`
    WHERE `playerid` = 4671
) AS c5

INNER JOIN
(
    SELECT AVG(volume) AS volume, AVG(first_block_ore) * 100 AS first_block_ore FROM `x-mines`
    WHERE `playerid` = 4671
) AS c6

WHERE `playerid` = 4671

ON DUPLICATE KEY UPDATE
`volume`=c6.volume, `first_block_ore`=TRUNCATE(c6.first_block_ore,2), `slope_before_neg`=TRUNCATE(c1.slope_before_neg,2), `slope_before_pos`=TRUNCATE(c2.slope_before_pos,2), `slope_after_pos`=TRUNCATE(c3.slope_after_neg,2), `slope_after_pos`=TRUNCATE(c4.slope_after_pos,2), `spread_before`=c5.spread_before, `spread_after`=c5.spread_after, `ore_begin`=c5.ore_begin, `ore_length`=c5.ore_length;
