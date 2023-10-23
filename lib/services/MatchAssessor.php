<?php

/**
 * MemberData-Ranking Match Assessor
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

namespace MemberDataRanking\Lib\Services;

use MemberDataRanking\Models\MemberMatch;
use MemberDataRanking\Models\Result;
use MemberDataRanking\Controllers\Base;

class MatchAssessor
{

    public static function assessMatch(MemberMatch $matchModel)
    {
        if ($matchModel->isNew()) {
            return false;
        }

        $result1 = null;
        $result2 = null;
        if (isset($matchModel->results) && count($matchModel->results) == 2) {
            $result1 = $matchModel->results[0];
            $result2 = $matchModel->results[1];
        }
        else {
            $resultModel = new Result();
            $rows = $resultModel->select('*')->where('match_id', $matchModel->getKey())->get();
            if (!empty($rows) && count($rows) == 2) {
                $result1 = new Result($rows[0]);
                $result2 = new Result($rows[1]);
            }
        }
        $matchModel->results = [$result1, $result2];

        if (empty($result1) || $result1->isNew() || $result1->match_id != $matchModel->getKey()) {
            return false;
        }
        if (empty($result2) || $result2->isNew() || $result2->match_id != $matchModel->getKey()) {
            return false;
        }
        if ($result1->is_dirty == 'N' && $result2->is_dirty == 'N') {
            // already assessed, no use assessing again
            return true;
        }

        self::doAssessment($matchModel, $result1, $result2);
        return true;
    }

    private static function doAssessment(MemberMatch $matchModel, Result $result1, Result $result2)
    {
        $config = Base::getRankConfig();
        $totalScore = $result1->score + $result2->score;
        $maxScore = 2 * max($result1->score, $result2->score);

        $previousResult1 = self::findPreviousEntry($result1->player_id, $matchModel->entered_at, $result1->getKey(), $matchModel->matchtype);
        $previousResult2 = self::findPreviousEntry($result2->player_id, $matchModel->entered_at, $result2->getKey(), $matchModel->matchtype);

        self::assessSingleResult($matchModel, $result1, $result2, $previousResult1, $previousResult2, $totalScore, $maxScore, $config);
        self::assessSingleResult($matchModel, $result2, $result1, $previousResult2, $previousResult1, $totalScore, $maxScore, $config);
    }

    private static function assessSingleResult(MemberMatch $matchModel, Result $result, Result $opponent, ?Result $previous, ?Result $opponentPrevious, int $totalScore, int $maxScore, object $config)
    {
        // keep the original rank result to see if we need to update anything
        $oldRank = null;
        if (!empty($result->rank_end)) {
            $oldRank = $result->rank_end;
        }

        if (empty($previous) || $previous->isNew()) {
            $previous = new Result();
            $previous->rank_end = $config->base_rank ?? 1000;
        }
        if (empty($opponentPrevious) || $opponentPrevious->isNew()) {
            $opponentPrevious = new Result();
            $opponentPrevious->rank_end = $config->base_rank ?? 1000;
        }
        $kval = $config->k_value ?? 32;
        $cval = $config->c_value ?? 400;

        $qa = pow(10, $previous->rank_end / $cval);
        $qb = pow(10, $opponentPrevious->rank_end / $cval);
        $ea = $qa / ($qa + $qb);
        $sa = ($result->score + (($result->score > $opponent->score) ? 1 : 0)) / ($result->score + $opponent->score + 1);

        $result->expected = ceil($ea * 1000);
        $result->rank_start = $previous->rank_end;
        $result->rank_change = ceil(($kval * (ceil($sa * 1000) - $result->expected)) / 1000);
        $result->rank_end = $result->rank_start + $result->rank_change;
        $result->c_value = $cval;
        $result->k_value = $kval;
        $result->s_value = $config->s_value ?? 0;
        $result->l_value = $config->l_value ?? 0;
        $result->is_dirty = 'N';
        $result->save();

        if ($oldRank == null || $oldRank != $result->rank_end) {
            // make all rank entries after this entry dirty
            $result->makeLaterDirty($matchModel);

            // then make all rank entries dirty if their match contains at least one dirty match
            // This makes sure all opponents get their rank recalculated if the players rank may change
            // Do not do this for our current match, the other result has just been assessed or will be
            // assessed after this
            $result->makeMatchesDirty();
        }
    }

    private static function findPreviousEntry(int $memberId, string $entered_at, int $resultId, string $type): Result
    {
        // find the entry before this entry, based on entered_at and result id
        // entered_at is probably unique enough
        $matchModel = new MemberMatch();
        $resultModel = new Result();
        $value = $resultModel->select('*')
            ->leftJoin($matchModel->tableName(), 'mm', $resultModel->tableName() . '.match_id=mm.id')
            ->where('player_id', $memberId)
            ->where('mm.matchtype', $type)
            ->andSub()->where('mm.entered_at', '<', $entered_at)
                ->orSub()->where('entered_at', $entered_at)->where($resultModel->tableName() . '.id', '<', $resultId)->get()
            ->get()
            ->orderBy('mm.entered_at', 'desc')
            ->orderBy($resultModel->tableName() . '.id', 'desc')
            ->first();

        return new Result($value);
    }
}
