<?php

/**
 * MemberData-Ranking activation routines
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

use MemberData\Models\Migration;

function memberdataranking_uninstall_hook()
{
    Activator::uninstall();
}

class Activator
{
    private const CONFIG = MEMBERDATARANKING_PACKAGENAME . "_version";

    public static function deactivate()
    {
    }

    public static function uninstall()
    {
        // instantiate the Migrate model and run the activate method
        $model = new Migration(MEMBERDATARANKING_PACKAGENAME . '_migrations');
        $model->uninstall(realpath(__DIR__ . '/../models'));
    }

    public static function activate()
    {
        update_option(self::CONFIG, 'new');
        self::update();
    }

    public static function upgrade()
    {
        update_option(self::CONFIG, 'new');
    }

    public static function update()
    {
        if (get_option(self::CONFIG) == "new") {
            // this loads all database migrations from file and executes
            // all those that are not yet marked as migrated
            $model = new Migration(MEMBERDATARANKING_PACKAGENAME . '_migrations');
            $model->activate(realpath(__DIR__ . '/../models'));
            update_option(self::CONFIG, strftime('%F %T'));
        }
    }

    public static function register($plugin)
    {
        register_activation_hook($plugin, fn() => self::activate());
        register_deactivation_hook($plugin, fn() => self::deactivate());
        register_uninstall_hook($plugin, "memberdataranking_uninstall_hook");
        add_action('upgrader_process_complete', fn() => self::upgrade(), 10, 2);
        add_action('plugins_loaded', fn() => self::update());
    }
}
