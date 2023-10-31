<?php

namespace Modules\Matches\Services\Search;

use Illuminate\Support\Collection;
use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Managers\IMatchesTypeManager;
use Modules\Matches\Models\Matches;
use Modules\Matches\Models\MatchesProfile;
use Modules\Matches\Models\MatchesValues;
use Modules\Matches\Services\MatchTypes\Interfaces\IMatchesTypeService;
use Modules\Matches\Services\MatchTypes\RangeMatchesTypeService;

class SearchViewService
{
    public function createFormMatchesView(int $formId)
    {
        \DB::statement($this->dropView($formId));
        \DB::statement($this->createView($formId));
    }

    public function dumpView(int $formId): void
    {
        dd(\DB::select('SELECT * FROM ' . self::getViewName($formId)));
    }

    public function dropView(int $formId): string
    {
        return 'DROP VIEW IF EXISTS ' . self::getViewName($formId);
    }

    private function createView(int $formId): string
    {
        /** @var Collection $matches */
        $matches = Matches::where('matches_form_id', $formId)->get();
        if ($matches->count() == 0) {
            return 'select 1';
        }

        $index = 0;
        $selects = [];
        $joins = [];
        $wheres = [];

        /** @var Matches $match */
        foreach ($matches as $match) {
            switch ($match->match_type) {

                    case  EMatchType::value('RANGE')->name :
                    $oderIndexes = [
                        RangeMatchesTypeService::LOVER_BOUND_VALUE_INDEX,
                        RangeMatchesTypeService::UPPER_BOUND_VALUE_INDEX,
                    ];
                    foreach ($oderIndexes as $oderIndex) {
                        $mvAlias = 'MV_' . $index++;
                        $selects[] = "$mvAlias.value AS " . $match->db_code . "_" . $oderIndex . " ";
                        $joins[] = "JOIN matches_values $mvAlias ON (MP.id = $mvAlias.matches_profile_id ) ";
                        $wheres[] = "AND $mvAlias.matches_id = " . $match->id . " ";
                        $wheres[] = "AND $mvAlias.order = $oderIndex ";
                    }
                    break;
                default:
                    $mvAlias = 'MV_' . $index++;
                    $selects[] = "$mvAlias.value AS " . $match->db_code . " ";
                    $joins[] = "JOIN matches_values $mvAlias ON (MP.id = $mvAlias.matches_profile_id ) ";
                    $wheres[] = "AND $mvAlias.matches_id = " . $match->id . " ";
            }
        }

        $sql = "CREATE VIEW " . self::getViewName($formId) . "
                AS SELECT
                MP.id AS matches_profile_id,
                 MP.name,
                MP.is_professional," .
            implode(',', $selects) . " " .
            "FROM matches_profile MP " .
            implode(' ', $joins) . " " .
            "WHERE 1 = 1 " .
            implode(' ', $wheres) . " ";
        return $sql;
    }


    public function getFindQuery(MatchesProfile $profileToMatch, bool $exact = true): string
    {
        $factory = app(IMatchesTypeManager::class);
        $query = 'SELECT DISTINCT (matches_profile_id) AS id ' .
            'FROM ' . SearchViewService::getViewName($profileToMatch->formId()) . ' ' .
            'WHERE is_professional != ' . $profileToMatch->is_professional;

        $query .= ' AND ( ';
        $addedWheres = [];
        $index = 0;
        /** @var MatchesValues $value */
        foreach ($profileToMatch->values()->get() as $value) {
            if (!in_array($value->matches_id, $addedWheres)) {
                $type = $value->match()->first()->match_type;
                /** @var IMatchesTypeService $service */
                $service = $factory->make(EMatchType::value($type));
                $where = $service->getViewSearchQuery(
                    $value,
                    $profileToMatch,
                    $exact,
                );
                if ($index == 0) {
                    $where = preg_replace(['/ AND /', '/ OR /'], ' ', $where, 1);
                }
                $query .= $where;
                $addedWheres[] = $value->matches_id;
                $index++;
            }
        }
        $query .= ' ) ';

        return $query;
    }

    public static function getViewName($formId): string
    {
        return 'values_form_' . $formId;
    }
}
