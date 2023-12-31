<?php

/**
 * MemberData-Ranking Configuration Controller
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

use MemberDataRanking\Lib\Display;

class Configuration extends Base
{
    public function index($data)
    {
        $eloconfig = self::getRankConfig();
        $data = [
            "base_rank" => $eloconfig->base_rank ?? 1000,
            "validgroups" => $eloconfig->validgroups ?? [],
            "sheet" => $eloconfig->sheet ?? 0,
        ];

        if ($this->canAuthenticate()) {
            $data["k_value"] = $eloconfig->k_value ?? 32;
            $data["c_value"] = $eloconfig->c_value ?? 400;
            $data["l_value"] = $eloconfig->l_value ?? 32;
            $data["s_value"] = $eloconfig->s_value ?? 16;
            $data["namefield"] = $eloconfig->namefield ?? '';
            $data["groupingfield"] = $eloconfig->groupingfield ?? '';
            $data['token'] = $eloconfig->token ?? '';
        }

        return $data;
    }

    public function basic($data)
    {
        $sheet = intval($data['model']['sheet'] ?? 0);
        $field = $data['model']['groupingfield'] ?? '';
        $configuration = \apply_filters('memberdata_configuration', ['sheet' => $sheet, 'configuration' => []]);
        $attributes = $configuration['configuration'];
        $groupvalues = \apply_filters('memberdata_values', ['sheet' => $sheet, "field" => $field, 'values' => []]);
        $sheets = \apply_filters('memberdata_find_sheets', []);
        $rankAttributes = $this->findRankAttributes($sheet);

        $data = [
            "groupingvalues" => $groupvalues['values'] ?? [],
            "rankAttributes" => $rankAttributes
        ];

        if ($this->canAuthenticate()) {
            $data["attributes"] = array_column($attributes, 'name');
            $data["sheets"] = $sheets;
        }

        return $data;
    }

    public function save($data)
    {
        $this->authenticate();
        $eloconfig = self::getRankConfig();
        $sheet = intval($eloconfig->sheet ?? 0);

        $eloconfig->base_rank = intval($data['model']['base_rank'] ?? 1000);
        $eloconfig->k_value = intval($data["model"]["k_value"] ?? 32);
        $eloconfig->c_value  = intval($data["model"]["c_value"] ?? 400);
        $eloconfig->sheet = intval($data['model']['sheet'] ?? 0);
        $eloconfig->token = $data['model']['token'] ?? '';

        $configuration = \apply_filters('memberdata_configuration', ['sheet' => $sheet, 'configuration' => []]);
        $attributes = $configuration['configuration'];
        $names = array_column($attributes, 'name');

        $namefield = $data['model']['namefield'] ?? '';
        $eloconfig->namefield = '';
        if (in_array($namefield, $names)) {
            $eloconfig->namefield = $namefield;
        }

        $groupingfield = $data['model']['groupingfield'] ?? 'none';
        $eloconfig->groupingfield = '';
        if (in_array($groupingfield, $names)) {
            $eloconfig->groupingfield = $groupingfield;
        }

        $groupvalues = \apply_filters('memberdata_values', ["sheet" => $sheet, "field" => $eloconfig->groupingfield, 'values' => []]);
        $values = $data['model']['validgroups'] ?? [];
        $validvalues = [];
        foreach ($values as $v) {
            if (in_array($v, $groupvalues['values'] ?? [])) {
                $validvalues[] = $v;
            }
        }
        $eloconfig->validgroups = $validvalues;

        update_option(Display::PACKAGENAME . '_values', json_encode($eloconfig));
        return $this->index();
    }

    private function findRankAttributes($sheetid)
    {
        $retval = [];
        $attributes = self::getConfig($sheetid);
        foreach ($attributes as $attribute) {
            if ($attribute['type'] == 'rank') {
                $retval[] = $attribute['name'];
            }
        }
        return $retval;
    }
}
