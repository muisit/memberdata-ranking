<?php

/**
 * MemberData-Ranking Player Controller
 * 
 * @package             memberdata-ranking
 * @author              Michiel Uitdehaag
 * @copyright           2020 Michiel Uitdehaag for muis IT
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

namespace MemberDataRanking\Controllers;

class Player extends Base
{
    public function index($data)
    {
        $this->authenticate();
        $eloconfig = self::getConfig();

        $namefield = $eloconfig->namefield ?? null;
        $groupingfield = $eloconfig->groupingfield ?? null;
        $settings = [
            "sorter" => $namefield,
            "sortDirection" => 'asc'
        ];
        $result = \apply_filters('memberdata_find_members', $settings);
        $attributes = \apply_filters('memberdata_configuration', []);
        $attribute = null;
        foreach ($attributes as $a) {
            if ($a['type'] == 'rank') {
                $attribute = $a;
                break;
            }
        }

        $data = [];
        foreach ($result['list'] as $member) {
            $row = [
                'id' => $member['id'],
                'rank' => 1000,
            ];
            if (!empty($namefield) && isset($member[$namefield])) {
                $row['name'] = $member[$namefield];
            }
            if (!empty($groupingfield) && isset($member[$groupingfield])) {
                $row['groupname'] = $member[$groupingfield];
            }

            if ($attribute != null) {
                $row['rank'] = intval($member[$attribute['name']] ?? $eloconfig->base_rank);
            }
            $data[] = $row;
        }
        usort($data, function ($a, $b) {
            if ($a['rank'] == $b['rank']) {
                return ($a['name'] ?? $a['id']) <=> ($b['name'] ?? $b['id']);
            }
            return -1 * ($a['rank'] <=> $b['rank']);
        });
        return $data;
    }
}
