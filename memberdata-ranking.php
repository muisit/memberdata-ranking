<?php

/**
 * MemberData Ranking
 *
 * @package             memberdata-ranking
 * @author              Michiel Uitdehaag
 * @copyright           2020 - 2023 Michiel Uitdehaag for muis IT
 * @licenses            GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:         memberdata-ranking
 * Plugin URI:          https://github.com/muisit/memberdata-ranking
 * Description:         Simple ranking of players in a configurable elo calculation
 * Version:             1.1.6
 * Requires at least:   6.1
 * Requires PHP:        7.2
 * Author:              Michiel Uitdehaag
 * Author URI:          https://www.muisit.nl
 * License:             GNU GPLv3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:         memberdata-ranking
 * Domain Path:         /languages
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

define('MEMBERDATARANKING_VERSION', "1.1.6");
define('MEMBERDATARANKING_PACKAGENAME', 'memberdata_ranking');

function memberdata_ranking_autoloader($name)
{
    if (!strncmp($name, 'MemberDataRanking\\', 18)) {
        $elements = explode('\\', $name);
        // require at least MemberDataRanking\<sub>\<name>, so 3 elements
        if (sizeof($elements) > 2 && $elements[0] == "MemberDataRanking") {
            $fname = $elements[sizeof($elements) - 1] . ".php";
            $dir = strtolower(implode("/", array_splice($elements, 1, -1))); // remove the base part and the file itself
            if (file_exists(__DIR__ . "/" . $dir . "/" . $fname)) {
                include(__DIR__ . "/" . $dir . "/" . $fname);
            }
        }
    }
}

spl_autoload_register('memberdata_ranking_autoloader');

if (defined('ABSPATH')) {
    \MemberDataRanking\Lib\Activator::register(__FILE__);

    add_action('memberdata_loaded', function () {
        \MemberDataRanking\Lib\Display::register(__FILE__);
        \MemberDataRanking\Lib\API::register(__FILE__);
        \MemberDataRanking\Lib\Plugin::register(__FILE__);
    });
}
