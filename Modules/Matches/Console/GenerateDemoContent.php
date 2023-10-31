<?php
/**
 * php artisan matches:createDemoContent
 */

namespace Modules\Matches\Console;

use App\Services\UsersService;
use Modules\Matches\Services\DemoContentService;
use Illuminate\Console\Command;
use Modules\Matches\Services\MatchesFormService;
use Modules\Matches\Services\MatchesOptionService;
use Modules\Matches\Services\MatchesOptionValuesService;
use Modules\Matches\Services\MatchesProfileService;
use Modules\Matches\Services\MatchesService;
use Modules\Matches\Services\MatchesValueService;

class GenerateDemoContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = ' matches:createDemoContent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a demo content with demo users having  password 123';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $demoContentService = new DemoContentService(
            new UsersService(),
            new MatchesFormService(
                new MatchesService(
                    new MatchesOptionService(
                        new MatchesOptionValuesService()
                    )
                )
            ),
            new MatchesProfileService(
                new MatchesFormService(
                    new MatchesService(new MatchesOptionService(
                            new MatchesOptionValuesService()
                        )
                    )
                ),
                new MatchesValueService(),
                new MatchesOptionValuesService(),
            ),
        );
;
        $demoContentService->get();
    }
}
