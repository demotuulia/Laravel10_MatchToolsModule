<?php

namespace Modules\Matches\Managers;

use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Services\MatchTypes\Interfaces\IMatchesTypeService;

interface IMatchesTypeManager
{
    public function make(EMatchType $type): IMatchesTypeService;
}
