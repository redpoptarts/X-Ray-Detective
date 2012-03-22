UPDATE `x-stats` 
SET
    `diamond_ratio` = FORMAT( (`diamond_count` * 100 / `stone_count`),2),
    `gold_ratio`    = FORMAT( (`gold_count` * 100 / `stone_count`),2),
    `lapis_ratio`   = FORMAT( (`lapis_count` * 100 / `stone_count`),2),
    `mossy_ratio`   = FORMAT( (`mossy_count` * 100 / `stone_count`),2),
    `iron_ratio`    = FORMAT( (`iron_count` * 100 / `stone_count`),2)
WHERE `stone_count` > 50;