<?php

namespace Davoodf1995\Desk365;

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
            ->hasConfigFile();
    }

    public function packageBooted()
    {
        // Register any additional boot logic here
    }
}

