<?php

namespace Modules\Matches\Managers\MatchesType;

use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Managers\IMatchesTypeManager;
use Modules\Matches\Services\MatchTypes\DateFromMatchesTypeService;
use Modules\Matches\Services\MatchTypes\DateToMatchesTypeService;
use Modules\Matches\Services\MatchTypes\BiggerThanMatchesTypeService;
use Modules\Matches\Services\MatchTypes\EqualMatchesTypeService;
use Modules\Matches\Services\MatchTypes\Interfaces\IMatchesTypeService;
use Modules\Matches\Services\MatchTypes\MenuMatchesTypeService;
use Modules\Matches\Services\MatchTypes\MultipleChooseMatchesTypeService;
use Modules\Matches\Services\MatchTypes\RadioButtonMatchesTypeService;
use Modules\Matches\Services\MatchTypes\RangeMatchesTypeService;
use Modules\Matches\Services\MatchTypes\SmallerThanMatchesTypeService;

class MatchesTypeManager implements IMatchesTypeManager
{
    public function make(EMatchType $type): IMatchesTypeService
    {
        switch ($type) {
            case EMatchType::EQUAL:
                return new EqualMatchesTypeService();
                break;
            case EMatchType::SMALLER_THAN:
                return new SmallerThanMatchesTypeService();
                break;
            case EMatchType::BIGGER_THAN:
                return new BiggerThanMatchesTypeService();
                break;
            case EMatchType::RADIO_BUTTON:
                return new RadioButtonMatchesTypeService();
            case EMatchType::RANGE:
                return new RangeMatchesTypeService();
            case EMatchType::MULTIPLE_CHOOSE:
                return new MultipleChooseMatchesTypeService();
            case EMatchType::MENU:
                return new MenuMatchesTypeService();
            case   EMatchType::DATE_FROM:
                return new DateFromMatchesTypeService();
            case   EMatchType::DATE_TO:
                return new DateToMatchesTypeService();
            case EMatchType::CHECK_BOX:
            case EMatchType::RANGE:
            case EMatchType::DATE_FROM:
            case EMatchType::DATE_TO:
                throw new \Exception('To be implemented');
                break;
        }

        return new EqualMatchesTypeService();
    }

}
