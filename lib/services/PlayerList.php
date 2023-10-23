<?php

/**
 * MemberData-Ranking Player Listing Service
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

namespace MemberDataRanking\Lib\Services;

use MemberDataRanking\Controllers\Base;

class PlayerList
{
    public static function listPlayers($groupname = 'all')
    {
        $eloconfig = Base::getRankConfig();
        $namefield = $eloconfig->namefield ?? null;
        $groupingfield = $eloconfig->groupingfield ?? null;

        $settings = [
            "sorter" => $namefield,
            "sortDirection" => 'asc',
            'sheet' => $eloconfig->sheet
        ];

        if (!empty($groupingfield) && $groupname != 'all') {
            $settings["filter"] = [
                $groupingfield => [ "values" => [$groupname]]
            ];
        }
        $result = \apply_filters('memberdata_find_members', $settings);

        $config = \apply_filters('memberdata_configuration', ['sheet' => $eloconfig->sheet]);
        $attributes = $config['configuration'] ?? [];
        $rankAttributes = [];
        foreach ($attributes as $a) {
            if ($a['type'] == 'rank') {
                $rankAttributes[] = $a;
            }
        }

        $data = [];
        foreach ($result['list'] as $member) {
            $row = [
                'id' => $member['id'],
                'rankings' => []
            ];

            if (!empty($namefield) && isset($member[$namefield])) {
                $row['name'] = $member[$namefield];
            }
            if (!empty($groupingfield) && isset($member[$groupingfield])) {
                $row['groupname'] = $member[$groupingfield];
            }
            else {
                // skip members without a valid group
                continue;
            }

            foreach ($rankAttributes as $a) {
                $rankingValue = intval($member[$a['name']] ?? $eloconfig->base_rank);
                $row['rankings'][$a['name']] = $rankingValue;
            }
            $data[] = $row;
        }

        usort($data, function ($a, $b) use ($rankAttributes) {
            foreach ($rankAttributes as $attr) {
                if ($a['rankings'][$attr['name']] != $b['rankings'][$attr['name']]) {
                    return -1 * ($a['rankings'][$attr['name']] <=> $b['rankings'][$attr['name']]);
                }
            }
            return ($a['name'] ?? $a['id']) <=> ($b['name'] ?? $b['id']);
        });

        return $data;
    }
}
