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

use MemberDataRanking\Lib\Services\PlayerList;

class Player extends Base
{
    public function index($data)
    {
        $this->checkNonce();
        error_log("token " . json_encode($data));
        $showall = $this->hasValidToken($data['model']['token'] ?? '') || self::canAuthenticate();
        error_log("showall " . json_encode($showall));
        $data = PlayerList::listPlayers('all', $showall);
        return $data;
    }
}
