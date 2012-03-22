SELECT p.playerid, p.playername, world, replaced AS block, cnt
FROM `lb-players` AS p


INNER JOIN
    (SELECT *, "Erebus" AS world, count(*) AS cnt
        FROM `lb-worlderebus`
            WHERE  replaced = 1
                OR replaced = 15
                OR replaced = 14
                OR replaced = 56
                OR replaced = 25
                OR replaced = 48
                AND type = 0
        GROUP BY playerid, replaced
    ) as w1

USING (playerid)

UNION ALL

SELECT p.playerid, p.playername, world, replaced AS block, cnt
FROM `lb-players` AS p
    
INNER JOIN
    (SELECT *, "Ether" AS world, count(*) AS cnt
        FROM `lb-worldether`
            WHERE  replaced = 1
                OR replaced = 15
                OR replaced = 14
                OR replaced = 56
                OR replaced = 25
                OR replaced = 48
                AND type = 0
        GROUP BY playerid, replaced
    ) AS w2
    
USING (playerid)

GROUP BY playerid, replaced


/*
WHERE  replaced = 1
    OR replaced = 15
    OR replaced = 14
    OR replaced = 56
    OR replaced = 25
    OR replaced = 48
    AND type = 0
    
GROUP BY playerid, replaced

LIMIT 10000

*/