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

use MemberData\Controllers\Base as BaseController;
use MemberDataRanking\Lib\Display;

class Base extends BaseController
{
    public static function getRankConfig()
    {
        $eloconfig = json_decode(get_option(Display::PACKAGENAME . "_values"));
        if (empty($eloconfig)) {
            $eloconfig = (object)[
                'base_rank' => 1000,
                'k_value' => 32,
                'c_value' => 400,
                'l_value' => 32,
                's_value' => 16,
                'namefield' => '',
                'groupingfield' => 'none',
                'sheet' => 0,
                'token' => uniqid()
            ];
            add_option(Display::PACKAGENAME . '_values', json_encode($eloconfig));
        }
        return $eloconfig;
    }

    protected function tokenOrAuthenticate($token)
    {
        $this->checkNonce();
        $config = self::getRankConfig();
        return $token == $config->token || $this->authenticate();
    }
}
