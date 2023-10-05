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
    public function index()
    {
        $this->authenticate();
        $eloconfig = self::getConfig();
        $attributes = \apply_filters('memberdata_configuration', []);
        $groupvalues = \apply_filters('memberdata_values', ["field" => $eloconfig->groupingfield]);

        $data = [
            "base_rank" => $eloconfig->base_rank ?? 1000,
            "k_value" => $eloconfig->k_value ?? 32,
            "c_value" => $eloconfig->c_value ?? 400,
            "l_value" => $eloconfig->l_value ?? 32,
            "s_value" => $eloconfig->s_value ?? 16,
            "namefield" => $eloconfig->namefield ?? '',
            "groupingfield" => $eloconfig->groupingfield ?? '',
            "groupingvalues" => $groupvalues['result'] ?? [],
            "attributes" => array_column($attributes, 'name'),
            "validgroups" => $eloconfig->validgroups ?? [],
        ];
        return $data;
    }

    public function save($data)
    {
        $this->authenticate();
        $eloconfig = self::getConfig();
        $attributes = \apply_filters('memberdata_configuration', []);
        $names = array_column($attributes, 'name');

        $eloconfig->base_rank = intval($data['model']['base_rank'] ?? 1000);
        $eloconfig->k_value = intval($data["model"]["k_value"] ?? 32);
        $eloconfig->c_value  = intval($data["model"]["c_value"] ?? 400);

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

        $groupvalues = \apply_filters('memberdata_values', ["field" => $eloconfig->groupingfield]);
        $values = $data['model']['validgroups'] ?? [];
        $validvalues = [];
        foreach ($values as $v) {
            if (in_array($v, $groupvalues['result'] ?? [])) {
                $validvalues[] = $v;
            }
        }
        $eloconfig->validgroups = $validvalues;

        update_option(Display::PACKAGENAME . '_values', json_encode($eloconfig));
        return $this->index();
    }
}
