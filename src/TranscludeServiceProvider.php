<?php

namespace CHStudio\LaravelTransclude;

use CHStudio\LaravelTransclude\TranscludeCompiler;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class TranscludeServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $compiler = new TranscludeCompiler;
        $this->app['view']->share($compiler->getName(), $compiler);

        Blade::directive('transclude', [$compiler, 'compileTransclude']);
        Blade::directive('endtransclude', [$compiler, 'compileEndTransclude']);
        Blade::directive('transcluded', [$compiler, 'compileTranscluded']);
    }
}
