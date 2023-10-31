<?php

namespace Modules\Matches\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Matches\Models\Matches;
use Modules\Matches\Services\MatchesOptionService;
use Modules\Matches\Services\MatchesOptionValuesService;
use Modules\Matches\Services\MatchesService;

class AddMatchToProfilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $matchId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $matchId)
    {
        $this->matchId = $matchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $searchViewService = new MatchesService(
            new MatchesOptionService(new MatchesOptionValuesService())
        );
        $match = Matches::find($this->matchId);
        $searchViewService->addNewMatchToProfiles($match);
    }
}
