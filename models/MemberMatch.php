<?php

/**
 * MemberData-Ranking Match model
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

class MemberMatch extends Base
{
    public $table = "memberdataranking_match";
    public $pk = "id";
    public $fields = array("id", "entered_at");

    public $rules = array(
        "id" => "skip",
        "entered_at" => "required|datetime"
    );

    public function sorted(QueryBuilder $builder)
    {
        return $builder->orderBy('entered_at', 'desc')->orderBy('id', 'desc');
    }
}
