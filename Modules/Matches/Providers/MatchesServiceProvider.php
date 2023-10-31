<?php

namespace Modules\Matches\Providers;

use Modules\Matches\Console\CreateMatchesUser;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Modules\Matches\Console\GenerateDemoContent;
use Modules\Matches\Managers\IMatchesTypeManager;
use Modules\Matches\Managers\MatchesType\MatchesTypeManager;
use Modules\Matches\Services\DashboardService;
use Modules\Matches\Services\MatchesFormService;
use Modules\Matches\Services\MatchesService;
use Modules\Matches\Services\MatchesOptionService;
use Modules\Matches\Services\MatchesOptionValuesService;
use Modules\Matches\Services\MatchesProfileService;
use Modules\Matches\Services\MatchesValueService;
use Modules\Matches\Services\SearchService;

class MatchesServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Matches';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'matches';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateMatchesUser::class,
                GenerateDemoContent::class,
            ]);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->bind(IMatchesTypeManager::class, function ($app) {
            return new MatchesTypeManager();
        });
        $this->app->singleton(SearchService::class, function () {
            return new SearchService();
        });
        $this->app->singleton(MatchesFormService::class, function () {
            return new MatchesFormService(
                new MatchesService(new MatchesOptionService(new MatchesOptionValuesService()))
            );
        });

        $this->app->singleton(MatchesFormService::class, function () {
            return new MatchesFormService(
                new MatchesService(new MatchesOptionService(new MatchesOptionValuesService()))
            );
        });
        $this->app->singleton(MatchesProfileService::class, function () {
            return new MatchesProfileService(
                new MatchesFormService(
                    new MatchesService(new MatchesOptionService(new MatchesOptionValuesService()))
                ),
                new MatchesValueService(),
                new MatchesOptionValuesService(),

            );
        });
        $this->app->singleton(MatchesService::class, function () {
            return new MatchesService(
                new MatchesOptionService(new MatchesOptionValuesService()),
            );
        });

        $this->app->singleton(MatchesOptionService::class, function () {
            return new MatchesOptionService(
                new MatchesOptionValuesService(),
            );
        });

        $this->app->singleton(DashboardService::class, function () {
            return new DashboardService();
        });
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes(
            [
                module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
            ], 'config'
        );
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );

        // Enable te read from Modules/Matches/Config/matches-abilities.php by \Config::get('matches-abilities');
        $pathToAppMatchesAbilities = module_path($this->moduleName, 'Config/matches-abilities.php');
        $pathToMatchesAbilities = config_path('matches-abilities.php');
        $matchesAbilitiesGroup = 'matches-abilities';
        $this->publishes(
            [
                $pathToAppMatchesAbilities => $pathToMatchesAbilities,
            ], $matchesAbilitiesGroup
        );
        $this->mergeConfigFrom(
            $pathToAppMatchesAbilities, $matchesAbilitiesGroup
        );

        $pathToAppMatches = module_path($this->moduleName, 'Config/matches.php');
        $pathToMatches = config_path('matches.php');
        $matchesGroup = 'matches';
        $this->publishes(
            [
                $pathToAppMatches => $pathToMatches,
            ], $matchesGroup
        );
        $this->mergeConfigFrom(
            $pathToAppMatches, $matchesGroup
        );

    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes(
            [
                $sourcePath => $viewPath
            ], ['views', $this->moduleNameLower . '-module-views']
        );

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'Resources/lang'));
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
