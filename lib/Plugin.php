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

use MemberDataRanking\Models\MemberMatch;
use MemberDataRanking\Lib\Services\MatchAssessor;
use MemberData\Models\Member;
use MemberDataRanking\Models\Result;

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
            if (MatchAssessor::assessMatch($matchModel)) {
                $config = \apply_filters('memberdata_configuration', []);
                self::updateMemberRanking($matchModel->results[0]->getLastResult(), $config);
                self::updateMemberRanking($matchModel->results[1]->getLastResult(), $config);
            }
        }, 500, 1);
    }

    private static function updateMemberRanking(object $result, array $config)
    {
        if (!empty($result)) {
            $member = new Member($result->player_id);
            $attributes = [];
            foreach ($config as $attribute) {
                if ($attribute['type'] == 'rank') {
                    $attributes[$attribute['name']] = $result->rank_end;
                }
            }
            apply_filters('memberdata_save_attributes', [
                'member' => $member,
                'attributes' => $attributes,
                'messages' => [],
                'config' => $config
            ]);
        }
    }
}
