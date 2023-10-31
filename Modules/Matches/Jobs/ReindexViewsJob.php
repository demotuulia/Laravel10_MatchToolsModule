<?php

namespace Modules\Matches\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Matches\Services\Search\SearchViewService;

class ReindexViewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $formId;
    /**
     * Create a new job instance.
     */
    public function __construct(int $formId)
    {
        $this->formId = $formId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $searchViewService = new SearchViewService();
        $searchViewService->createFormMatchesView($this->formId);
    }
}
