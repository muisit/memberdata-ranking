<?php

/**
 * MemberData-Ranking page display routines
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

use MemberData\Controllers\Base;
use MemberData\Lib\Services\ManifestService;
use MemberDataRanking\Lib\Services\PlayerList;

class Display
{
    public const PACKAGENAME = MEMBERDATARANKING_PACKAGENAME;
    private const ADMINSCRIPT = 'src/admin.ts';
    private const FESCRIPT = 'src/frontend.ts';

    public static function adminPage()
    {
        ManifestService::enqueueAssets(self::ADMINSCRIPT, self::PACKAGENAME, __DIR__ . '/../dist', 'memberdata-ranking');
        $nonce = wp_create_nonce(Base::createNonceText());
        $data = [
            "nonce" => $nonce,
            "url" => admin_url('admin-ajax.php?action=' . self::PACKAGENAME),
        ];
        $obj = json_encode($data);
        $id = self::PACKAGENAME . '-admin';
        $dataName = 'data-' . self::PACKAGENAME;
        echo <<<HEREDOC
        <div id="$id" $dataName='$obj'></div>
HEREDOC;
    }

    public static function addFrontEnd($attributes)
    {
        $groupname = "all";
        $rankname = "";
        if (is_array($attributes) && count($attributes)) {
            foreach ($attributes as $key => $attr) {
                if (strtolower($key) == 'group') {
                    $groupname = $attr;
                }
                if (strtolower($key) == 'type') {
                    $rankname = $attr;
                }
            }
        }

        ManifestService::enqueueAssets(self::FESCRIPT, self::PACKAGENAME, __DIR__ . '/../dist', 'memberdata-ranking');
        $nonce = wp_create_nonce(Base::createNonceText());
        $data = [
            "nonce" => $nonce,
            "url" => admin_url('admin-ajax.php?action=' . self::PACKAGENAME),
            "group" => $groupname,
            "ranking" => $rankname,
            "token" => $_GET['token'] ?? ''
        ];
        $obj = json_encode($data);
        $id = self::PACKAGENAME . '-frontend';
        $dataName = 'data-' . self::PACKAGENAME;
        return <<<HEREDOC
        <div id="$id" $dataName='$obj'></div>
HEREDOC;
    }

    public static function addRankingListOutput($attributes = [])
    {
        $labelName = esc_html_x('Name', 'noun', 'memberdata-ranking');
        $labelGroup = esc_html_x('Group', 'noun', 'memberdata-ranking');
        $labelPoints = esc_html__('Points', 'memberdata-ranking');

        // allow a group name as attribute
        $groupname = "all";
        $rankname = "";
        if (is_array($attributes) && count($attributes)) {
            foreach ($attributes as $key => $attr) {
                if (strtolower($key) == 'group') {
                    $groupname = $attr;
                }
                if (strtolower($key) == 'type') {
                    $rankname = $attr;
                }
            }
        }

        $data = PlayerList::listPlayers($groupname);
        $tablerows = "";
        $pos = 0;
        $realpos = 0;
        $lastrank = -1;
        foreach ($data as $row) {
            $name = $row['name'] ?? 'N.N.';
            $group = $groupname == 'all' ? ($row['groupname'] ?? null) : null;
            if (!isset($row['rankings']) || (!empty($rankname) && !isset($row['rankings'][$rankname]))) {
                $rank = 0;
            }
            else if (empty($rankname)) {
                $rank = max($row['rankings']);
            }
            else {
                $rank = $row['rankings'][$rankname] ?? 0;
            }

            if ($rank <= 0) continue;

            $realpos += 1;
            if ($lastrank < 0 || $lastrank != $rank) {
                $pos = $realpos;
            }
           
            $tablerows .= "\r\n<tr><td class='pos'>$pos</td><td class='player-name'>$name</td>";
            if ($groupname == "all") {
                $tablerows .= "<td class='group-name'>$group</td>";
            }
            $tablerows .= "<td class='rank'>$rank</td></tr>";
        }

        $headers = "<th>#</th>\r\n<th>$labelName</th>\r\n<th>$labelPoints</th>\r\n";
        if ($groupname == "all") {
            $headers = "<th>#</th>\r\n<th>$labelName</th>\r\n<th>$labelGroup</th>\r\n<th>$labelPoints</th>\r\n";
        }

        return <<<HEREDOC
        <table class='memberdata-ranking-list'>
            <thead>
                <tr>$headers</tr>
            </thead>
            <tbody>
              $tablerows
            </tbody>
        </table>
        HEREDOC;
    }

    public static function register($plugin)
    {
        load_plugin_textdomain('memberdata-ranking', false, basename(dirname(__DIR__)) . '/languages');
        add_action('admin_menu', fn() => self::adminMenu());
        add_shortcode('memberdata-ranking-list', fn($a) => self::addRankingListOutput($a));
        add_shortcode('memberdata-ranking', fn($a) => self::addFrontEnd($a));
    }

    private static function adminMenu()
    {
        add_submenu_page(
            'memberdata',
            __('Ranking'),
            __('Ranking'),
            'manage_memberdata',
            self::PACKAGENAME,
            fn() => Display::adminPage(),
            50
        );
    }
}
