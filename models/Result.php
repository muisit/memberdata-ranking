<?php

/**
 * MemberData-Ranking Result model
 * 
 * @package             memberdata-ranking
 * @author              Michiel Uitdehaag
 * @copyright           2023 Michiel Uitdehaag for muis IT
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

namespace MemberDataRanking\Models;

use MemberData\Models\Base;
use MemberData\Models\QueryBuilder;

class Result extends Base
{
    public $table = "memberdataranking_result";
    public $pk = "id";
    public $fields = array("id", "match_id", "player_id", "score", "expected", "change", "rank_start", "rank_change", "rank_end",
        "c_value", "s_value", "l_value", "k_value", "is_dirty", "modified", "modifier"
    );

    public $rules = array(
        "id" => "skip",
        "match_id" => "skip",
        "player_id" => "required|int|min=1",
        "score" => "required|int|min=0",
        "expected" => "int",
        "rank_start" => "int",
        "rank_change" => "int",
        "rank_end" => "int",
        "c_value" => "int",
        "s_value" => "int",
        "l_value" => "int",
        "k_value" => "int",
        "is_dirty" => "enum=N,Y",
        "modified" => "skip",
        "modifier" => "skip"
    );

    public function save()
    {
        $user = wp_get_current_user();
        if ($user !== null) {
            $this->modifier = $user->ID;
        }
        else  {
            $this->modifier = -1;
        }
        $this->modified = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        return parent::save();
    }

    public function withMatch(QueryBuilder $builder)
    {
        $mm = new MemberMatch();
        return $builder->leftJoin($mm->tableName(), 'mm', 'mm.id = ' . $this->tableName() . '.match_id');
    }

    public function getLastResult(string $type)
    {
        return $this->select()
            ->withMatch()
            ->where('player_id', $this->player_id)
            ->where('mm.matchtype', $type)
            ->orderBy('mm.entered_at', 'desc')
            ->orderBy('mm.id', 'desc')
            ->first();
    }

    public function makeLaterDirty($matchModel = null)
    {
        if (empty($matchModel)) {
            $matchModel = new MemberMatch($this->match_id);
            $matchModel->load();
        }
        $this->query()->set('is_dirty', 'Y')
            ->withMatch()
            ->where('mm.matchtype', $matchModel->matchtype)
            ->andSub()
                ->andSub()->where('mm.entered_at', '>', $matchModel->entered_at)->get()
                ->orSub()->where('mm.entered_at', $matchModel->entered_at)->where($this->tableName() . '.id', '>', $this->getKey())->get()
            ->get()
            ->where('player_id', $this->player_id)
            ->update();
    }

    public function makeMatchesDirty()
    {
        $dirtymatches = $this->select('match_id')->where('is_dirty', 'Y')->where('match_id', '<>', $this->match_id)->get();
        if (!empty($dirtymatches)) {
            $ids = array_column($dirtymatches, 'match_id');
            $this->query()
                ->set('is_dirty', 'Y')
                ->where('is_dirty', 'N')
                ->where('match_id', 'in', $ids)
                ->update();
        }
    }
}
