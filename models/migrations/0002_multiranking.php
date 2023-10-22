<?php

namespace MemberDataRanking\Models;

use MemberData\Models\MigrationObject;
use MemberData\Models\Sheet;
use MemberDataRanking\Controllers\Base;

class Migration0002 extends MigrationObject
{
    public function up()
    {
        $this->addColumn("memberdataranking_match", "matchtype", "text NULL");
        // find all columns of 'rank' type
        $sheetModel = new Sheet();
        $sheets = $sheetModel->select()->get();
        $matchType = null;
        foreach ($sheets as $sheet) {
            $sheet = new Sheet($sheet);
            $config = Base::getConfig($sheet->getKey());
            foreach ($config as $attribute) {
                if ($attribute['type'] == 'rank') {
                    $matchType = $attribute['name'];
                    break;
                }
            }
            if (!empty($matchType)) break;
        }

        $this->rawQuery("UPDATE " . $sheetModel->tableName('memberdataranking_match') . " set matchtype='" . $this->escape($matchType) . "';");
        return true;
    }

    public function down()
    {
        $this->dropColumn("memberdataranking_match", "matchtype");
        return true;
    }
}
