<?php

/**
 * MemberData-Ranking Plugin Interface
 *
 * @package             memberdata-ranking
 * @author              Michiel Uitdehaag
 * @copyright           2020 - 2023 Michiel Uitdehaag for muis IT
 * @licenses            GPL-3.0-or-later
 *
 * This file is part of memberdata-ranking.
 *
 * memberdata-ranking is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * memberdata-ranking is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with memberdata-ranking.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace MemberDataRanking\Lib;

use MemberDataRanking\Lib\Services\MatchAssessor;
use MemberData\Models\Sheet;
use MemberData\Models\Member;
use MemberDataRanking\Models\MemberMatch;
use MemberDataRanking\Models\Result;
use MemberDataRanking\Controllers\Base;

class Plugin
{
    public static function register($plugin)
    {
        // add a rank type, which will be filled with the member's ranking value
        add_filter('memberdata_attribute_types', function ($config) {
            $config["rank"] = ["label" => "Ranking", "rules" => "readonly", "options" => null];
            return $config;
        }, 600, 1);
      
        add_filter(Display::PACKAGENAME . '_assess_match', function (MemberMatch $matchModel) {
            $eloconfig = Base::getRankConfig();
            if (MatchAssessor::assessMatch($matchModel)) {
                $config = \apply_filters('memberdata_configuration', ['sheet' => $eloconfig->sheet]);
                self::updateMemberRanking($matchModel->results[0]->getLastResult($matchModel->matchtype), $config, $matchModel->matchtype);
                self::updateMemberRanking($matchModel->results[1]->getLastResult($matchModel->matchtype), $config, $matchModel->matchtype);
            }
            MatchAssessor::clearRankOfNonParticipants($matchModel->matchtype);
        }, 500, 1);

        add_filter('memberdata_save_configuration', function ($configuration) {
            // if there are 'originalName' entries that differ from the 'name' value and have a rank type,
            // update the matchtype for that name
            $matchModel = new MemberMatch();
            foreach ($configuration as $sheetno => $attributes) {
                $sheet = new Sheet(substr($sheetno, 6));
                $sheet->load();
                if (!$sheet->isNew()) {
                    foreach ($attributes as $attribute) {
                        if ($attribute['type'] == 'rank') {
                            if (isset($attribute['name']) && isset($attribute['originalName']) && $attribute['name'] != $attribute['originalName']) {
                                $matchModel->query()
                                    ->set('matchtype', $attribute['name'])
                                    ->where('matchtype', $attribute['originalName'])
                                    ->whereExists(function ($qb) use ($sheet) {
                                        $model = new Result();
                                        $memberModel = new Member();
                                        return $qb->select('*')->from($model->table)
                                            ->innerJoin($memberModel->tableName(), 'm', 'm.id = ' . $model->tableName() . '.player_id')
                                            ->innerJoin($sheet->tableName(), 's', 's.id = m.sheet_id')
                                            ->where('s.id', $sheet->getKey());
                                    })
                                    ->update();
                            }
                        }
                    }
                }
            }
            return $configuration;
        }, 500, 1);
    }

    private static function updateMemberRanking(object $result, array $config, string $type)
    {
        if (!empty($result)) {
            $member = new Member($result->player_id);
            $attributes = [];
            foreach ($config['configuration'] as $attribute) {
                if ($attribute['type'] == 'rank' && $attribute['name'] == $type) {
                    $attributes[$attribute['name']] = $result->rank_end;
                }
            }

            apply_filters('memberdata_save_attributes', [
                'member' => $member,
                'attributes' => $attributes,
                'messages' => [],
                'configuration' => $config
            ]);
        }
    }
}
