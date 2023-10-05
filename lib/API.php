<?php

/**
 * MemberData-Ranking API Interface
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

use MemberDataRanking\Controllers\Configuration;
use MemberDataRanking\Controllers\Player;
use MemberDataRanking\Controllers\Matches;

class API extends \MemberData\Lib\API
{
    protected $routes = [
        'configuration.post' => [Configuration::class, 'index'],
        'configuration.save.post' => [Configuration::class, 'save'],
        'player.post' => [Player::class, 'index'],
        'match.post' => [Matches::class, 'index'],
        'match.save.post' => [Matches::class, 'save'],
        'match.reassess.post' => [Matches::class, 'reassess'],
        'match.delete.post' => [Matches::class, 'remove']
    ];

    public static function register($plugin)
    {
        add_action('wp_ajax_' . Display::PACKAGENAME, fn($page) => self::ajaxHandler($page));
        add_action('wp_ajax_nopriv_' . Display::PACKAGENAME, fn($page) => self::ajaxHandler($page));
    }

    protected static function ajaxHandler($page)
    {
        $dat = new static();
        $dat->resolve();
    }
}
