<?php

namespace Devmatika\Desk365;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class Desk365ServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('desk365')
            ->hasConfigFile()
            ->hasMigrations(['2025_01_01_000000_create_desk365_api_logs_table']);
    }

    public function packageBooted()
    {
        // Register any additional boot logic here
    }
}



