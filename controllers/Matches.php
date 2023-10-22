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

use MemberDataRanking\Models\MemberMatch as MatchModel;
use MemberDataRanking\Models\Result as ResultModel;
use MemberDataRanking\Lib\Display;

class Matches extends Base
{
    public function index($data)
    {
        $this->authenticate();

        $offset = intval($data['model']['offset'] ?? 0);
        $pagesize = intval($data['model']['pagesize'] ?? 0);
        $matchtype = $data['model']['matchtype'] ?? '';

        $matchModel = new MatchModel();
        $count = $matchModel->select()->where('matchtype', $matchtype)->count();
        $qb = $matchModel->select()->where('matchtype', $matchtype)->sorted();
        if ($pagesize > 0) {
            $qb->limit($pagesize)->offset($offset);
        }
        $matches = $qb->get();
        $matches = $this->decorateMatches($matches);

        return [
            'count' => $count,
            'list' => $matches
        ];
    }

    public function save($data)
    {
        $this->authenticate();
        $matchModel = new MatchModel();
        $modelData = $data['model'] ?? [];
        $results = $modelData['results'] ?? [];

        if (count($results) != 2) {
            error_log('die because data invalid');
            die(403);
        }

        if (isset($modelData['id'])) {
            $matchModel = new MatchModel($modelData['id']);
            $matchModel->load();
        }
        $entered_at = $this->parseDate($modelData['entered_at'] ?? null);

        $matchModel->matchtype = $modelData['matchtype'] ?? '';
        $matchModel->entered_at = $entered_at;
        $result1 = $this->createResult($results[0], $matchModel);
        $result2 = $this->createResult($results[1], $matchModel);

        if ($matchModel->validate() && $result1?->validate() && $result2?->validate()) {
            $matchModel->save();
            $result1->match_id = $matchModel->getKey();
            $result1->save();
            $result2->match_id = $matchModel->getKey();
            $result2->save();
            $matchModel->results = [$result1, $result2];
            \apply_filters(Display::PACKAGENAME . '_assess_match', $matchModel);
            return true;
        }
        else {
            error_log(json_encode([$matchModel->errors, $result1?->errors ?? [], $result2?->errors ?? []]));
            return false;
        }
    }

    public function reassess()
    {
        $this->authenticate();
        $matchModel = new MatchModel();
        $resultModel = new ResultModel();
        $matches = $resultModel->select('match_id')->where('is_dirty', 'Y')->get();
        if (!empty($matches)) {
            $matchIds = array_unique(array_column($matches, 'match_id'));

            $matches = $matchModel->select()->where('id', 'in', $matchIds)->sorted()->get();
            for ($i = count($matches) - 1; $i >= 0; $i--) {
                $matchModel = new MatchModel($matches[$i]);
                \apply_filters(Display::PACKAGENAME . '_assess_match', $matchModel);
            }
        }
        return true;
    }

    public function remove($data)
    {
        $this->authenticate();
        $matchModel = new MatchModel();
        $modelData = $data['model'] ?? [];
        if (isset($modelData['id'])) {
            $matchModel = new MatchModel($modelData['id']);
            $matchModel->load();
        }

        if (!$matchModel->isNew()) {
            $resultModel = new ResultModel();
            $results = $resultModel->select()->where('match_id', $matchModel->getKey())->get();
            foreach ($results as $result) {
                $result = new ResultModel($result);
                $result->makeLaterDirty();
                $result->makeMatchesDirty();
                $result->delete();
            }
            $matchModel->delete();
            return true;
        }
        return false;
    }

    private function createResult($resultData, $matchModel): ?ResultModel
    {
        $resultModel = new ResultModel($resultData['id'] ?? 0);
        $resultModel->load();

        if (!$resultModel->isNew() && $resultModel->match_id != $matchModel->getKey()) {
            error_log("resultmodel is not new and match id does not match");
            return null;
        }

        $resultModel->player_id = $resultData['player_id'] ?? null;
        $resultModel->score = $resultData['score'] ?? 0; // if unset, assume 0
        $resultModel->is_dirty = 'Y';
        return $resultModel;
    }

    private function decorateMatches(array $matches)
    {
        if (empty($matches)) {
            return;
        }

        $ids = array_column($matches, 'id');
        $resultModel = new ResultModel();
        $results = $resultModel->select()->where('match_id', 'in', $ids)->get();
        $resultsByMatchId = [];
        foreach ($results as $result) {
            $mid = 'm' . $result->match_id;
            if (!isset($resultsByMatchId[$mid])) {
                $resultsByMatchId[$mid] = [];
            }
            $resultsByMatchId[$mid][] = $result;
        }

        return array_map(function ($matchvalue) use ($resultsByMatchId) {
            $mid = 'm' . $matchvalue->id;
            if (isset($resultsByMatchId[$mid])) {
                $matchvalue->results = $resultsByMatchId[$mid];
            }
            return $matchvalue;
        }, $matches);
    }

    private function parseDate(?string $date): string
    {
        if (empty($date)) {
            return (new DateTimeImmutable())->format('Y-m-d H:i:s');
        }
        // expect a YYYY-mm-dd part to the left, we need a specific day at least
        $datePart = substr($date, 0, 10);
        $timePart = trim(substr($date, 11));
        $parts = empty($timePart) ? [] : explode(':', $timePart);

        $format = 'Y-m-d';
        switch (count($parts)) {
            case 0:
                break; // no time
            case 1:
                $format .= ' H';
                break;
            case 2:
                $format .= ' H:i';
                break;
            default:
                $format .= ' H:i:s';
                break;
        }
        $dt = \DateTime::createFromFormat($format, $date);
        return $dt->format('Y-m-d H:i:s');
    }
}
