<?php

namespace Modules\Matches\Models\Traits;

use Carbon\Carbon;
use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Models\Matches;
use Modules\Matches\Models\MatchesOptions;

trait TMatchesFormat
{

    private function formatOutput(Matches $match): Matches
    {
        return match ($match->match_type) {
            EMatchType::MULTIPLE_CHOOSE->name => $this->formatMultipleChoose($match),
            EMatchType::MENU->name => $this->formatMenu($match),
            EMatchType::DATE_FROM->name => $this->formatDate($match),
            default => $match,
        };
    }

    /**
     * Define for each option if it is selected
     */
    private function formatMultipleChoose(Matches $match): Matches
    {
        $selectedOptions = explode(',', $match->values[0]['value']);
        $selectedOptionValues = [];
        if ($match->match_type == EMatchType::MULTIPLE_CHOOSE->name) {
            /** @var MatchesOptions $option */
            foreach ($match->options as $option) {
                if (in_array($option->id, $selectedOptions)) {
                    $selectedOptionValues[] = $option->value;
                    $option->selected=true;
                }
            }
        }
        $match->valueLabel = implode(', ', $selectedOptionValues);
        return $match;
    }

    /**
     * Add valueLabel, which is read from current option by id
     */
    private function formatMenu(Matches $match): Matches
    {
        if (!is_null($match->values()->first()->value)) {
            /** @var MatchesOptions $option */
            foreach ($match->options as $option) {
                if ($option->id == $match->values->first()->value) {
                    $match->values->first()->valueLabel = $option->value;
                }
            }
        }
        return $match;
    }

    private function formatDate(Matches $match): Matches
    {
        $date = (int)$match->values->first()->value;
        $match->values->first()->valueLabel = $date
            ? Carbon::parse($date)->format('Y-m-d')
            : '';
        return $match;
    }
}
