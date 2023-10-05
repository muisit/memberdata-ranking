<?php

namespace MemberDataRanking\Models;

use MemberData\Models\MigrationObject;

class Migration0001 extends MigrationObject
{
    public function up()
    {
        $this->createTable("memberdataranking_match", "(
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `entered_at` datetime NOT NULL,
            PRIMARY KEY (`id`)) ENGINE=InnoDB");

        $this->createTable("memberdataranking_result", "(
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `match_id` int(11) NOT NULL,
            `player_id` int(11) NOT NULL,
            `score` int(11) NOT NULL,
            `expected` int(11) NOT NULL,
            `rank_start` int(11) NOT NULL,
            `rank_change` int(11) NOT NULL,
            `rank_end` int(11) NOT NULL,
            `c_value` int(11) NOT NULL,
            `s_value` int(11) NOT NULL,
            `l_value` int(11) NOT NULL,
            `k_value` int(11) NOT NULL,
            `is_dirty` char(1) NOT NULL DEFAULT('N'),
            `modified` datetime NOT NULL,
            `modifier` int(11) NOT NULL,
            PRIMARY KEY (`id`)) ENGINE=InnoDB");
        return true;
    }

    public function down()
    {
        $this->dropTable("memberdataranking_match");
        $this->dropTable("memberdataranking_result");
        return true;
    }
}